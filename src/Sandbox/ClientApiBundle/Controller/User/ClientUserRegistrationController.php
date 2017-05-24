<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserRegistrationController;
use Sandbox\ApiBundle\Entity\Buddy\Buddy;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sandbox\ApiBundle\Entity\ThirdParty\WeChat;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserClient;
use Sandbox\ApiBundle\Entity\User\UserPhoneCode;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Sandbox\ApiBundle\Traits\WeChatApi;
use Sandbox\ApiBundle\Traits\YunPianSms;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;
use Sandbox\ClientApiBundle\Data\User\RegisterSubmit;
use Sandbox\ClientApiBundle\Data\User\RegisterVerify;
use Sandbox\ApiBundle\Entity\User\UserRegistration;
use Sandbox\ClientApiBundle\Form\User\RegisterSubmitType;
use Sandbox\ClientApiBundle\Form\User\RegisterVerifyType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializationContext;

/**
 * Registration controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 *
 * @Route("/register")
 */
class ClientUserRegistrationController extends UserRegistrationController
{
    // Traits
    use YunPianSms;
    use WeChatApi;

    // Constants
    const ERROR_MISSING_PHONE_OR_EMAIL_CODE = 400001;
    const ERROR_MISSING_PHONE_OR_EMAIL_MESSAGE = 'register.submit.missing_email_phone';

    const ERROR_INVALID_EMAIL_ADDRESS_CODE = 400002;
    const ERROR_INVALID_EMAIL_ADDRESS_MESSAGE = 'register.submit.invalid_email';

    const ERROR_INVALID_PHONE_CODE = 400004;
    const ERROR_INVALID_PHONE_MESSAGE = 'register.submit.invalid_phone';

    const ERROR_INVALID_VERIFICATION_CODE = 400006;
    const ERROR_INVALID_VERIFICATION_MESSAGE = 'register.verify.invalid_verification';

    const ERROR_EXPIRED_VERIFICATION_CODE = 400007;
    const ERROR_EXPIRED_VERIFICATION_MESSAGE = 'register.verify.expired_verification';

    const ERROR_INVALID_INVITER_CODE = 400008;
    const ERROR_INVALID_INVITER_MESSAGE = 'register.verify.invalid_inviter';

    /**
     * Registration submit.
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
     * @Route("/submit")
     * @Method({"POST"})
     *
     * @return string
     *
     * @throws \Exception
     */
    public function postRegisterSubmitAction(
        Request $request
    ) {
        $submit = new RegisterSubmit();

        $form = $this->createForm(new RegisterSubmitType(), $submit);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handleRegisterSubmit($submit);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Registration verification.
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
     * @Route("/verify")
     * @Method({"POST"})
     *
     * @return string
     *
     * @throws \Exception
     */
    public function postRegisterVerifyAction(
        Request $request
    ) {
        $verify = new RegisterVerify();

        $form = $this->createForm(new RegisterVerifyType(), $verify);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handleRegisterVerify($verify);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * @param RegisterSubmit $submit
     *
     * @return View
     */
    private function handleRegisterSubmit(
        $submit
    ) {
        $email = $submit->getEmail();
        $phone = $submit->getPhone();
        $phoneCode = $submit->getPhoneCode();

        if (is_null($email)) {
            if (is_null($phone)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_MISSING_PHONE_OR_EMAIL_CODE,
                    self::ERROR_MISSING_PHONE_OR_EMAIL_MESSAGE
                );
            }
        } else {
            $phone = null;
        }

        if (!is_null($email)) {
            // check email valid
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_INVALID_EMAIL_ADDRESS_CODE,
                    self::ERROR_INVALID_EMAIL_ADDRESS_MESSAGE
                );
            }
        } else {
            // check  and phone number valid
            if (is_null($phone)
                || !is_numeric($phone)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_INVALID_PHONE_CODE,
                    self::ERROR_INVALID_PHONE_MESSAGE
                );
            } else {
                if (is_null($phoneCode)) {
                    $phoneCode = UserPhoneCode::DEFAULT_PHONE_CODE;
                }
            }
        }

        $em = $this->getDoctrine()->getManager();

        // generate registration entity
        $registration = $this->generateRegistration($email, $phone, $phoneCode);

        $em->persist($registration);
        $em->flush();

        $formalPhone = $registration->getPhoneCode().$registration->getPhone();

        // send verification code by email or sms
        $this->sendNotification(
            $registration->getEmail(),
            $formalPhone,
            $registration->getCode(),
            $registration->getPhoneCode()
        );

        return new View();
    }

    /**
     * @param RegisterVerify $verify
     *
     * @return View
     */
    private function handleRegisterVerify(
        $verify
    ) {
        $em = $this->getDoctrine()->getManager();

        $email = $verify->getEmail();
        $phoneCode = $verify->getPhoneCode();
        $phone = $verify->getPhone();
        $code = $verify->getCode();
        $password = $verify->getPassword();
        $weChatData = $verify->getWeChat();
        $inviterUserId = $verify->getInviterUserId();
        $inviterPhone = $verify->getInviterPhone();

        // get registration by (email / phone) with code
        $registration = $this->getMyRegistration($email, $phone, $code, $phoneCode);
        if (is_null($registration)) {
            return $this->customErrorView(
                400,
                self::ERROR_INVALID_VERIFICATION_CODE,
                self::ERROR_INVALID_VERIFICATION_MESSAGE
            );
        }

        // check code validation time
        $registration = $this->verifyRegistration($registration);
        if (is_null($registration)) {
            return $this->customErrorView(
                400,
                self::ERROR_EXPIRED_VERIFICATION_CODE,
                self::ERROR_EXPIRED_VERIFICATION_MESSAGE
            );
        }

        $inviter = null;

        // check inviter user
        if (!is_null($inviterUserId)) {
            $inviter = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->find($inviterUserId);
            if (is_null($inviter)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_INVALID_INVITER_CODE,
                    self::ERROR_INVALID_INVITER_MESSAGE
                );
            }
        }

        // check inviter phone
        if (!is_null($inviterPhone)) {
            $inviter = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->findOneBy(array(
                    'phone' => $inviterPhone,
                ));
            if (is_null($inviter)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_INVALID_INVITER_CODE,
                    self::ERROR_INVALID_INVITER_MESSAGE
                );
            }
        }

        // so far, code is verified
        // get existing user or create a new user
        $userArray = $this->finishRegistration($em, $password, $registration, $inviter);
        $user = $userArray['user'];
        $isRegistration = $userArray['is_registration'];
        if (is_null($user)) {
            // update db
            $em->flush();

            // response
            return new View();
        }

        // bind third party resource, handle it as a login
        // and give authorization info in response
        $responseArray = $this->handleThirdPartyLogin($em, $user, $weChatData);

        // update db
        $em->flush();

        // post user account to internal api
        $this->postUserAccount($user->getId());

        $em->flush();

        if ($isRegistration) {
            // add bean
            $parameter = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Parameter\Parameter')
                ->findOneBy(array('key' => Parameter::KEY_BEAN_USER_REGISTER));
            $responseArray = array_merge($responseArray, array(
                'bean_user_register' => $parameter->getValue(),
            ));
        }

        // response
        $view = new View($responseArray);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('login')));

        return $view;
    }

    /**
     * @param $email
     * @param $phone
     * @param $code
     * @param $phoneCode
     *
     * @return UserRegistration
     */
    private function getMyRegistration(
        $email,
        $phone,
        $code,
        $phoneCode
    ) {
        // code is required
        // email or phone, only one of them should be provided, not none, not both
        if (is_null($code)
            || ((is_null($email) && is_null($phone))
                || (!is_null($email) && !is_null($phone)))) {
            return;
        }

        if (!is_null($phone) && is_null($phoneCode)) {
            $phoneCode = UserPhoneCode::DEFAULT_PHONE_CODE;
        }

        return $this->getRepo('User\UserRegistration')->findOneBy(array(
            'email' => $email,
            'phoneCode' => $phoneCode,
            'phone' => $phone,
            'code' => $code,
        ));
    }

    /**
     * @param UserRegistration $registration
     *
     * @return UserRegistration
     */
    private function verifyRegistration(
        $registration
    ) {
        $globals = $this->container->get('twig')->getGlobals();
        $maxTokenTime = $globals['expired_verification_time'];

        $now = new \DateTime('now');
        if ($now > $registration->getCreationDate()->modify($maxTokenTime)) {
            return;
        }

        $registration->setCreationDate($now);

        return $registration;
    }

    /**
     * @param EntityManager    $em
     * @param string           $password
     * @param UserRegistration $registration
     * @param User             $inviter
     *
     * @return array
     */
    private function finishRegistration(
        $em,
        $password,
        $registration,
        $inviter
    ) {
        $user = null;

        $phone = $registration->getPhone();
        $phoneCode = $registration->getPhoneCode();
        $email = $registration->getEmail();
        $registrationId = $registration->getId();

        if (!is_null($email)) {
            $user = $this->getRepo('User\User')->findOneByEmail($email);
        } else {
            $user = $this->getRepo('User\User')->findOneByPhone($phone);
        }

        $isRegistration = false;
        if (is_null($user) && !is_null($password)) {
            // generate user
            $user = $this->generateUser($email, $phone, $password, $registrationId, $phoneCode, $inviter);
            $em->persist($user);

            // create default profile
            $profile = new UserProfile();
            $profile->setName('');
            $profile->setUser($user);
            $em->persist($profile);

            // add service account to buddy list
            $this->addBuddyToUser(array($user));

            $isRegistration = true;
        }

        if (!is_null($user)) {
            // remove registration
            $em->remove($registration);
        }

        $em->flush();

        if ($isRegistration) {
            //update user bean
            $this->get('sandbox_api.bean')->postBeanChange(
                $user->getId(),
                0,
                null,
                Parameter::KEY_BEAN_USER_REGISTER
            );

            //update invitee bean
            if ($inviter) {
                $this->get('sandbox_api.bean')->postBeanChange(
                    $inviter->getId(),
                    0,
                    null,
                    Parameter::KEY_BEAN_SUCCESS_INVITATION
                );
            }
        }

        return array(
            'user' => $user,
            'is_registration' => $isRegistration,
        );
    }

    /**
     * @param string $email
     * @param string $phone
     * @param string $password
     * @param int    $registrationId
     * @param string $phoneCode
     * @param User   $inviter
     *
     * @return User User
     */
    private function generateUser(
        $email,
        $phone,
        $password,
        $registrationId,
        $phoneCode,
        $inviter
    ) {
        $user = new User();
        $user->setPassword($password);

        if (!is_null($email)) {
            $user->setEmail($email);
        } else {
            $user->setPhone($phone);
            $user->setPhoneCode($phoneCode);
        }

        // set inviter
        if (!is_null($inviter)) {
            $user->setInviterId($inviter->getId());
        }

        // get xmppUsername from response
        $response = $this->createXmppUser($user, $registrationId);
        $responseJson = json_decode($response);
        $user->setXmppUsername($responseJson->username);

        return $user;
    }

    /**
     * @param string $email
     * @param string $phone
     * @param string $phoneCode
     *
     * @return UserRegistration UserRegistration
     */
    private function generateRegistration(
        $email,
        $phone,
        $phoneCode
    ) {
        $registration = $this->getRepo('User\UserRegistration')->findOneBy(array(
                'email' => $email,
                'phone' => $phone,
                'phoneCode' => $phoneCode,
            )
        );
        if (is_null($registration)) {
            $registration = new UserRegistration();
            $registration->setEmail($email);
            $registration->setPhone($phone);
            $registration->setPhoneCode($phoneCode);
        }
        $registration->setCreationDate(new \DateTime('now'));
        $registration->setCode($this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH));

        return $registration;
    }

    /**
     * @param User $user
     * @param int  $registrationId
     *
     * @return mixed
     */
    private function createXmppUser(
        $user,
        $registrationId
    ) {
        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // Openfire API URL
        $apiUrl = $globals['openfire_innet_url'].
            $globals['openfire_plugin_bstuser'].
            $globals['openfire_plugin_bstuser_users'];

        // generate username
        $username = strval(1000000 + $registrationId);

        // request json
        $jsonData = $this->createJsonData(
            $username,
            $user->getPassword()
        );

        // set ezUser secret to basic auth
        $userNameSecret = $globals['openfire_plugin_bstuser_property_name_ezuser'].':'.
            $globals['openfire_plugin_bstuser_property_secret_ezuser'];

        $basicAuth = 'Basic '.base64_encode($userNameSecret);

        // init curl
        $ch = curl_init($apiUrl);

        // get then response when post OpenFire API
        $response = $this->callAPI(
            $ch,
            'POST',
            array('Authorization: '.$basicAuth),
            $jsonData);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        return $response;
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return string
     */
    private function createJsonData(
        $username,
        $password
    ) {
        $dataArray = array();
        $dataArray['username'] = $username;
        $dataArray['password'] = $password;

        return json_encode($dataArray);
    }

    /**
     * @param string $email
     * @param string $phone
     * @param string $code
     * @param string $phoneCode
     */
    private function sendNotification(
        $email,
        $phone,
        $code,
        $phoneCode
    ) {
        if (!is_null($email)) {
            // send verification URL to email
            $subject = '【创合秒租】'.$this->before('@', $email).'，欢迎注册展想创合！';
            $this->sendEmail($subject, $email, $this->before('@', $email),
                'Emails/registration_email_verification.html.twig',
                array(
                    'code' => $code,
                ));
        } else {
            // sms verification code to phone
            if (UserPhoneCode::DEFAULT_PHONE_CODE == $phoneCode) {
                // default chinese message
                $smsText = self::ZH_SMS_BEFORE.$code.self::ZH_SMS_AFTER;
            } else {
                // other country use english message
                $smsText = self::EN_SMS_BEFORE.$code.self::EN_SMS_AFTER;
            }

            $this->send_sms($phone, $smsText);
        }
    }

    /**
     * @param ThirdPartyOAuthWeChatData $weChatData
     *
     * @return bool
     */
    private function hasThirdPartyLogin(
        $weChatData
    ) {
        // today, we only have WeChat login
        if (is_null($weChatData)) {
            return false;
        }

        if (is_null($weChatData->getCode())) {
            return false;
        }

        return true;
    }

    /**
     * @param EntityManager             $em
     * @param User                      $user
     * @param ThirdPartyOAuthWeChatData $weChatData
     *
     * @return array
     */
    private function handleThirdPartyLogin(
        $em,
        $user,
        $weChatData = null
    ) {
        $weChat = null;

        if ($this->hasThirdPartyLogin($weChatData)) {
            $weChat = $this->getRepo('ThirdParty\WeChat')->findOneByAuthCode($weChatData->getCode());
            if (is_null($weChat)) {
                $this->throwNotFoundIfNull($weChat, self::NOT_FOUND_MESSAGE);
            }

            // do oauth with WeChat api with openId and accessToken
            $this->throwUnauthorizedIfWeChatAuthFail($weChat);
        }

        return $this->saveAuthForResponse($em, $user, $weChat);
    }

    /**
     * @param EntityManager $em
     * @param User          $user
     * @param WeChat        $weChat
     *
     * @return array
     */
    private function saveAuthForResponse(
        $em,
        $user,
        $weChat = null
    ) {
        $now = new \DateTime();

        // create auth for user login with third party oauth
        $userClient = null;

        if (!is_null($weChat)) {
            $userClient = $weChat->getUserClient();
        }

        if (is_null($userClient)) {
            $userClient = new UserClient();
            $userClient->setCreationDate($now);
            $userClient->setModificationDate($now);

            $em->persist($userClient);
            $em->flush();
        }

        $userToken = $this->saveUserToken($em, $user, $userClient);

        // update WeChat if any
        $this->updateWeChatBinding($em, $weChat, $user, $userClient, $now);

        // response
        return array(
            'client' => $userClient,
            'token' => $userToken,
            'user' => $user,
        );
    }

    /**
     * @param EntityManager $em
     * @param WeChat        $weChat
     * @param User          $user
     * @param UserClient    $userClient
     * @param \DateTime     $now
     */
    private function updateWeChatBinding(
        $em,
        $weChat,
        $user,
        $userClient,
        $now
    ) {
        if (is_null($weChat)) {
            return;
        }

        if (!is_null($weChat->getUser())) {
            return;
        }

        $myWeChat = $this->getRepo('ThirdParty\WeChat')->findOneByUser($user);
        if (!is_null($myWeChat)) {
            $myWeChat->setUser(null);
            $em->flush();
        }

        $weChat->setUser($user);
        $weChat->setUserClient($userClient);
        $weChat->setModificationDate($now);
    }
}
