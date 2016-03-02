<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserRegistrationController;
use Sandbox\ApiBundle\Entity\Buddy\Buddy;
use Sandbox\ApiBundle\Entity\ThirdParty\WeChat;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserClient;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Sandbox\ApiBundle\Entity\User\UserToken;
use Sandbox\ApiBundle\Traits\WeChatApi;
use Sandbox\ApiBundle\Traits\YunPianSms;
use Sandbox\ApiBundle\Traits\StringUtil;
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
    use StringUtil;
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
            }
        }

        $em = $this->getDoctrine()->getManager();

        // generate registration entity
        $registration = $this->generateRegistration($email, $phone);

        $em->persist($registration);
        $em->flush();

        // send verification code by email or sms
        $this->sendNotification($registration->getEmail(),
            $registration->getPhone(),
            $registration->getCode());

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
        $phone = $verify->getPhone();
        $code = $verify->getCode();
        $password = $verify->getPassword();
        $weChatData = $verify->getWeChat();

        // get registration by (email / phone) with code
        $registration = $this->getMyRegistration($email, $phone, $code);
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

        // so far, code is verified
        // if password not provided, stop here
        if (is_null($password) || empty($password)) {
            // update db
            $em->flush();

            // response
            return new View();
        }

        $user = $this->finishRegistration($em, $email, $phone, $password, $registration);

        // so far, user is created
        // if third party login not provided, stop here
        if (!$this->hasThirdPartyLogin($weChatData)) {
            // update db
            $em->flush();

            // response
            return new View();
        }

        // bind third party resource, handle it as a login
        // and give authorization info in response
        $responseArray = $this->finishRegistrationWithThirdPartyLogin($em, $user, $weChatData);

        // update db
        $em->flush();

        // response
        $view = new View($responseArray);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('login')));

        return $view;
    }

    /**
     * @param $email
     * @param $phone
     * @param $code
     *
     * @return UserRegistration
     */
    private function getMyRegistration(
        $email,
        $phone,
        $code
    ) {
        // code is required
        // email or phone, only one of them should be provided, not none, not both
        if (is_null($code)
            || ((is_null($email) && is_null($phone))
                || (!is_null($email) && !is_null($phone)))) {
            return;
        }

        return $this->getRepo('User\UserRegistration')->findOneBy(array(
            'email' => $email,
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
     * @param string           $email
     * @param string           $phone
     * @param string           $password
     * @param UserRegistration $registration
     *
     * @return User
     */
    private function finishRegistration(
        $em,
        $email,
        $phone,
        $password,
        $registration
    ) {
        // generate user entity
        $user = $this->generateUser($email, $phone, $password, $registration->getId());
        $em->persist($user);

        // post user account to internal api
        $this->postUserAccount($user->getId());

        // create default profile
        $profile = new UserProfile();
        $profile->setName('');
        $profile->setUser($user);
        $em->persist($profile);

        // remove registration
        $em->remove($registration);

        // add service account to buddy list
        $this->addBuddyToUser(array($user));

        return $user;
    }

    /**
     * @param string $email
     * @param string $phone
     * @param string $password
     * @param int    $registrationId
     *
     * @return User User
     */
    private function generateUser(
        $email,
        $phone,
        $password,
        $registrationId
    ) {
        $user = new User();
        $user->setPassword($password);

        if (!is_null($email)) {
            $user->setEmail($email);
        } else {
            $user->setPhone($phone);
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
     *
     * @return UserRegistration UserRegistration
     */
    private function generateRegistration(
        $email,
        $phone
    ) {
        $registration = $this->getRepo('User\UserRegistration')->findOneBy(array(
                'email' => $email,
                'phone' => $phone,
            )
        );
        if (is_null($registration)) {
            $registration = new UserRegistration();
            $registration->setEmail($email);
            $registration->setPhone($phone);
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
        $response = $this->get('curl_util')->callAPI(
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
     */
    private function sendNotification(
        $email,
        $phone,
        $code
    ) {
        if (!is_null($email)) {
            // send verification URL to email
            $subject = '【展想创合】'.$this->before('@', $email).'，欢迎注册展想创合！';
            $this->sendEmail($subject, $email, $this->before('@', $email),
                'Emails/registration_email_verification.html.twig',
                array(
                    'code' => $code,
                ));
        } else {
            // sms verification code to phone
            $smsText = '【展想创合】欢迎注册展想创合！您的手机验证码为：'.$code.'，请输入后进行验证，谢谢！验证码在10分钟内有效。';

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

        if (is_null($weChatData->getOpenId()) || is_null($weChatData->getAccessToken())) {
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
    private function finishRegistrationWithThirdPartyLogin(
        $em,
        $user,
        $weChatData
    ) {
        $openId = $weChatData->getOpenId();
        $accessToken = $weChatData->getAccessToken();

        $weChat = $this->getRepo('ThirdParty\WeChat')->findOneBy(array(
            'openid' => $openId,
            'accessToken' => $accessToken,
        ));
        if (is_null($weChat)) {
            return array();
        }

        // do oauth with WeChat api with openId and accessToken
        $this->doWeChatAuthByOpenIdAccessToken($openId, $accessToken);

        return $this->saveAuthForThirdPartyLogin($em, $user, $weChat);
    }

    /**
     * @param EntityManager $em
     * @param User          $user
     * @param WeChat        $weChat
     *
     * @return array
     */
    private function saveAuthForThirdPartyLogin(
        $em,
        $user,
        $weChat
    ) {
        // bind WeChat with user
        $now = new \DateTime();

        $weChat->setUser($user);
        $weChat->setModificationDate($now);

        // create auth for user login with third party oauth
        $userClient = $weChat->getUserClient();
        if (is_null($userClient)) {
            $userClient = new UserClient();
            $userClient->setCreationDate($now);
            $userClient->setModificationDate($now);

            $em->persist($userClient);

            $weChat->setUserClient($userClient);
        }

        $userToken = new UserToken();
        $userToken->setUser($user);
        $userToken->setUserId($user->getId());
        $userToken->setClient($userClient);
        $userToken->setClientId($userClient->getId());
        $userToken->setToken($this->generateRandomToken());
        $userToken->setOnline(true);
        $userToken->setCreationDate(new \DateTime('now'));

        $em->persist($userToken);

        // response
        return array(
            'client' => $userClient,
            'token' => $userToken,
            'user' => $user,
        );
    }
}
