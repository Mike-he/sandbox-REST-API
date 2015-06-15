<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserPasswordController;
use Sandbox\ClientApiBundle\Data\User\PasswordChange;
use Sandbox\ClientApiBundle\Data\User\PasswordForgetReset;
use Sandbox\ClientApiBundle\Data\User\PasswordForgetVerify;
use Sandbox\ClientApiBundle\Data\User\PasswordForgetSubmit;
use Sandbox\ClientApiBundle\Form\PasswordChangeType;
use Sandbox\ClientApiBundle\Form\PasswordForgetResetType;
use Sandbox\ClientApiBundle\Form\PasswordForgetSubmitType;
use Sandbox\ClientApiBundle\Form\PasswordForgetVerifyType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Password controller
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 *
 * @Route("/password")
 */
class ClientUserPasswordController extends UserPasswordController
{
    const HALF_HOUR_IN_MILLIS = 1800000;

    /**
     * Change password
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
     * @Route("/change")
     * @Method({"POST"})
     *
     * @return string
     * @throws \Exception
     */
    public function postPasswordChangeAction(
        Request $request
    ) {
        $userId = $this->getUsername();

        $change = new PasswordChange();

        $form = $this->createForm(new PasswordChangeType(), $change);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {
            return $this->handlePasswordChange(
                $request,
                $userId,
                $change
            );
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Forget password submit email or phone
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
     * @Route("/forget/submit")
     * @Method({"POST"})
     *
     * @return string
     * @throws BadRequestHttpException
     */
    public function postPasswordForgetSubmitAction(
        Request $request
    ) {
        $submit = new PasswordForgetSubmit();

        $form = $this->createForm(new PasswordForgetSubmitType(), $submit);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {
            return $this->handlePasswordForgetSubmit($submit);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Forget password submit email or phone
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
     * @Route("/forget/verify")
     * @Method({"POST"})
     *
     * @return string
     * @throws BadRequestHttpException
     */
    public function postPasswordForgetVerifyAction(
        Request $request
    ) {
        $verify = new PasswordForgetVerify();

        $form = $this->createForm(new PasswordForgetVerifyType(), $verify);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {
            return $this->handlePasswordForgetVerify($verify);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Forget password submit email or phone
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
     * @Route("/forget/reset")
     * @Method({"POST"})
     *
     * @return string
     * @throws BadRequestHttpException
     */
    public function postPasswordForgetResetAction(
        Request $request
    ) {
        $reset = new PasswordForgetReset();

        $form = $this->createForm(new PasswordForgetResetType(), $reset);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {
            return $this->handlePasswordForgetReset($reset);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * @param Request        $request
     * @param string         $userId
     * @param PasswordChange $change
     *
     * @return View
     */
    private function handlePasswordChange(
        $request,
        $userId,
        $change
    ) {
        $requestUserId = $change->getUserid();
        $requestCurrentPassword = $change->getCurrentpassword();
        $newPassword = $change->getNewpassword();
        $fullJID = $change->getFulljid();

        if ($userId != $requestUserId) {
            return $this->customErrorView(400, 490, 'Invalid user ID');
        }

        if ($requestCurrentPassword === $newPassword) {
            return $this->customErrorView(400, 491, 'Same password');
        }

        // TODO validate new password with password rule


        // get user
        $user = $this->getRepo('JtUser')->findOneByXmppUsername($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        if ($requestCurrentPassword != $user->getPassword()) {
            return $this->customErrorView(400, 493, 'Wrong current password');
        }

        if ($newPassword === $user->getPassword()) {
            return $this->customErrorView(400, 491, 'Same password');
        }

        // change password
        $this->changePassword($request, $user, $newPassword, $fullJID);

        // response
        $view = new View();
        $view->setData(array(
            'result' => true,
        ));

        return $view;
    }

    /**
     * @param Request $request
     * @param JtUser  $user
     * @param string  $newPassword
     * @param string  $fullJID
     */
    private function changePassword(
        $request,
        $user,
        $newPassword,
        $fullJID
    ) {
        $em = $this->getDoctrine()->getManager();

        // the request auth from header
        $auth = $request->headers->get(self::HTTP_HEADER_AUTH);

        // update xmpp user password
        $this->updateXmppUserPassword($auth, $user->getXmppUsername(), $newPassword, $fullJID);

        // set new password
        $user->setPassword($newPassword);

        // delete all other tokens of this user
        $this->removeUserOtherTokens($user->getId(), $this->getUser()->getSecretdigest(), $em);

        // save
        $em->flush();
    }

    /**
     * @param $auth
     * @param $username
     * @param $password
     * @param $fullJID
     */
    private function updateXmppUserPassword(
        $auth,
        $username,
        $password,
        $fullJID = null
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

        // request json
        $jsonData = $this->createJsonData($username, $password, $fullJID);

        // init curl
        $ch = curl_init($apiUrl);

        // get then response when post OpenFire API
        $response = $this->callAPI($ch, $jsonData, $auth, 'PUT');
        if (!$response) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
    }

    /**
     * @param $username
     * @param $password
     * @param $fullJID
     *
     * @return string
     */
    private function createJsonData(
        $username,
        $password,
        $fullJID
    ) {
        $dataArray = array();
        $dataArray['username'] = $username;
        $dataArray['password'] = $password;

        if (!is_null($fullJID)) {
            $dataArray['fulljid'] = $fullJID;
        }

        return json_encode($dataArray);
    }

    /**
     * @param PasswordForgetSubmit $submit
     *
     * @return View
     */
    private function handlePasswordForgetSubmit(
        $submit
    ) {
        $email = $submit->getEmail();
        $countryCode = $submit->getCountrycode();
        $phone = $submit->getPhone();

        if (!is_null($email)) {
            // check email valid
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->customErrorView(400, 491, 'Invalid email address');
            }

            // get user by email
            $user = $this->getRepo('JtUser')->findOneByEmail($email);
        } else {
            if (is_null($countryCode) || is_null($phone)) {
                return $this->customErrorView(400, 490, 'Missing phone number or email address');
            }

            // check country code and phone number valid
            if (!is_numeric($countryCode) || !is_numeric($phone)) {
                return $this->customErrorView(400, 492, 'Invalid phone number');
            }

            // get user by email
            $user = $this->getRepo('JtUser')->findOneBy(array(
                'countrycode' => $countryCode,
                'phone' => $phone,
            ));
        }

        if (is_null($user)) {
            return $this->customErrorView(400, 493, 'Account not found');
        }

        if (!$user->getActivated()) {
            return $this->customErrorView(400, 494, 'Account not activated');
        }

        // save or update forget password
        $forgetPassword = $this->saveOrUpdateForgetPassword($user->getId(), 'submit', $email);

        // send verification
        $this->sendVerification($email, $phone, $user->getXmppUsername(), $forgetPassword);

        // response
        $view = new View();
        $view->setData(array(
            'token' => $forgetPassword->getToken(),
        ));

        return $view;
    }

    /**
     * @param $userId
     * @param $status
     * @param $email
     *
     * @return ForgetPassword
     */
    private function saveOrUpdateForgetPassword(
        $userId,
        $status,
        $email
    ) {
        $type = 'email';
        if (is_null($email)) {
            $type = 'phone';
        }

        $forgetPassword = $this->getRepo('ForgetPassword')->findOneBy(array(
            'userid' => $userId,
            'type' => $type,
        ));

        if (is_null($forgetPassword)) {
            $forgetPassword = $this->saveForgetPassword($userId, $status, $type);
        } else {
            $forgetPassword = $this->updateForgetPassword($forgetPassword, $status);
        }

        return $forgetPassword;
    }

    /**
     * @param $userId
     * @param $status
     * @param $type
     *
     * @return ForgetPassword
     */
    private function saveForgetPassword(
        $userId,
        $status,
        $type
    ) {
        $forgetPassword = new ForgetPassword();

        $forgetPassword->setUserid($userId);
        $forgetPassword->setToken($this->generateRandomToken());
        $forgetPassword->setCode($this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH));
        $forgetPassword->setStatus($status);
        $forgetPassword->setType($type);
        $forgetPassword->setCreationdate(time());

        $em = $this->getDoctrine()->getManager();
        $em->persist($forgetPassword);
        $em->flush();

        return $forgetPassword;
    }

    /**
     * @param  ForgetPassword $forgetPassword
     * @param  string         $status
     * @return ForgetPassword
     */
    private function updateForgetPassword(
        $forgetPassword,
        $status
    ) {
        $forgetPassword->setToken($this->generateRandomToken());
        $forgetPassword->setStatus($status);

        if ($status === 'submit') {
            $forgetPassword->setCode($this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH));
            $forgetPassword->setCreationdate(time());
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $forgetPassword;
    }

    /**
     * @param string         $email
     * @param string         $phone
     * @param string         $username
     * @param ForgetPassword $forgetPassword
     */
    private function sendVerification(
        $email,
        $phone,
        $username,
        $forgetPassword
    ) {
        if (!is_null($email)) {
            // find user's own name
            $name = $this->getUserVCardName($username);

            // send verification URL to email
            $subject = '[EasyLinks Forget Password] '.$name.', please reset your password';
            $this->sendEmail($subject, $email, $name,
                'Emails/forget_password_email_verification.html.twig',
                array(
                    'token' => $forgetPassword->getToken(),
                    'code' => $forgetPassword->getCode(),
                )
            );
        } else {
            // sms verification code to phone
            $smsText = 'Verification code: '.$forgetPassword->getCode();
            $this->sendSms($phone, urlencode($smsText));
        }
    }

    /**
     * @param PasswordForgetVerify $verify
     *
     * @return View
     */
    private function handlePasswordForgetVerify(
        $verify
    ) {
        $token = $verify->getToken();
        $code = $verify->getCode();

        if (is_null($token) || is_null($code)) {
            return $this->customErrorView(400, 490, 'Invalid verification');
        }

        $forgetPassword = $this->getRepo('ForgetPassword')->findOneBy(array(
            'token' => $token,
            'code' => $code,
            'status' => 'submit',
        ));

        if (is_null($forgetPassword)) {
            return $this->customErrorView(400, 490, 'Invalid verification');
        }

        $currentTime = time().'000';
        if ($currentTime - $forgetPassword->getCreationdate() > self::HALF_HOUR_IN_MILLIS) {
            return $this->customErrorView(400, 491, 'Expired verification');
        }

        // update forget password
        $forgetPassword = $this->updateForgetPassword($forgetPassword, 'verify');

        // response
        $view = new View();
        $view->setData(array(
            'token' => $forgetPassword->getToken(),
        ));

        return $view;
    }

    /**
     * @param PasswordForgetReset $reset
     *
     * @return View
     */
    private function handlePasswordForgetReset(
        $reset
    ) {
        $token = $reset->getToken();
        $password = $reset->getPassword();

        if (is_null($token)) {
            return $this->customErrorView(400, 490, 'Invalid token');
        }

        if (is_null($password)) {
            return $this->customErrorView(400, 492, 'Invalid password');
        }

        // TODO validate password with password rule


        $forgetPassword = $this->getRepo('ForgetPassword')->findOneBy(array(
            'token' => $token,
            'status' => 'verify',
        ));
        $this->throwNotFoundIfNull($forgetPassword, self::NOT_FOUND_MESSAGE);

        $currentTime = time().'000';
        if ($currentTime - $forgetPassword->getCreationdate() > self::HALF_HOUR_IN_MILLIS) {
            return $this->customErrorView(400, 491, 'Expired token');
        }

        $user = $this->getRepo('JtUser')->find($forgetPassword->getUserid());
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        if ($password === $user->getPassword()) {
            return $this->customErrorView(400, 493, 'Same password');
        }

        $this->resetPassword($user, $password, $forgetPassword);

        // response
        $view = new View();
        $view->setData(array(
            'result' => true,
        ));

        return $view;
    }

    /**
     * @param JtUser         $user
     * @param string         $password
     * @param ForgetPassword $forgetPassword
     */
    private function resetPassword(
        $user,
        $password,
        $forgetPassword
    ) {
        $em = $this->getDoctrine()->getManager();

        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // set ezUser secret to basic auth
        $ezuserNameSecret = $globals['openfire_plugin_bstuser_property_name_ezuser'].':'.
            $globals['openfire_plugin_bstuser_property_secret_ezuser'];

        $basicAuth = 'Basic '.base64_encode($ezuserNameSecret);

        // update xmpp user password
        $this->updateXmppUserPassword($basicAuth, $user->getXmppUsername(), $password);

        // set new password
        $user->setPassword($password);

        // delete all other tokens of this user
        $this->removeUserOtherTokens($user->getId(), null, $em);

        // remove forgetPassword
        $em->remove($forgetPassword);
        $em->flush();
    }
}
