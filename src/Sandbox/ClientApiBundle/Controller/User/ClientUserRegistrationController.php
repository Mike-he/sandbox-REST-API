<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserRegistrationController;
use Sandbox\ClientApiBundle\Data\User\RegisterSubmit;
use Sandbox\ClientApiBundle\Data\User\RegisterVerify;
use Sandbox\ApiBundle\Entity\User\UserRegistration;
use Sandbox\ClientApiBundle\Form\RegisterSubmitType;
use Sandbox\ClientApiBundle\Form\RegisterVerifyType;
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
        $form->submit(json_decode($request->getContent(), true));

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
        $form->submit(json_decode($request->getContent(), true));

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
        $countryCode = $submit->getCountrycode();
        $phone = $submit->getPhone();
        $password = $submit->getPassword();
        $name = $submit->getName();

        if (is_null($name)) {
            return $this->customErrorView(400, 495, 'Invalid name');
        }

        if (is_null($password)) {
            return $this->customErrorView(400, 496, 'Invalid password');
        }

        // TODO validate password with password rule

        if (is_null($email)) {
            if (is_null($countryCode) || is_null($phone)) {
                return $this->customErrorView(400, 490, 'Missing phone number or email address');
            }
        } else {
            $countryCode = null;
            $phone = null;
        }

        $registration = new UserRegistration();

        if (!is_null($email)) {
            // check email valid
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->customErrorView(400, 491, 'Invalid email address');
            }

            // check email already used
            $user = $this->getRepo('JtUser')->findOneByEmail($email);
            if (!is_null($user) && $user->getActivated()) {
                return $this->customErrorView(400, 492, 'Email address already used');
            }
        } else {
            // check country code and phone number valid
            if (is_null($countryCode) || is_null($phone)
                || !is_numeric($countryCode) || !is_numeric($phone)) {
                return $this->customErrorView(400, 493, 'Invalid phone number');
            }

            // check phone number already used
            $user = $this->getRepo('JtUser')->findOneBy(array(
                'countrycode' => $countryCode,
                'phone' => $phone,
            ));
            if (!is_null($user) && $user->getActivated()) {
                return $this->customErrorView(400, 494, 'Phone number already used');
            }
        }

        $em = $this->getDoctrine()->getManager();

        $newRegistration = false;
        if (is_null($user)) {
            $newRegistration = true;

            $user = new JtUser();
            $user->setPassword($password);

            if (!is_null($email)) {
                $user->setEmail($email);
            } else {
                $user->setCountrycode($countryCode);
                $user->setPhone($phone);
            }

            $user->setActivated(false);
            $time = time();
            $user->setCreationdate($time);
            $user->setModificationdate($time);

            $em->persist($user);
            $em->flush();
        } else {
            $registration = $this->getRepo('UserRegistration')->findOneByUserid($user->getId());
        }

        $registration->setUserid($user->getId());
        $registration->setName($name);
        $registration->setToken($this->generateRandomToken());
        $registration->setCode($this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH));
        $registration->setCreationdate(time());

        if ($newRegistration) {
            $em->persist($registration);
        }
        $em->flush();

        if (!is_null($email)) {
            // send verification URL to email
            $subject = '[EasyLinks Registration] '.$name.', please confirm your email';
            $this->sendEmail($subject, $email, $name,
                    'Emails/registration_email_verification.html.twig',
                    array(
                        'token' => $registration->getToken(),
                        'code' => $registration->getCode(),
                    ));
        } else {
            // sms verification code to phone
            $smsText = 'Verification code: '.$registration->getCode();
            $this->sendSms($phone, urlencode($smsText));
        }

        // response
        $view = new View();
        $view->setData(array(
            'token' => $registration->getToken(),
        ));

        return $view;
    }

    /**
     * @param  RegisterVerify $verify
     * @return View
     */
    private function handleRegisterVerify(
        $verify
    ) {
        $token = $verify->getToken();
        $code = $verify->getCode();

        if (is_null($token) || is_null($code)) {
            return $this->customErrorView(400, 490, 'Invalid verification');
        }

        // get registration entity
        $registration = $this->getRepo('UserRegistration')->findOneBy(array(
            'token' => $token,
            'code' => $code,
        ));

        if (is_null($registration)) {
            return $this->customErrorView(400, 490, 'Invalid verification');
        }

        // get user entity
        $user = $this->getRepo('JtUser')->find($registration->getUserid());
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        // check token validation time
        $currentTime = time().'000';
        if ($currentTime - $registration->getCreationdate() > self::ONE_DAY_IN_MILLIS) {
            return $this->customErrorView(400, 491, 'Expired verification');
        }

        // get response
        $response = $this->createXmppUser($registration, $user);

        // get username info from response
        $responseJSON = json_decode($response);
        $xmppUsername = $responseJSON->username;

        // set xmppUsername
        $user->setXmppUsername($xmppUsername);

        // activate user
        $user->setActivated(true);

        // create personal vcard
        $vcard = new JtVCard();
        $vcard->setUserid($xmppUsername);
        $vcard->setName($registration->getName());

        $email = $user->getEmail();
        $countryCode = $user->getCountrycode();
        $phone = $user->getPhone();

        if (!is_null($email)) {
            $vcard->setEmail($email);
        } elseif (!is_null($countryCode) && !is_null($phone)) {
            $vcardPhone = $this->constructVCardPhone($countryCode, $phone);
            $vcard->setPhone($vcardPhone);
        }

        // bind invitation
        $invitations = $this->getPendingInvitations($user);
        if (!is_null($invitations)) {
            foreach ($invitations as $invitation) {
                $invitation->setUserid($xmppUsername);
            }
        }

        // remove verification
        $em = $this->getDoctrine()->getManager();
        $em->persist($vcard);
        $em->remove($registration);
        $em->flush();

        // response
        $view = new View();
        $view->setData(array(
            'result' => true,
        ));

        return $view;
    }

    /**
     * @param $registration
     * @param $user
     *
     * @return mixed
     */
    private function createXmppUser(
        $registration,
        $user
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
        $username = strval(100000000 + $user->getId());

        // request json
        $jsonData = $this->createJsonData(
            $username,
            $user->getPassword(),
            $registration->getName(),
            $user->getEmail()
        );

        // set ezUser secret to basic auth
        $ezuserNameSecret = $globals['openfire_plugin_bstuser_property_name_ezuser'].':'.
            $globals['openfire_plugin_bstuser_property_secret_ezuser'];

        $basicAuth = 'Basic '.base64_encode($ezuserNameSecret);

        // init curl
        $ch = curl_init($apiUrl);

        // get then response when post OpenFire API
        $response = $this->callAPI($ch, $jsonData, $basicAuth, 'POST');

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $response;
    }

    /**
     * @param $name
     * @param $password
     * @param $email
     *
     * @return string
     */
    private function createJsonData(
        $username,
        $password,
        $name,
        $email
    ) {
        $dataArray = array();
        $dataArray['username'] = $username;
        $dataArray['password'] = $password;

        if (!is_null($name)) {
            $dataArray['name'] = $name;
        }

        if (!is_null($email)) {
            $dataArray['email'] = $email;
        }

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
