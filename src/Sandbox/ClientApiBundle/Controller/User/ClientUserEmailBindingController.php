<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserEmailBindingController;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserEmailVerification;
use Sandbox\ApiBundle\Traits\StringUtil;
use Sandbox\ClientApiBundle\Data\User\EmailBindingSubmit;
use Sandbox\ClientApiBundle\Data\User\EmailBindingVerify;
use Sandbox\ClientApiBundle\Form\EmailBindingSubmitType;
use Sandbox\ClientApiBundle\Form\EmailBindingVerifyType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Email binding controller
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 *
 * @Route("/email")
 */
class ClientUserEmailBindingController extends UserEmailBindingController
{
    use StringUtil;

    const BAD_PARAM_MESSAGE = "Bad parameters";

    const NOT_FOUND_MESSAGE = "This resource does not exist";

    const NOT_ALLOWED_MESSAGE = "You are not allowed to perform this action";

    const HALF_HOUR_IN_MILLIS = 1800000;

    /**
     * Email bind submit email
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
     * @throws \Exception
     */
    public function postEmailBindSubmitAction(
        Request $request
    ) {
        $userId = $this->getUserid();

        $submit = new EmailBindingSubmit();

        $form = $this->createForm(new EmailBindingSubmitType(), $submit);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handleEmailBindSubmit($userId, $submit);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Email bind verify code
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
     * @throws \Exception
     */
    public function postEmailBindVerifyAction(
        Request $request
    ) {
        $userId = $this->getUserid();

        $verify = new EmailBindingVerify();

        $form = $this->createForm(new EmailBindingVerifyType(), $verify);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handleEmailBindVerify($userId, $verify);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * @param integer            $userId
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
            return $this->customErrorView(400, 490, 'Invalid email address');
        }

        // check email already used
        $user = $this->getRepo('User\User')->findOneBy(array(
            'email' => $email,
            'banned' => false,
        ));
        if (!is_null($user)) {
            return $this->customErrorView(400, 491, 'Email address already used');
        }

        // get email verification entity
        $emailVerification = $this->getRepo('User\UserEmailVerification')->findOneByUserId($userId);

        $newEmailVerification = false;
        if (is_null($emailVerification)) {
            $newEmailVerification = true;
            $emailVerification = new UserEmailVerification();
        }

        $emailVerification->setUserId($userId);
        $emailVerification->setEmail($email);
        $emailVerification->setCode($this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH));

        $em = $this->getDoctrine()->getManager();
        if ($newEmailVerification) {
            $em->persist($emailVerification);
        }
        $em->flush();

        // send verification URL to email
        $subject = '[Sandbox Bind Email Verification] '.$this->before('@', $email).', please confirm your email';
        $this->sendEmail($subject, $email, $this->before('@', $email),
            'Emails/bind_email_verification.html.twig',
            array(
                'code' => $emailVerification->getCode(),
            )
        );

        return new View();
    }

    /**
     * @param integer            $userId
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
            return $this->customErrorView(400, 490, 'Invalid verification');
        }

        if (new \DateTime("now") >  $emailVerification->getCreationDate()->modify('+0.5 hour')) {
            return $this->customErrorView(400, 491, 'Expired verification');
        }

        // bind email
        $user = $this->getRepo('User\User')->find($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $user->setEmail($emailVerification->getEmail());

        //TODO no vcard
//        // change personal email in vcard
//        $vcard = $this->getRepo('JtVCard')->findOneBy(array(
//            'userid' => $user->getXmppUsername(),
//            'companyid' => null,
//        ));
//        $vcard->setEmail($emailVerification->getEmail());

        // remove verification
        $em = $this->getDoctrine()->getManager();
        $em->remove($emailVerification);
        $em->flush();

        return new View();
    }
}
