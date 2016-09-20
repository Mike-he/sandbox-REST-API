<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use JMS\Serializer\SerializationContext;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\ApiBundle\Entity\Error\Error;
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

    const VERIFICATION_CODE_LENGTH = 6;
    const ZH_SMS_BEFORE = '【展想创合】您正在登陆管理员账号，如确认是本人行为，请提交以下验证码完成操作：';
    const ZH_SMS_AFTER = '。验证码在10分钟内有效。';

    const EN_SMS_BEFORE = '【Sandbox3】Your verification code is ';
    const EN_SMS_AFTER = '. The verification code will be expired after 10 minutes.';

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

        // save or update user check code
        $userCheckCode = $this->saveUserCheckCode($admin, $em);
        if (is_null($userCheckCode->getId())) {
            $em->persist($userCheckCode);
            $em->flush();
        }

        // send verification code by sms
        $this->sendSMSNotification(
            $userCheckCode
        );

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

        return $this->handleAdminUserLogin($request, $admin);
    }

    /**
     * @param Request $request
     * @param User    $admin
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
            setrawcookie(self::ADMIN_COOKIE_NAME, $adminToken->getToken(), null, '/', $request->getHost());

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
                )
            );

        //if user check code is existed, check expire date time
        if (!is_null($userCheckCode)) {
            $globals = $this->container->get('twig')->getGlobals();

            if (
                new \DateTime('now') < $userCheckCode
                    ->getCreationDate()
                    ->modify($globals['expired_verification_time'])
            ) {
                return $userCheckCode;
            } else {
                // if the date time is expired, update code and creation date
                $userCheckCode->setCode($checkCode);
                $userCheckCode->setCreationDate(new \DateTime('now'));

                $em->persist($userCheckCode);
                $em->flush();

                return $userCheckCode;
            }
        }

        $userCheckCode = new UserCheckCode($admin->getId());
        $userCheckCode->setPhone($admin->getPhone());
        $userCheckCode->setPhoneCode($admin->getPhoneCode());
        $userCheckCode->setEmail($admin->getEmail());
        $userCheckCode->setCode($checkCode);

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

    private function handlePositionData($positions)
    {
        $platform = array();
        foreach ($positions as $position) {
            switch ($position['platform']) {
                case 'shop':
                    $platform['shop'][] = $position;
                    break;
                case 'sales':
                    $platform['sales'][] = $position;
                    break;
                default:
                    $platform['official'][] = $position;
            }
        }

        return $platform;
    }

    private function removeUserCheckCodeIfExited($checkCode, $phone)
    {
        $userCheckCode = $this->getRepo('User\UserCheckCode')->findOneBy(
            array(
                'code' => $checkCode,
                'phone' => $phone,
            )
        );

        if (is_null($userCheckCode)) {
            return $this->customErrorView(
                401,
                self::ERROR_WRONG_CHECK_CODE_CODE,
                self::ERROR_WRONG_CHECK_CODE_MESSAGE
            );
        }

        // remove User check code
        $this->getDoctrine()->getManager()->remove($userCheckCode);
        $this->getDoctrine()->getManager()->flush();

        return;
    }
}
