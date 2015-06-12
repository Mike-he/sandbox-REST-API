<?php

namespace Sandbox\ClientApiBundle\Controller;

use Sandbox\ApiBundle\Controller\PhoneBindingController;
use Sandbox\ClientApiBundle\Data\PhoneBindingSubmit;
use Sandbox\ClientApiBundle\Data\PhoneBindingVerify;
use Sandbox\ApiBundle\Entity\PhoneVerification;
use Sandbox\ClientApiBundle\Form\PhoneBindingSubmitType;
use Sandbox\ClientApiBundle\Form\PhoneBindingVerifyType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Phone binding controller
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 *
 * @Route("/phone")
 */
class ClientPhoneBindingController extends PhoneBindingController
{
    const BAD_PARAM_MESSAGE = "Bad parameters";

    const NOT_FOUND_MESSAGE = "This resource does not exist";

    const NOT_ALLOWED_MESSAGE = "You are not allowed to perform this action";

    const HALF_HOUR_IN_MILLIS = 1800000;

    /**
     * Phone binding submit country code and phone
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
    public function postPhoneBindSubmitAction(
        Request $request
    ) {
        $userId = $this->getUserid();

        $submit = new PhoneBindingSubmit();

        $form = $this->createForm(new PhoneBindingSubmitType(), $submit);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {
            return $this->handlePhoneBindSubmit($userId, $submit);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Phone binding verify code
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
    public function postPhoneBindVerifyAction(
        Request $request
    ) {
        $userId = $this->getUserid();

        $verify = new PhoneBindingVerify();

        $form = $this->createForm(new PhoneBindingVerifyType(), $verify);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {
            return $this->handlePhoneBindVerify($userId, $verify);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Phone unbind
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
    public function postPhoneUnbindAction(
        Request $request
    ) {
        $userId = $this->getUserid();

        $submit = new PhoneBindingSubmit();

        $form = $this->createForm(new PhoneBindingSubmitType(), $submit);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {
            return $this->handlePhoneUnbind($userId, $submit);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * @param integer            $userId
     * @param PhoneBindingSubmit $submit
     *
     * @return View
     */
    private function handlePhoneBindSubmit(
        $userId,
        $submit
    ) {
        $countryCode = $submit->getCountrycode();
        $phone = $submit->getPhone();

        // check country code and phone number valid
        if (!$this->isPhoneNumberValid($countryCode, $phone, true)) {
            return $this->customErrorView(400, 490, 'Invalid phone number');
        }

        // check phone number already used
        $user = $this->getRepo('JtUser')->findOneBy(array(
            'countrycode' => $countryCode,
            'phone' => $phone,
            'activated' => true,
        ));
        if (!is_null($user)) {
            return $this->customErrorView(400, 491, 'Phone number already used');
        }

        // get phone verification entity
        $phoneVerification = $this->getRepo('PhoneVerification')->findOneByUserid($userId);

        $newPhoneVerification = false;
        if (is_null($phoneVerification)) {
            $newPhoneVerification = true;
            $phoneVerification = new PhoneVerification();
        }

        $phoneVerification->setUserid($userId);
        $phoneVerification->setCountrycode($countryCode);
        $phoneVerification->setPhone($phone);
        $phoneVerification->setToken($this->generateRandomToken());
        $phoneVerification->setCode($this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH));
        $phoneVerification->setCreationdate(time());

        $em = $this->getDoctrine()->getManager();
        if ($newPhoneVerification) {
            $em->persist($phoneVerification);
        }
        $em->flush();

        // sms verification code to phone
        $smsText = 'Verification code: '.$phoneVerification->getCode();
        $this->sendSms($phone, urlencode($smsText));

        // response
        $view = new View();
        $view->setData(array(
            'token' => $phoneVerification->getToken(),
        ));

        return $view;
    }

    /**
     * @param integer            $userId
     * @param PhoneBindingVerify $verify
     *
     * @return View
     */
    private function handlePhoneBindVerify(
        $userId,
        $verify
    ) {
        $token = $verify->getToken();
        $code = $verify->getCode();

        // get phone verification entity
        $phoneVerification = $this->getRepo('PhoneVerification')->findOneByUserid($userId);
        $this->throwNotFoundIfNull($phoneVerification, self::NOT_FOUND_MESSAGE);

        if ($token != $phoneVerification->getToken()
            || $code != $phoneVerification->getCode()) {
            return $this->customErrorView(400, 490, 'Invalid verification');
        }

        $currentTime = time().'000';
        if ($currentTime - $phoneVerification->getCreationdate() > self::HALF_HOUR_IN_MILLIS) {
            return $this->customErrorView(400, 491, 'Expired verification code');
        }

        // bind phone
        $user = $this->getRepo('JtUser')->find($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $user->setCountrycode($phoneVerification->getCountrycode());
        $user->setPhone($phoneVerification->getPhone());

        // change personal phone in vcard
        $vcard = $this->getRepo('JtVCard')->findOneBy(array(
            'userid' => $user->getXmppUsername(),
            'companyid' => null,
        ));
        $vcardPhone = $this->constructVCardPhone($phoneVerification->getCountrycode(), $phoneVerification->getPhone());
        $vcard->setPhone($vcardPhone);

        // remove verification
        $em = $this->getDoctrine()->getManager();
        $em->remove($phoneVerification);
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
     * @param PhoneBindingSubmit $submit
     *
     * @return View
     */
    private function handlePhoneUnbind(
        $userId,
        $submit
    ) {
        $countryCode = $submit->getCountrycode();
        $phone = $submit->getPhone();

        // check country code and phone number valid
        if (!$this->isPhoneNumberValid($countryCode, $phone, true)) {
            return $this->customErrorView(400, 490, 'Invalid phone number');
        }

        // check phone not found
        $user = $this->getRepo('JtUser')->findOneBy(array(
            'id' => $userId,
            'countrycode' => $countryCode,
            'phone' => $phone,
            'activated' => true,
        ));
        if (is_null($user)) {
            return $this->customErrorView(400, 491, 'Wrong phone number');
        }

        if (is_null($user->getEmail())) {
            return $this->customErrorView(400, 492, 'Only phone number is bound');
        }

        // unbind phone
        $user->setCountrycode(null);
        $user->setPhone(null);

        // claer personal phone in vcard
        $vcard = $this->getRepo('JtVCard')->findOneBy(array(
            'userid' => $user->getXmppUsername(),
            'companyid' => null,
        ));
        $vcard->setPhone(null);

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
