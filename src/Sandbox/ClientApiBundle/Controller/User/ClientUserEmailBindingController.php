<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserEmailBindingController;
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
        $username = $this->getUsername();

        $submit = new EmailBindingSubmit();

        $form = $this->createForm(new EmailBindingSubmitType(), $submit);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {
            return $this->handleEmailBindSubmit($userId, $username, $submit);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Email bind verify token
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
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {
            return $this->handleEmailBindVerify($userId, $verify);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Email unbind
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
     * @Route("/unbind")
     * @Method({"POST"})
     *
     * @return string
     * @throws \Exception
     */
    public function postEmailUnbindAction(
        Request $request
    ) {
        $userId = $this->getUserid();

        $submit = new EmailBindingSubmit();

        $form = $this->createForm(new EmailBindingSubmitType(), $submit);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {
            return $this->handleEmailUnbind($userId, $submit);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * @param integer            $userId
     * @param string             $username
     * @param EmailBindingSubmit $submit
     *
     * @return View
     */
    private function handleEmailBindSubmit(
        $userId,
        $username,
        $submit
    ) {
        $email = $submit->getEmail();

        // check email valid
        if (is_null($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->customErrorView(400, 490, 'Invalid email address');
        }

        // check email already used
        $user = $this->getRepo('JtUser')->findOneBy(array(
            'email' => $email,
            'activated' => true,
        ));
        if (!is_null($user)) {
            return $this->customErrorView(400, 491, 'Email address already used');
        }

        // get email verification entity
        $emailVerification = $this->getRepo('EmailVerification')->findOneByUserid($userId);

        $newEmailVerification = false;
        if (is_null($emailVerification)) {
            $newEmailVerification = true;
            $emailVerification = new EmailVerification();
        }

        $emailVerification->setUserid($userId);
        $emailVerification->setEmail($email);
        $emailVerification->setToken($this->generateRandomToken());
        $emailVerification->setCode($this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH));
        $emailVerification->setCreationdate(time());

        $em = $this->getDoctrine()->getManager();
        if ($newEmailVerification) {
            $em->persist($emailVerification);
        }
        $em->flush();

        // find user's own name
        $name = $this->getUserVCardName($username, null);

        // send verification URL to email
        $subject = '[EasyLinks Bind Email Verification] '.$name.', please confirm your email';
        $this->sendEmail($subject, $email, $name,
            'Emails/bind_email_verification.html.twig',
            array(
                'code' => $emailVerification->getCode(),
            )
        );

        // response
        $view = new View();
        $view->setData(array(
            'token' => $emailVerification->getToken(),
        ));

        return $view;
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
        $token = $verify->getToken();
        $code = $verify->getCode();

        // get email verification entity
        $emailVerification = $this->getRepo('EmailVerification')->findOneByUserid($userId);
        $this->throwNotFoundIfNull($emailVerification, self::NOT_FOUND_MESSAGE);

        if ($token != $emailVerification->getToken()
            || $code != $emailVerification->getCode()) {
            return $this->customErrorView(400, 490, 'Invalid verification');
        }

        $currentTime = time().'000';
        if ($currentTime - $emailVerification->getCreationdate() > self::HALF_HOUR_IN_MILLIS) {
            return $this->customErrorView(400, 491, 'Expired verification');
        }

        // bind email
        $user = $this->getRepo('JtUser')->find($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $user->setEmail($emailVerification->getEmail());

        // change personal email in vcard
        $vcard = $this->getRepo('JtVCard')->findOneBy(array(
            'userid' => $user->getXmppUsername(),
            'companyid' => null,
        ));
        $vcard->setEmail($emailVerification->getEmail());

        // remove verification
        $em = $this->getDoctrine()->getManager();
        $em->remove($emailVerification);
        $em->flush();

        // response
        $view = new View();
        $view->setData(array(
            'result' => true,
        ));

        return $view;
    }

    /**
     * @param integer            $userId
     * @param EmailBindingSubmit $submit
     *
     * @return View
     */
    private function handleEmailUnbind(
        $userId,
        $submit
    ) {
        $email = $submit->getEmail();

        // check email valid
        if (is_null($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->customErrorView(400, 490, 'Invalid email address');
        }

        // check email not found
        $user = $this->getRepo('JtUser')->findOneBy(array(
            'id' => $userId,
            'email' => $email,
            'activated' => true,
        ));
        if (is_null($user)) {
            return $this->customErrorView(400, 491, 'Wrong email address');
        }

        if (is_null($user->getCountrycode()) || is_null($user->getPhone())) {
            return $this->customErrorView(400, 492, 'Only email address is bound');
        }

        // unbind email
        $user->setEmail(null);

        // claer personal email in vcard
        $vcard = $this->getRepo('JtVCard')->findOneBy(array(
            'userid' => $user->getXmppUsername(),
            'companyid' => null,
        ));
        $vcard->setEmail(null);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // response
        $view = new View();
        $view->setData(array(
            'result' => true,
        ));

        return $view;
    }
}
