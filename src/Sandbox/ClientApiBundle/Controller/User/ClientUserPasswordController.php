<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserPasswordController;
use Sandbox\ApiBundle\Entity\User\UserForgetPassword;
use Sandbox\ApiBundle\Entity\User\UserPhoneCode;
use Sandbox\ApiBundle\Traits\YunPianSms;
use Sandbox\ApiBundle\Traits\StringUtil;
use Sandbox\ClientApiBundle\Data\User\PasswordForgetReset;
use Sandbox\ClientApiBundle\Data\User\PasswordForgetVerify;
use Sandbox\ClientApiBundle\Data\User\PasswordForgetSubmit;
use Sandbox\ClientApiBundle\Form\User\PasswordForgetResetType;
use Sandbox\ClientApiBundle\Form\User\PasswordForgetSubmitType;
use Sandbox\ClientApiBundle\Form\User\PasswordForgetVerifyType;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Password controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 *
 * @Route("/password")
 */
class ClientUserPasswordController extends UserPasswordController
{
    // Traits
    use StringUtil;
    use YunPianSms;

    // Constants
    const ERROR_MISSING_PHONE_OR_EMAIL_CODE = 400001;
    const ERROR_MISSING_PHONE_OR_EMAIL_MESSAGE = 'Missing phone number or email address.-手机号和邮箱不能同时为空';

    const ERROR_INVALID_EMAIL_ADDRESS_CODE = 400002;
    const ERROR_INVALID_EMAIL_ADDRESS_MESSAGE = 'Invalid email address.-邮箱地址无效';

    const ERROR_INVALID_PHONE_CODE = 400003;
    const ERROR_INVALID_PHONE_MESSAGE = 'Invalid phone number.-手机号无效';

    const ERROR_ACCOUNT_NOT_FOUND_CODE = 400004;
    const ERROR_ACCOUNT_NOT_FOUND_MESSAGE = 'Account not found.-账号不存在';

    const ERROR_ACCOUNT_NOT_ACTIVATED_CODE = 400005;
    const ERROR_ACCOUNT_NOT_ACTIVATED_MESSAGE = 'Account not activated.-账号未激活';

    const ERROR_INVALID_VERIFICATION_CODE = 400006;
    const ERROR_INVALID_VERIFICATION_MESSAGE = 'Invalid verification.-验证无效';

    const ERROR_EXPIRED_VERIFICATION_CODE = 400007;
    const ERROR_EXPIRED_VERIFICATION_MESSAGE = 'Expired verification.-验证过期';

    const ERROR_INVALID_TOKEN_CODE = 400008;
    const ERROR_INVALID_TOKEN_MESSAGE = 'Invalid token.-令牌无效';

    const ERROR_EXPIRED_TOKEN_CODE = 400009;
    const ERROR_EXPIRED_TOKEN_MESSAGE = 'Expired token.-令牌过期';

    const ERROR_INVALID_PASSWORD_CODE = 400010;
    const ERROR_INVALID_PASSWORD_MESSAGE = 'Invalid password.-密码无效';

    const ERROR_SAME_PASSWORD_CODE = 400011;
    const ERROR_SAME_PASSWORD_MESSAGE = 'Same password.-新密码与旧密码不能相同';

    const ZH_SMS_RESET_PASSWORD_BEFORE = '【展想创合】您正在重置账号密码，如确认是本人行为，请提交以下验证码完成操作：';
    const ZH_SMS_RESET_PASSWORD_AFTER = '。验证码在10分钟内有效。';

    const EN_SMS_RESET_PASSWORD_BEFORE = '【Sandbox3】Your verification code is ';
    const EN_SMS_RESET_PASSWORD_AFTER = '.';

    /**
     * Forget password submit email or phone.
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
     *
     * @throws BadRequestHttpException
     */
    public function postPasswordForgetSubmitAction(
        Request $request
    ) {
        $submit = new PasswordForgetSubmit();

        $form = $this->createForm(new PasswordForgetSubmitType(), $submit);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handlePasswordForgetSubmit($submit);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Forget password submit email or phone.
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
     *
     * @throws BadRequestHttpException
     */
    public function postPasswordForgetVerifyAction(
        Request $request
    ) {
        $verify = new PasswordForgetVerify();

        $form = $this->createForm(new PasswordForgetVerifyType(), $verify);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handlePasswordForgetVerify($verify);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Forget password submit email or phone.
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
     *
     * @throws BadRequestHttpException
     */
    public function postPasswordForgetResetAction(
        Request $request
    ) {
        $reset = new PasswordForgetReset();

        $form = $this->createForm(new PasswordForgetResetType(), $reset);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handlePasswordForgetReset($reset);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
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
        $phone = $submit->getPhone();
        $phoneCode = $submit->getPhoneCode();

        if (!is_null($email)) {
            // check email valid
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->customErrorView(400, self::ERROR_INVALID_EMAIL_ADDRESS_CODE, self::ERROR_INVALID_EMAIL_ADDRESS_MESSAGE);
            }

            // get user by email
            $user = $this->getRepo('User\User')->findOneByEmail($email);
        } else {
            if (is_null($phone)) {
                return $this->customErrorView(400, self::ERROR_MISSING_PHONE_OR_EMAIL_CODE, self::ERROR_MISSING_PHONE_OR_EMAIL_MESSAGE);
            }

            // check phone number valid
            if (!is_numeric($phone)) {
                return $this->customErrorView(400, self::ERROR_INVALID_PHONE_CODE, self::ERROR_INVALID_PHONE_MESSAGE);
            }

            if (is_null($phoneCode)) {
                $phoneCode = UserPhoneCode::DEFAULT_PHONE_CODE;
            }

            // get user by phone
            $user = $this->getRepo('User\User')->findOneBy(array(
                'phone' => $phone
            ));
        }

        if (is_null($user)) {
            return $this->customErrorView(400, self::ERROR_ACCOUNT_NOT_FOUND_CODE, self::ERROR_ACCOUNT_NOT_FOUND_MESSAGE);
        }

        if ($user->isBanned()) {
            return $this->customErrorView(
                401,
                ClientUserLoginController::ERROR_ACCOUNT_BANNED_CODE,
                ClientUserLoginController::ERROR_ACCOUNT_BANNED_MESSAGE
            );
        }

        // save or update forget password
        $forgetPassword = $this->saveOrUpdateForgetPassword(
            $user->getId(),
            'submit', $email,
            $phone,
            $phoneCode
        );

        $formalPhone = $phoneCode.$phone;

        // send verification
        $this->sendVerification($email, $formalPhone, $forgetPassword, $phoneCode);

        return new View();
    }

    /**
     * @param string $userId
     * @param string $status
     * @param string $email
     * @param string $phone
     * @param string $phoneCode
     *
     * @return UserForgetPassword
     */
    private function saveOrUpdateForgetPassword(
        $userId,
        $status,
        $email,
        $phone,
        $phoneCode
    ) {
        $type = 'email';
        if (is_null($email)) {
            $type = 'phone';
        }

        $forgetPassword = $this->getRepo('User\UserForgetPassword')->findOneBy(array(
            'userId' => $userId,
            'type' => $type,
        ));

        if (is_null($forgetPassword)) {
            $forgetPassword = $this->saveForgetPassword($userId, $status, $type, $email, $phone, $phoneCode);
        } else {
            $forgetPassword = $this->updateForgetPassword($forgetPassword, $status);
        }

        return $forgetPassword;
    }

    /**
     * @param string $userId
     * @param string $status
     * @param string $type
     * @param string $email
     * @param string $phone
     * @param string $phoneCode
     *
     * @return UserForgetPassword ForgetPassword
     */
    private function saveForgetPassword(
        $userId,
        $status,
        $type,
        $email,
        $phone,
        $phoneCode
    ) {
        $forgetPassword = new UserForgetPassword();

        $forgetPassword->setUserId($userId);
        $forgetPassword->setCode($this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH));
        $forgetPassword->setStatus($status);
        $forgetPassword->setType($type);
        $forgetPassword->setEmail($email);
        $forgetPassword->setPhoneCode($phoneCode);
        $forgetPassword->setPhone($phone);
        $forgetPassword->setCreationDate(new \DateTime('now'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($forgetPassword);
        $em->flush();

        return $forgetPassword;
    }

    /**
     * @param UserForgetPassword $forgetPassword
     * @param string             $status
     *
     * @return UserForgetPassword
     */
    private function updateForgetPassword(
        $forgetPassword,
        $status
    ) {
        $forgetPassword->setStatus($status);

        if ($status === 'submit') {
            $forgetPassword->setCode($this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH));
            $forgetPassword->setCreationDate(new \DateTime('now'));
        } else {
            $forgetPassword->setToken($this->generateRandomToken());
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $forgetPassword;
    }

    /**
     * @param string             $email
     * @param string             $phone
     * @param UserForgetPassword $forgetPassword
     * @param string             $phoneCode
     */
    private function sendVerification(
        $email,
        $phone,
        $forgetPassword,
        $phoneCode
    ) {
        if (!is_null($email)) {

            // send verification URL to email
            $subject = '【展想创合】'.$this->before('@', $email).'，您正在重置账号密码。';
            $this->sendEmail($subject, $email, $this->before('@', $email),
                'Emails/forget_password_email_verification.html.twig',
                array(
                    'code' => $forgetPassword->getCode(),
                )
            );
        } else {
            if (UserPhoneCode::DEFAULT_PHONE_CODE == $phoneCode) {
                // sms verification code to phone
                $smsText = self::ZH_SMS_RESET_PASSWORD_BEFORE
                    .$forgetPassword->getCode().self::ZH_SMS_RESET_PASSWORD_AFTER;
            } else {
                $smsText = self::EN_SMS_RESET_PASSWORD_BEFORE
                    .$forgetPassword->getCode().self::EN_SMS_RESET_PASSWORD_AFTER;
            }

            $this->send_sms($phone, $smsText);
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
        $email = $verify->getEmail();
        $phone = $verify->getPhone();
        $code = $verify->getCode();
        $phoneCode = $verify->getPhoneCode();

        if (is_null($code) ||
            (!is_null($email) && !is_null($phone))) {
            return $this->customErrorView(400, self::ERROR_INVALID_VERIFICATION_CODE, self::ERROR_INVALID_VERIFICATION_MESSAGE);
        }

        if (!is_null($phone) && is_null($phoneCode)) {
            $phoneCode = UserPhoneCode::DEFAULT_PHONE_CODE;
        }

        $forgetPassword = $this->getRepo('User\UserForgetPassword')->findOneBy(array(
            'email' => $email,
            'phoneCode' => $phoneCode,
            'phone' => $phone,
            'code' => $code,
            'status' => 'submit',
        ));

        if (is_null($forgetPassword)) {
            return $this->customErrorView(400, self::ERROR_INVALID_VERIFICATION_CODE, self::ERROR_INVALID_VERIFICATION_MESSAGE);
        }

        // filter by user is banned
        $user = $this->getRepo('User\User')->findOneById($forgetPassword->getUserId());
        if ($user->isBanned()) {
            return $this->customErrorView(
                401,
                ClientUserLoginController::ERROR_ACCOUNT_BANNED_CODE,
                ClientUserLoginController::ERROR_ACCOUNT_BANNED_MESSAGE
            );
        }

        $globals = $this->container->get('twig')->getGlobals();
        if (new \DateTime('now') > $forgetPassword->getCreationDate()->modify($globals['expired_verification_time'])) {
            return $this->customErrorView(400, self::ERROR_EXPIRED_VERIFICATION_CODE, self::ERROR_EXPIRED_VERIFICATION_MESSAGE);
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
        // get auth
        $auth = null;
        $headers = apache_request_headers();
        if (array_key_exists('Authorization', $headers)) {
            $auth = $headers['Authorization'];
        }

        // get payload fields
        $token = $reset->getToken();
        $password = $reset->getPassword();

        if (is_null($token)) {
            return $this->customErrorView(400, self::ERROR_INVALID_TOKEN_CODE, self::ERROR_INVALID_TOKEN_MESSAGE);
        }

        if (is_null($password)) {
            return $this->customErrorView(400, self::ERROR_INVALID_PASSWORD_CODE, self::ERROR_INVALID_PASSWORD_MESSAGE);
        }

        $forgetPassword = $this->getRepo('User\UserForgetPassword')->findOneBy(array(
            'token' => $token,
            'status' => 'verify',
        ));
        $this->throwNotFoundIfNull($forgetPassword, self::NOT_FOUND_MESSAGE);

        $globals = $this->container->get('twig')->getGlobals();
        if (new \DateTime('now') > $forgetPassword->getCreationDate()->modify($globals['expired_verification_time'])) {
            return $this->customErrorView(400, self::ERROR_EXPIRED_VERIFICATION_CODE, self::ERROR_EXPIRED_VERIFICATION_MESSAGE);
        }

        $user = $this->getRepo('User\User')->find($forgetPassword->getUserid());
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        if ($password === $user->getPassword()) {
            return $this->customErrorView(400, self::ERROR_SAME_PASSWORD_CODE, self::ERROR_SAME_PASSWORD_MESSAGE);
        }

        $this->resetPassword($user, $password, $forgetPassword, $auth);

        return new View();
    }

    /**
     * @param User               $user
     * @param string             $password
     * @param UserForgetPassword $forgetPassword
     * @param null               $auth
     */
    private function resetPassword(
        $user,
        $password,
        $forgetPassword,
        $auth = null
    ) {
        // update xmpp user password
        $result = $this->updateXmppUser($user->getXmppUsername(), $password);
        if (!$result) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();

        // set new password
        $user->setPassword($password);

        // delete all other tokens of this user
        $this->removeUserOtherAuth($user->getId(), $auth, $em);

        // remove forgetPassword
        $em->remove($forgetPassword);
        $em->flush();
    }

    /**
     * @param int  $userId
     * @param null $basicAuth
     * @param null $em
     */
    protected function removeUserOtherAuth(
        $userId,
        $basicAuth = null,
        $em = null
    ) {
        $isEmNull = false;
        if (is_null($em)) {
            $isEmNull = true;
            $em = $this->getDoctrine()->getManager();
        }

        $currentToken = null;
        if (!is_null($basicAuth)) {
            $currentToken = $this->getUsernameFromBasicAuth($basicAuth);
        }

        $tokens = $this->getRepo('User\UserToken')->findByUserId($userId);
        foreach ($tokens as $token) {
            if (!is_null($currentToken)
                && $token->getToken() === $currentToken) {
                continue;
            }

            $em->remove($token);
        }

        if ($isEmNull) {
            $em->flush();
        }
    }
}
