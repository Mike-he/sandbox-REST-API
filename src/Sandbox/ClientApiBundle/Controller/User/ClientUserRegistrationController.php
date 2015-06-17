<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserRegistrationController;
use Sandbox\ApiBundle\Entity\User\User;
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
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Registration controller
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 *
 * @Route("/register")
 */
class ClientUserRegistrationController extends UserRegistrationController
{
    use StringUtil;

    const ONE_DAY_IN_MILLIS = 86400000;

    /**
     * Registration submit
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
     * Registration verification
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
     * Registration get invitation info
     *
     * @param Request $request the request object
     * @param string  $token   the invitation token for registration
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/invite/{token}")
     * @Method({"GET"})
     *
     * @return string
     * @throws \Exception
     */
    public function getRegisterInviteAction(
        Request $request,
        $token
    ) {
        // get invitation by token
        $invitation = $this->getRepo('Invitation')->findOneByToken($token);
        $this->throwNotFoundIfNull($invitation, self::NOT_FOUND_MESSAGE);

        // set array for view
        $array = $this->setRegisterInviteArray(
            $invitation->getName(),
            $invitation->getEmail(),
            $invitation->getCountrycode(),
            $invitation->getPhone()
        );

        return new View($array);
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

        // TODO validate password with password rule

        // TODO one minute

        if (is_null($email)) {
            if (is_null($phone)) {
                return $this->customErrorView(400, 490, 'Missing phone number or email address');
            }
        } else {
            $phone = null;
        }

        if (!is_null($email)) {
            // check email valid
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->customErrorView(400, 491, 'Invalid email address');
            }

            // check email already used
            $user = $this->getRepo('User\User')->findOneByEmail($email);
            if (!is_null($user) && $user->getActivated()) {
                return $this->customErrorView(400, 492, 'Email address already used');
            }
        } else {
            // check country code and phone number valid
            if (is_null($phone)
                || !is_numeric($phone)) {
                return $this->customErrorView(400, 493, 'Invalid phone number');
            }

            // check phone number already used
            $user = $this->getRepo('User\User')->findOneBy(array(
                'phone' => $phone,
            ));
            if (!is_null($user) && $user->getActivated()) {
                return $this->customErrorView(400, 494, 'Phone number already used');
            }
        }

        $em = $this->getDoctrine()->getManager();

        $registration = new UserRegistration();
        $registration->setEmail($email);
        $registration->setPhone($phone);
        $registration->setCode($this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH));

        $em->persist($registration);
        $em->flush();

        if (!is_null($email)) {
            // send verification URL to email
            $subject = '[Sandbox Registration],'.$this->before('@', $registration->getEmail()).' please confirm your email';
            $this->sendEmail($subject, $email, $this->before('@', $registration->getEmail()),
                    'Emails/registration_email_verification.html.twig',
                    array(
                        'code' => $registration->getCode(),
                    ));
        } else {
            // sms verification code to phone
            $smsText = 'Verification code: '.$registration->getCode();
            $this->sendSms($phone, urlencode($smsText));
        }
    }

    /**
     * @param  RegisterVerify $verify
     * @return View
     */
    private function handleRegisterVerify(
        $verify
    ) {
        $email = $verify->getEmail();
        $phone = $verify->getPhone();
        $password = $verify->getPassword();
        $code = $verify->getCode();

        // check verify is valid
        $userRegistration = $this->checkVerificationValid($email, $phone, $password, $code);

        // generate user entity
        $user = $this->generateUser($email, $phone, $password, $userRegistration->getId());

        // generate user vcard
        //$vcard = $this->generateVCard();

        $em = $this->getDoctrine()->getManager();
        //$em->persist($vcard);
        $em->persist($user);
        $em->remove($userRegistration);
        $em->flush();

        // response
        $view = new View();
        $view->setData(array(
            'result' => true,
        ));

        return $view;
    }

    /**
     * @param  string           $email
     * @param  string           $phone
     * @param  string           $password
     * @param  string           $code
     * @return UserRegistration object
     */
    private function checkVerificationValid(
        $email,
        $phone,
        $password,
        $code
    ) {
        if (is_null($password)
            || is_null($code)
            || (is_null($email) && is_null($phone)
                || (!is_null($email) && !is_null($phone)))) {
            return $this->customErrorView(400, 490, 'Invalid verification');
        }

        // get registration entity
        $registration = $this->getRepo('User\UserRegistration')->findOneBy(array(
            'email' => $email,
            'phone' => $phone,
        ));

        if (is_null($registration)) {
            return $this->customErrorView(400, 490, 'Invalid email or phone');
        }

        // get registration entity
        $registration = $this->getRepo('User\UserRegistration')->findOneBy(array(
            'email' => $email,
            'phone' => $phone,
            'code' => $code,
        ));

        if (is_null($registration)) {
            return $this->customErrorView(400, 490, 'Invalid verification code');
        }

        // check token validation time
        $currentTime = time().'000';
        if ($currentTime - $registration->getCreationDate() > self::ONE_DAY_IN_MILLIS) {
            return $this->customErrorView(400, 491, 'Expired verification');
        }

        return $registration;
    }

    /**
     * @param  string $email
     * @param  string $phone
     * @param  string $password
     * @param  int    $registrationId
     * @return User   User
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

        // get xmppUsername  from response
        $response = $this->createXmppUser($user, $registrationId);
        $responseJSON = json_decode($response);
        $xmppUsername = $responseJSON->username;
        $user->setXmppUsername($xmppUsername);

        return $user;
    }

    private function generateVCard()
    {
        // TODO
    }

    /**
     * @param  User  $user
     * @param  int registrationId
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
        $apiUrl = $globals['openfire_innet_protocol'].
            $globals['openfire_innet_address'].
            $globals['openfire_innet_port'].
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
        $response = $this->callAPI($ch, $jsonData, $basicAuth, 'POST');

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->throwBadRequestIfCallApiFailed($httpCode);

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
     * @param JtUser $user
     *
     * @return array|null
     */
    private function getPendingInvitations(
        $user
    ) {
        $invitations = null;

        $email = $user->getEmail();
        $countryCode = $user->getCountrycode();
        $phone = $user->getPhone();

        if (!is_null($email)) {
            $invitations = $this->getRepo('Invitation')->findBy(array(
                'email' => $email,
                'status' => 'pending',
            ));
        } elseif (!is_null($countryCode) || !is_null($phone)) {
            $invitations = $this->getRepo('Invitation')->findBy(array(
                'countrycode' => $countryCode,
                'phone' => $phone,
                'status' => 'pending',
            ));
        }

        return $invitations;
    }

    /**
     * @param $name
     * @param $email
     * @param $countryCode
     * @param $phone
     * @return array
     */
    private function setRegisterInviteArray(
        $name,
        $email,
        $countryCode,
        $phone
    ) {
        $array = array();

        if (!is_null($name)) {
            $array['name'] = $name;
        }

        if (!is_null($email)) {
            $array['email'] = $email;
        }

        if (!is_null($countryCode)) {
            $array['countrycode'] = $countryCode;
        }

        if (!is_null($phone)) {
            $array['phone'] = $phone;
        }

        return $array;
    }
}
