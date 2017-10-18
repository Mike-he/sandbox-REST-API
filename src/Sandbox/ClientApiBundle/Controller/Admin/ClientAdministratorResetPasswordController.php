<?php

namespace Sandbox\ClientApiBundle\Controller\Admin;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserCheckCode;
use Sandbox\ApiBundle\Entity\User\UserPhoneCode;
use Sandbox\ApiBundle\Traits\YunPianSms;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ClientAdministratorRegisterController.
 */
class ClientAdministratorResetPasswordController extends SandboxRestController
{
    use YunPianSms;

    /**
     * @Route("/admin_reset/check_code")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postClientAdminCheckCodeAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['phone_code']) || !isset($data['phone'])) {
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

        if (is_null($salesAdmin)) {
            return new View([
                'error_code' => '400001',
                'error_message' => '该手机号未注册',
            ]);
        }

        $userCheckCode = $this->saveUserCheckCode($phoneCode, $phone);

        $this->sendSMSNotification($userCheckCode);

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/admin_reset/verify")
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
                'type' => '3',
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

        $admin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
            ->findOneBy([
                'phoneCode' => $phoneCode,
                'phone' => $phone,
            ]);

        if (is_null($admin)) {
            return new View();
        }

        $em = $this->getDoctrine()->getManager();

        $admin->setPassword($password);

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($admin->getUserId());

        $this->get('sandbox_api.jmessage')
            ->updatePassword(
                $user->getXmppUsername(),
                $user->getPassword()
            );

        $em->flush();

        return new View();
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
                    'type' => 3,
                )
            );

        //if user check code is existed, check expire date time
        if (is_null($userCheckCode)) {
            $userCheckCode = new UserCheckCode();
            $userCheckCode->setPhone($phone);
            $userCheckCode->setPhoneCode($phoneCode);
            $userCheckCode->setType(3);
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
