<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserEmailBindingController;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserEmailVerification;
use Sandbox\ApiBundle\Traits\StringUtil;
use Sandbox\ClientApiBundle\Data\User\EmailBindingSubmit;
use Sandbox\ClientApiBundle\Data\User\EmailBindingVerify;
use Sandbox\ClientApiBundle\Form\User\EmailBindingSubmitType;
use Sandbox\ClientApiBundle\Form\User\EmailBindingVerifyType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Email binding controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 *
 * @Route("/email")
 */
class ClientUserEmailBindingController extends UserEmailBindingController
{
    use StringUtil;

    const ERROR_INVALID_EMAIL_ADDRESS_CODE = 400001;
    const ERROR_INVALID_EMAIL_ADDRESS_MESSAGE = 'client.account.invalid_email_address';

    const ERROR_EMAIL_ALREADY_USED_CODE = 400002;
    const ERROR_EMAIL_ALREADY_USED_MESSAGE = 'client.account.email_already_used';

    const ERROR_INVALID_VERIFICATION_CODE = 400003;
    const ERROR_INVALID_VERIFICATION_MESSAGE = 'client.account.invalid_verification';

    const ERROR_EXPIRED_VERIFICATION_CODE = 400004;
    const ERROR_EXPIRED_VERIFICATION_MESSAGE = 'client.account.expired_verification';

    /**
     * Email bind submit email.
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
     * @Route("/bind/submit")
     * @Method({"POST"})
     *
     * @return string
     *
     * @throws \Exception
     */
    public function postEmailBindSubmitAction(
        Request $request
    ) {
        $userId = $this->getUserId();

        $submit = new EmailBindingSubmit();

        $form = $this->createForm(new EmailBindingSubmitType(), $submit);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handleEmailBindSubmit($userId, $submit);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Email bind verify code.
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
     * @Route("/bind/verify")
     * @Method({"POST"})
     *
     * @return string
     *
     * @throws \Exception
     */
    public function postEmailBindVerifyAction(
        Request $request
    ) {
        $userId = $this->getUserId();

        $verify = new EmailBindingVerify();

        $form = $this->createForm(new EmailBindingVerifyType(), $verify);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handleEmailBindVerify($userId, $verify);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * @param int                $userId
     * @param EmailBindingSubmit $submit
     *
     * @return View
     */
    private function handleEmailBindSubmit(
        $userId,
        $submit
    ) {
        $email = $submit->getEmail();

        // check email valid
        if (is_null($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->customErrorView(400, self::ERROR_INVALID_EMAIL_ADDRESS_CODE, self::ERROR_INVALID_EMAIL_ADDRESS_MESSAGE);
        }

        // check email already used
        $user = $this->getRepo('User\User')->findOneBy(array(
            'email' => $email,
            'banned' => false,
        ));
        if (!is_null($user)) {
            return $this->customErrorView(400, self::ERROR_EMAIL_ALREADY_USED_CODE, self::ERROR_EMAIL_ALREADY_USED_MESSAGE);
        }

        // create email verification entity
        $emailVerification = $this->generateEmailVerification($userId, $email);

        $em = $this->getDoctrine()->getManager();
        $em->persist($emailVerification);
        $em->flush();

        // send notification by email
        $this->sendEmailNotification($emailVerification);

        return new View();
    }

    /**
     * @param int                $userId
     * @param EmailBindingVerify $verify
     *
     * @return View
     */
    private function handleEmailBindVerify(
        $userId,
        $verify
    ) {
        $code = $verify->getCode();
        $email = $verify->getEmail();

        // get email verification entity
        $emailVerification = $this->getRepo('User\UserEmailVerification')->findOneBy(
            array(
                'userId' => $userId,
                'email' => $email,
                'code' => $code,
            )
        );
        $this->throwNotFoundIfNull($emailVerification, self::NOT_FOUND_MESSAGE);

        if ($code != $emailVerification->getCode()) {
            return $this->customErrorView(400, self::ERROR_INVALID_VERIFICATION_CODE, self::ERROR_INVALID_VERIFICATION_MESSAGE);
        }

        $globals = $this->container->get('twig')->getGlobals();
        if (new \DateTime('now') > $emailVerification->getCreationDate()->modify($globals['expired_verification_time'])) {
            return $this->customErrorView(400, self::ERROR_EXPIRED_VERIFICATION_CODE, self::ERROR_EXPIRED_VERIFICATION_MESSAGE);
        }

        // bind email
        $user = $this->getRepo('User\User')->find($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $user->setEmail($emailVerification->getEmail());

        // remove verification
        $em = $this->getDoctrine()->getManager();
        $em->remove($emailVerification);
        $em->flush();

        return new View();
    }

    /**
     * @param string $userId
     * @param string $email
     *
     * @return UserEmailVerification
     */
    private function generateEmailVerification(
        $userId,
        $email
    ) {
        // get email verification entity
        $emailVerification = $this->getRepo('User\UserEmailVerification')->findOneByUserId($userId);

        if (is_null($emailVerification)) {
            $emailVerification = new UserEmailVerification();
            $emailVerification->setUserId($userId);
        }

        $emailVerification->setEmail($email);
        $emailVerification->setCode($this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH));
        $emailVerification->setCreationDate(new \DateTime('now'));

        return $emailVerification;
    }

    /**
     * @param UserEmailVerification $emailVerification
     */
    private function sendEmailNotification(
        $emailVerification
    ) {
        $email = $emailVerification->getEmail();

        // send verification URL to email
        $subject = '【创合秒租】'.$this->before('@', $email).'，您正在申请绑定当前邮箱。';
        $this->sendEmail($subject, $email, $this->before('@', $email),
            'Emails/bind_email_verification.html.twig',
            array(
                'code' => $emailVerification->getCode(),
            )
        );
    }
}
