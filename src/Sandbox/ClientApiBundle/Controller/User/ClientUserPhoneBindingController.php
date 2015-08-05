<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserPhoneBindingController;
use Sandbox\ApiBundle\Entity\User\UserPhoneVerification;
use Sandbox\ClientApiBundle\Data\User\PhoneBindingSubmit;
use Sandbox\ClientApiBundle\Data\User\PhoneBindingVerify;
use Sandbox\ClientApiBundle\Form\User\PhoneBindingSubmitType;
use Sandbox\ClientApiBundle\Form\User\PhoneBindingVerifyType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Phone binding controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 *
 * @Route("/phone")
 */
class ClientUserPhoneBindingController extends UserPhoneBindingController
{
    const ERROR_INVALID_PHONE_NUMBER_CODE = 400001;
    const ERROR_INVALID_PHONE_NUMBER_MESSAGE = 'Invalid phone number.-该手机号无效';

    const ERROR_PHONE_NUMBER_USED_CODE = 400002;
    const ERROR_PHONE_NUMBER_USED_MESSAGE = 'Phone number already used.-该手机号已被使用';

    const ERROR_INVALID_VERIFICATION_CODE = 400003;
    const ERROR_INVALID_VERIFICATION_MESSAGE = 'Invalid verification.-该验证无效';

    const ERROR_EXPIRED_VERIFICATION_CODE = 400004;
    const ERROR_EXPIRED_VERIFICATION_MESSAGE = 'Expired verification.-该验证已过期';

    /**
     * Phone binding submit country code and phone.
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
    public function postPhoneBindSubmitAction(
        Request $request
    ) {
        $userId = $this->getUserId();

        $submit = new PhoneBindingSubmit();

        $form = $this->createForm(new PhoneBindingSubmitType(), $submit);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handlePhoneBindSubmit($userId, $submit);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Phone binding verify code.
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
    public function postPhoneBindVerifyAction(
        Request $request
    ) {
        $userId = $this->getUserId();

        $verify = new PhoneBindingVerify();

        $form = $this->createForm(new PhoneBindingVerifyType(), $verify);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handlePhoneBindVerify($userId, $verify);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * @param int                $userId
     * @param PhoneBindingSubmit $submit
     *
     * @return View
     */
    private function handlePhoneBindSubmit(
        $userId,
        $submit
    ) {
        $phone = $submit->getPhone();

        // check country code and phone number valid
        if (!$this->isPhoneNumberValid($phone)) {
            return $this->customErrorView(400, self::ERROR_INVALID_PHONE_NUMBER_CODE, self::ERROR_INVALID_PHONE_NUMBER_MESSAGE);
        }

        // check phone number already used
        $user = $this->getRepo('User\User')->findOneBy(array(
            'phone' => $phone,
            'banned' => false,
        ));
        if (!is_null($user)) {
            return $this->customErrorView(400, self::ERROR_PHONE_NUMBER_USED_CODE, self::ERROR_PHONE_NUMBER_USED_MESSAGE);
        }

        // get phone verification entity
        $phoneVerification = $this->generatePhoneVerification($userId, $phone);

        $em = $this->getDoctrine()->getManager();
        $em->persist($phoneVerification);
        $em->flush();

        $this->sendSMSNotification($phoneVerification);

        return new View();
    }

    /**
     * @param int                $userId
     * @param PhoneBindingVerify $verify
     *
     * @return View
     */
    private function handlePhoneBindVerify(
        $userId,
        $verify
    ) {
        $phone = $verify->getPhone();
        $code = $verify->getCode();

        // get phone verification entity
        $phoneVerification = $this->getRepo('User\UserPhoneVerification')->findOneBy(
            array(
                'userId' => $userId,
                'phone' => $phone,
                'code' => $code,
            )
        );
        $this->throwNotFoundIfNull($phoneVerification, self::NOT_FOUND_MESSAGE);

        if ($code != $phoneVerification->getCode()) {
            return $this->customErrorView(400, self::ERROR_INVALID_VERIFICATION_CODE, self::ERROR_INVALID_VERIFICATION_MESSAGE);
        }

        if (new \DateTime('now') >  $phoneVerification->getCreationDate()->modify('+0.5 hour')) {
            return $this->customErrorView(400, self::ERROR_EXPIRED_VERIFICATION_CODE, self::ERROR_EXPIRED_VERIFICATION_MESSAGE);
        }

        // bind phone
        $user = $this->getRepo('User\User')->find($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $user->setPhone($phoneVerification->getPhone());

        // remove verification
        $em = $this->getDoctrine()->getManager();
        $em->remove($phoneVerification);
        $em->flush();

        return new View();
    }

    /**
     * @param string $userId
     * @param string $phone
     *
     * @return UserPhoneVerification
     */
    private function generatePhoneVerification(
        $userId,
        $phone
    ) {
        $phoneVerification = $this->getRepo('User\UserPhoneVerification')->findOneByUserId($userId);
        if (is_null($phoneVerification)) {
            $phoneVerification = new UserPhoneVerification();
            $phoneVerification->setUserid($userId);
            $phoneVerification->setPhone($phone);
        }

        $phoneVerification->setCode($this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH));

        return $phoneVerification;
    }

    /**
     * @param UserPhoneVerification $phoneVerification
     */
    private function sendSMSNotification(
        $phoneVerification
    ) {
        $smsText = '您正在申请绑定当前手机，如确认是本人行为，请提交以下验证码完成操作：'
            .$phoneVerification->getCode();
        $this->sendSms($phoneVerification->getPhone(), urlencode($smsText));
    }
}
