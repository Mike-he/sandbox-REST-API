<?php

namespace Sandbox\ClientApiBundle\Controller\Admin;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserCheckCode;
use Sandbox\ApiBundle\Entity\User\UserPhoneCode;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Sandbox\ApiBundle\Traits\YunPianSms;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ClientAdministratorRegisterController.
 */
class ClientAdministratorRegisterController extends SandboxRestController
{
    use YunPianSms;

    /**
     * @Route("/admin_register/check_code")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postClientAdminCheckCodeAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['phone_code']) || !isset($data['phone']) || is_null($data['phone_code']) || empty($data['phone_code'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $phoneCode = $data['phone_code'];
        $phone = $data['phone'];

        $salesAdmin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
            ->findOneBy([
                'phoneCode' => $phoneCode,
                'phone' => $phone,
            ]);

        if (!is_null($salesAdmin)) {
            return new View([
                'error_code' => '400001',
                'error_message' => '该手机号码已注册',
            ]);
        }

        $userCheckCode = $this->saveUserCheckCode($phoneCode, $phone);

        $this->sendSMSNotification($userCheckCode);

        $view = new View();
        $view->setStatusCode('201');

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/admin_register/verify")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postClientAdminVerifyAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['phone_code']) || !isset($data['phone']) || !isset($data['code'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $phoneCode = $data['phone_code'];
        $phone = $data['phone'];
        $code = $data['code'];

        $userCheckCode = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCheckCode')
            ->findOneBy([
                'phoneCode' => $phoneCode,
                'phone' => $phone,
                'code' => $code,
                'type' => '2',
            ]);

        if (is_null($userCheckCode)) {
            return new View([
                'error_code' => '400002',
                'error_message' => '验证码错误',
            ]);
        }

        $maxTokenTime = $this->getParameter('expired_verification_time');
        $now = new \DateTime('now');
        if ($now > $userCheckCode->getCreationDate()->modify($maxTokenTime)) {
            return new View([
                'error_code' => '400003',
                'error_message' => '验证码过期',
            ]);
        }

        if (!isset($data['password'])) {
            return new View();
        }

        $password = $data['password'];

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy([
                'phoneCode' => $phoneCode,
                'phone' => $phone,
            ]);

        $em = $this->getDoctrine()->getManager();

        $xmppUsername = strval(3000000 + $userCheckCode->getId());

        if (is_null($user) && !is_null($password)) {
            $user = new User();
            $user->setPassword($password);
            $user->setPhoneCode($phoneCode);
            $user->setPhone($phone);
            $user->setXmppUsername($xmppUsername);
            $em->persist($user);

            // create default profile
            $profile = new UserProfile();
            $profile->setName('');
            $profile->setUser($user);
            $em->persist($profile);

            // add service account to buddy list
            $this->addBuddyToUser(array($user));

            // post user account to internal api
            $this->postUserAccount($user->getId());

            $this->get('sandbox_api.jmessage')
                ->createUser(
                    $user->getXmppUsername(),
                    $user->getPassword(),
                    ''
                );

            $em->flush();
        }

        $userId = $user->getId();

        $salesAdmin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
            ->findOneBy(array('userId' => $userId));

        if (!$salesAdmin) {
            $salesAdmin = new SalesAdmin();
            $salesAdmin->setPhone($user->getPhone());
            $salesAdmin->setPhoneCode($user->getPhoneCode());
            $salesAdmin->setXmppUsername('admin_' . $xmppUsername);
            $salesAdmin->setPassword($password);
            $salesAdmin->setUserId($user->getId());

            $em->persist($salesAdmin);
            $em->flush();

            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy(array('userId' => $userId));

            $nickname = $profile ? $profile->getName() : null;

            $this->get('sandbox_api.jmessage_property')
                ->createUser(
                    $salesAdmin->getXmppUsername(),
                    $salesAdmin->getPassword(),
                    $nickname
                );
        }

        $view = new View();
        $view->setStatusCode('201');

        return $view;
    }

    /**
     * @param $phoneCode
     * @param $phone
     *
     * @return object|UserCheckCode
     */
    private function saveUserCheckCode(
        $phoneCode,
        $phone
    ) {
        $em = $this->getDoctrine()->getManager();

        $checkCode = $this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH);

        $userCheckCode = $this->getRepo('User\UserCheckCode')
            ->findOneBy(
                array(
                    'phoneCode' => $phoneCode,
                    'phone' => $phone,
                    'type' => 2,
                )
            );

        //if user check code is existed, check expire date time
        if (is_null($userCheckCode)) {
            $userCheckCode = new UserCheckCode();
            $userCheckCode->setPhone($phone);
            $userCheckCode->setPhoneCode($phoneCode);
            $userCheckCode->setType(2);
        }

        // if the date time is expired, update code and creation date
        $userCheckCode->setCode($checkCode);
        $userCheckCode->setCreationDate(new \DateTime('now'));

        $em->persist($userCheckCode);
        $em->flush();

        return $userCheckCode;
    }

    /**
     * @param UserCheckCode $userCheckCode
     */
    private function sendSMSNotification(
        $userCheckCode
    ) {
        $phoneCode = $userCheckCode->getPhoneCode();

        if (UserPhoneCode::DEFAULT_PHONE_CODE == $phoneCode) {
            $smsText = self::ZH_SMS_BEFORE.$userCheckCode->getCode()
                .self::ZH_SMS_AFTER;
        } else {
            $smsText = self::EN_SMS_BEFORE.$userCheckCode->getCode()
                .self::EN_SMS_AFTER;
        }

        $this->send_sms(
            $phoneCode.$userCheckCode->getPhone(),
            $smsText
        );
    }
}
