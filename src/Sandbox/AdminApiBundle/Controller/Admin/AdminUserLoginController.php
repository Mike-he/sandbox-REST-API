<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializationContext;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\AdminApiBundle\Controller\Traits\HandleAdminLoginDataTrait;
use Sandbox\ApiBundle\Entity\Error\Error;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserClient;
use Sandbox\ApiBundle\Entity\User\UserToken;
use Sandbox\ApiBundle\Entity\User\UserCheckCode;
use Sandbox\ApiBundle\Entity\User\UserPhoneCode;
use Sandbox\ApiBundle\Form\User\UserClientType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Acl\Exception\Exception;
use Sandbox\ApiBundle\Traits\YunPianSms;

/**
 * Login controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminUserLoginController extends AdminRestController
{
    use YunPianSms;
    use HandleAdminLoginDataTrait;

    const PREFIX_ACCESS_TOKEN = 'access_token';
    const PREFIX_FRESH_TOKEN = 'fresh_token';

    /**
     * Get admin check code.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/check_code")
     * @Method({"POST"})
     *
     * @return string
     *
     * @throws \Exception
     */
    public function postAdminCheckCode(
        Request $request
    ) {
        // check security & get admin
        $error = new Error();
        $admin = $this->checkAdminIsExisted($error);

        if (is_null($admin)) {
            return $this->customErrorView(
                401,
                $error->getCode(),
                $error->getMessage()
            );
        }

        $em = $this->getDoctrine()->getManager();

        $admin = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')
            ->find($admin->getUserId());

        // save or update user check code
        $userCheckCode = $this->saveUserCheckCode($admin, $em);

        $noSms = $request->query->get('no_sms');
        if (!$noSms) {
            // send verification code by sms
            $this->sendSMSNotification(
                $userCheckCode
            );
        }

        $salesAdmin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
            ->findOneBy(array('userId' => $admin->getId()));

        if (!$salesAdmin) {
            $salesAdmin = new SalesAdmin();
            $salesAdmin->setPhone($admin->getPhone());
            $salesAdmin->setPhoneCode($admin->getPhoneCode());
            $salesAdmin->setXmppUsername('admin_'.$admin->getXmppUsername());
            $salesAdmin->setPassword($admin->getPassword());
            $salesAdmin->setUserId($admin->getId());

            $em->persist($salesAdmin);
            $em->flush();

            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy(array('userId' => $admin->getId()));

            $nickname = $profile ? $profile->getName() : null;

            $this->get('sandbox_api.jmessage')
                ->createUser(
                    $salesAdmin->getXmppUsername(),
                    $salesAdmin->getPassword(),
                    $nickname
                );

            $this->get('sandbox_api.jmessage_commnue')
                ->createUser(
                    $salesAdmin->getXmppUsername(),
                    $salesAdmin->getPassword(),
                    $nickname
                );
        }

        // here is a back door for testing
        $isTest = $request->query->get('test');
        if ($isTest) {
            return new View(
                array(
                    'code' => $userCheckCode->getCode(),
                )
            );
        }

        return new View();
    }

    /**
     * Login.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/login")
     * @Method({"POST"})
     *
     * @return string
     *
     * @throws \Exception
     */
    public function postAdminUserLoginAction(
        Request $request
    ) {
        $payload = json_decode($request->getContent(), true);

        if (!isset($payload['check_code'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // check security & get admin
        $admin = $this->checkAdminLoginSecurity();

        // remove check code if the given code is exited or return error
        $error = $this->removeUserCheckCodeIfExited($payload['check_code'], $admin->getPhone());

        if (!is_null($error)) {
            return $error;
        }

        $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')
            ->find($admin->getUserId());

        return $this->handleAdminUserLogin($request, $user);
    }

    /**
     * @param Request $request
     * @param SalesAdmin    $admin
     *
     * @return View
     *
     * @throws \Exception
     */
    private function handleAdminUserLogin(
        Request $request,
        $admin
    ) {
        try {
            $em = $this->getDoctrine()->getManager();

            // save or update admin client
            $adminClient = $this->saveAdminClient($request);
            if (is_null($adminClient->getId())) {
                $em->persist($adminClient);
                $em->flush();
            }

            // save or refresh admin token
            $adminToken = $this->saveAdminToken($admin, $adminClient);
            if (is_null($adminToken->getId())) {
                $em->persist($adminToken);
            }
            $em->flush();

            // get admin positions
            $positions = $this->getRepo('Admin\AdminPositionUserBinding')
                ->findPositionByAdmin($admin);
            $platform = $this->handlePositionData($positions);

            // response
            $view = new View();
            $view->setSerializationContext(
                SerializationContext::create()->setGroups(array('login'))
            );

            // set admin cookie
            setrawcookie(self::ADMIN_COOKIE_NAME, $adminToken->getToken(), null, '/', $this->getParameter('top_level_domain'));

            return $view->setData(
                array(
                    'admin' => $admin,
                    'token' => $adminToken,
                    'client' => $adminClient,
                    'platform' => $platform,
                )
            );
        } catch (Exception $e) {
            throw new \Exception('Something went wrong!');
        }
    }

    /**
     * @param Request $request
     *
     * @return UserClient
     */
    private function saveAdminClient(
        Request $request
    ) {
        $adminClient = new UserClient();

        // set creation date for new object
        $now = new \DateTime('now');
        $adminClient->setCreationDate($now);

        // get admin client if exist
        $adminClient = $this->getAdminClientIfExist($request, $adminClient);

        // set ip address
        $adminClient->setIpAddress($request->getClientIp());

        // set modification date
        $adminClient->setModificationDate($now);

        return $adminClient;
    }

    /**
     * @param Request    $request
     * @param UserClient $adminClient
     *
     * @return UserClient
     */
    private function getAdminClientIfExist(
        Request $request,
        UserClient $adminClient
    ) {
        $requestContent = $request->getContent();
        if (is_null($requestContent)) {
            return $adminClient;
        }

        // get client data from request payload
        $payload = json_decode($requestContent, true);
        $clientData = $payload['client'];

        if (is_null($clientData)) {
            return $adminClient;
        }

        if (array_key_exists('id', $clientData)) {
            // get existing admin client
            $adminClientExist = $this->getRepo('User\UserClient')->find($clientData['id']);

            // if exist use the existing object
            // else remove id from client data for further form binding
            if (!is_null($adminClientExist)) {
                $adminClient = $adminClientExist;
            } else {
                unset($clientData['id']);
            }
        }

        // bind client data
        $form = $this->createForm(new UserClientType(), $adminClient);
        $form->submit($clientData, true);

        return $adminClient;
    }

    /**
     * @param User       $admin
     * @param UserClient $adminClient
     *
     * @return UserToken
     */
    private function saveAdminToken(
        $admin,
        $adminClient
    ) {
        $adminToken = $this->getRepo('User\UserToken')->findOneBy(array(
            'user' => $admin,
            'client' => $adminClient,
        ));

        if (is_null($adminToken)) {
            $adminToken = new UserToken();
            $adminToken->setUser($admin);
            $adminToken->setUserId($admin->getId());
            $adminToken->setClient($adminClient);
            $adminToken->setClientId($adminClient->getId());
            $adminToken->setToken($this->generateRandomToken());
        }

        // refresh data
        $adminToken->setOnline(true);
        $adminToken->setToken($this->generateRandomToken(self::PREFIX_ACCESS_TOKEN.$admin->getId()));
        $adminToken->setRefreshToken($this->generateRandomToken(self::PREFIX_FRESH_TOKEN.$admin->getId()));
        $adminToken->setModificationDate(new \DateTime('now'));

        return $adminToken;
    }

    /**
     * @param User          $admin
     * @param EntityManager $em
     *
     * @return object|UserCheckCode
     */
    private function saveUserCheckCode(
        $admin,
        $em
    ) {
        $checkCode = $this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH);

        $userCheckCode = $this->getRepo('User\UserCheckCode')
            ->findOneBy(
                array(
                    'phoneCode' => $admin->getPhoneCode(),
                    'phone' => $admin->getPhone(),
                    'type' => 0,
                )
            );

        //if user check code is existed, check expire date time
        if (is_null($userCheckCode)) {
            $userCheckCode = new UserCheckCode();
            $userCheckCode->setPhone($admin->getPhone());
            $userCheckCode->setPhoneCode($admin->getPhoneCode());
            $userCheckCode->setEmail($admin->getEmail());
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

    /**
     * @param $checkCode
     * @param $phone
     *
     * @return View|void
     */
    private function removeUserCheckCodeIfExited(
        $checkCode,
        $phone
    ) {
        $userCheckCode = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCheckCode')
            ->findOneBy(array(
                'code' => $checkCode,
                'phone' => $phone,
                'type' => 0,
            ));

        if (is_null($userCheckCode)) {
            return $this->customErrorView(
                400,
                self::ERROR_WRONG_CHECK_CODE_CODE,
                self::ERROR_WRONG_CHECK_CODE_MESSAGE
            );
        }

        // check expire in time
        $globals = $this->container->get('twig')->getGlobals();

        $expiredTime = $userCheckCode
            ->getCreationDate()
            ->modify($globals['expired_verification_time']);

        if (new \DateTime('now') > $expiredTime) {
            return $this->customErrorView(
                401,
                self::ERROR_EXPIRED_VERIFICATION_CODE,
                self::ERROR_EXPIRED_VERIFICATION_MESSAGE
            );
        }

        // remove User check code
        $em = $this->getDoctrine()->getManager();
        $em->remove($userCheckCode);
        $em->flush();

        return;
    }
}
