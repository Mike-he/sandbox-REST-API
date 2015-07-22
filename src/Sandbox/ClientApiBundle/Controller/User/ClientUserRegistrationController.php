<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserRegistrationController;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Sandbox\ApiBundle\Traits\StringUtil;
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
    use StringUtil;

    const ERROR_MISSING_PHONE_OR_EMAIL_CODE = 400001;
    const ERROR_MISSING_PHONE_OR_EMAIL_MESSAGE = 'register.submit.missing_email_phone';

    const ERROR_INVALID_EMAIL_ADDRESS_CODE = 400002;
    const ERROR_INVALID_EMAIL_ADDRESS_MESSAGE = 'register.submit.invalid_email';

    const ERROR_EMAIL_ALREADY_USED_CODE = 400003;
    const ERROR_EMAIL_ALREADY_USED_MESSAGE = 'register.submit.used_email';

    const ERROR_INVALID_PHONE_CODE = 400004;
    const ERROR_INVALID_PHONE_MESSAGE = 'register.submit.invalid_phone';

    const ERROR_PHONE_ALREADY_USED_CODE = 400005;
    const ERROR_PHONE_ALREADY_USED_CODE_MESSAGE = 'register.submit.used_phone';

    const ERROR_INVALID_VERIFICATION_CODE = 400006;
    const ERROR_INVALID_VERIFICATION_MESSAGE = 'register.verify.invalid_verification';

    const ERROR_EXPIRED_VERIFICATION_CODE = 400007;
    const ERROR_EXPIRED_VERIFICATION_MESSAGE = 'register.verify.expired_verification';

    const PLUS_ONE_DAY = '+1 day';

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

            // check email already used
            $user = $this->getRepo('User\User')->findOneByEmail($email);
            if (!is_null($user)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_EMAIL_ALREADY_USED_CODE,
                    self::ERROR_EMAIL_ALREADY_USED_MESSAGE
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

            // check phone number already used
            $user = $this->getRepo('User\User')->findOneByPhone($phone);
            if (!is_null($user)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_PHONE_ALREADY_USED_CODE,
                    self::ERROR_PHONE_ALREADY_USED_CODE_MESSAGE
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
        $email = $verify->getEmail();
        $phone = $verify->getPhone();
        $password = $verify->getPassword();
        $code = $verify->getCode();

        if (is_null($password)
            || is_null($code)
            || (is_null($email) && is_null($phone)
                || (!is_null($email) && !is_null($phone)))) {
            return $this->customErrorView(
                400,
                self::ERROR_INVALID_VERIFICATION_CODE,
                self::ERROR_INVALID_VERIFICATION_MESSAGE
            );
        }

        // get registration entity
        $registration = $this->getRepo('User\UserRegistration')->findOneBy(array(
            'email' => $email,
            'phone' => $phone,
            'code' => $code,
        ));

        if (is_null($registration)) {
            return $this->customErrorView(
                400,
                self::ERROR_INVALID_VERIFICATION_CODE,
                self::ERROR_INVALID_VERIFICATION_MESSAGE
            );
        }

        // check token validation time
        if (new \DateTime('now') > $registration->getCreationDate()->modify('+1 day')) {
            return $this->customErrorView(
                400,
                self::ERROR_EXPIRED_VERIFICATION_CODE,
                self::ERROR_EXPIRED_VERIFICATION_MESSAGE
            );
        }

        // generate user entity
        $user = $this->generateUser($email, $phone, $password, $registration->getId());

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        // create default profile
        $profile = new UserProfile();

        $defaultName =
            $this->getGlobal('user_profile_default_name_prefix').$user->getId();
        $profile->setName($defaultName);

        $profile->setUser($user);
        $em->persist($profile);

        // remove registration
        $em->remove($registration);
        $em->flush();

        return new View();
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
        $response = $this->get('curl_util')->callAPI($ch, 'POST', $basicAuth, $jsonData);

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
            $subject = '[Sandbox Registration],'.$this->before('@', $email).' please confirm your email';
            $this->sendEmail($subject, $email, $this->before('@', $email),
                'Emails/registration_email_verification.html.twig',
                array(
                    'code' => $code,
                ));
        } else {
            // sms verification code to phone
            $smsText = 'Verification code: '.$code;
            $this->sendSms($phone, urlencode($smsText));
        }
    }
}
