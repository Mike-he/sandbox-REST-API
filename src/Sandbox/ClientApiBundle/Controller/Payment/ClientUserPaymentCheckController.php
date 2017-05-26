<?php

namespace Sandbox\ClientApiBundle\Controller\Payment;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserCheckCode;
use Sandbox\ApiBundle\Entity\User\UserPayment;
use Sandbox\ApiBundle\Entity\User\UserPhoneCode;
use Sandbox\ApiBundle\Traits\StringUtil;
use Sandbox\ApiBundle\Traits\YunPianSms;
use Sandbox\ClientApiBundle\Data\Payment\UserPaymentCheck;
use Sandbox\ClientApiBundle\Form\Payment\UserPaymentCheckType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClientUserPaymentCheckController extends SandboxRestController
{
    use YunPianSms;
    use StringUtil;

    const ERROR_INVALID_VERIFICATION_CODE = '400001';
    const ERROR_INVALID_VERIFICATION_MESSAGE = 'client.payment_check.invalid_verification';

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/payment/check_code/submit")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postUserPaymentCheckCodeAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $em = $this->getDoctrine()->getManager();

        // save or update user check code
        $userCheckCode = $this->saveUserCheckCode($userId, $em);

        // send verification code by sms
        $this->sendSMSNotification(
            $userId,
            $userCheckCode
        );

        return new View();
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/payment/check_code/verify")
     * @Method({"POST"})
     *
     * @return View
     */
    public function verifyUserPaymentCheckCodeAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $check = new UserPaymentCheck();

        $form = $this->createForm(new UserPaymentCheckType(), $check);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleUserPaymentCheck($check);
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/payment/check_my")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMyPaymentAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $userPayment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserPayment')
            ->findOneBy(array(
                'userId' => $userId,
            ));

        if (is_null($userPayment)) {
            return new View(array(
                'has_payment_password' => false,
                'has_touch_id' => false,
            ));
        }

        return new View(array(
            'has_payment_password' => true,
            'has_touch_id' => $userPayment->getTouchId(),
        ));
    }

    /**
     * @param UserPaymentCheck $check
     *
     * @return mixed
     */
    private function handleUserPaymentCheck(
        $check
    ) {
        $em = $this->getDoctrine()->getManager();

        $userId = $this->getUserId();
        $code = $check->getCode();
        $password = $check->getPassword();
        $touchID = is_null($check->isTouchID()) ? false : $check->isTouchID();

        $checkCode = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCheckCode')
            ->findOneBy(array(
                'userId' => $userId,
                'code' => $code,
                'type' => 1,
            ));
        if (is_null($checkCode)) {
            return $this->customErrorView(
                400,
                self::ERROR_INVALID_VERIFICATION_CODE,
                self::ERROR_INVALID_VERIFICATION_MESSAGE
            );
        }

        $maxTokenTime = $this->getParameter('expired_verification_time');
        $now = new \DateTime('now');
        if ($now > $checkCode->getCreationDate()->modify($maxTokenTime)) {
            return $this->customErrorView(
                400,
                self::ERROR_INVALID_VERIFICATION_CODE,
                self::ERROR_INVALID_VERIFICATION_MESSAGE
            );
        }

        $em->remove($checkCode);

        if (is_null($password)) {
            return new View();
        }

        // save user payment password
        $userPayment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserPayment')
            ->findOneBy(array(
                'userId' => $userId,
            ));

        if (is_null($userPayment)) {
            $userPayment = new UserPayment();
            $userPayment->setUserId($userId);
            $em->persist($userPayment);
        }

        $userPayment->setTouchId($touchID);
        $userPayment->setPassword($password);

        $em->flush();

        return new View(array(
            'id' => $userPayment->getId(),
        ));
    }

    /**
     * @param integer       $userId
     * @param EntityManager $em
     *
     * @return object|UserCheckCode
     */
    private function saveUserCheckCode(
        $userId,
        $em
    ) {
        $checkCode = $this->generateVerificationCode(self::VERIFICATION_CODE_LENGTH);

        $userCheckCode = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCheckCode')
            ->findOneBy(
                array(
                    'userId' => $userId,
                    'type' => 1,
                )
            );

        //if user check code is existed, check expire date time
        if (is_null($userCheckCode)) {
            $userCheckCode = new UserCheckCode();
            $userCheckCode->setUserId($userId);
            $userCheckCode->setType(1);
        }

        // if the date time is expired, update code and creation date
        $userCheckCode->setCode($checkCode);
        $userCheckCode->setCreationDate(new \DateTime('now'));

        $em->persist($userCheckCode);
        $em->flush();

        return $userCheckCode;
    }

    /**
     * @param integer       $userId
     * @param UserCheckCode $userCheckCode
     */
    private function sendSMSNotification(
        $userId,
        $userCheckCode
    ) {
        $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')->find($userId);
        $email = $user->getEmail();
        $phoneCode = $user->getPhoneCode();
        $phone = $user->getPhone();
        $code = $userCheckCode->getCode();

        if (!is_null($phone)) {
            if (UserPhoneCode::DEFAULT_PHONE_CODE == $phoneCode) {
                $smsText = self::ZH_SMS_BEFORE.$userCheckCode->getCode()
                    .self::ZH_SMS_AFTER;
            } else {
                $smsText = self::EN_SMS_BEFORE.$userCheckCode->getCode()
                    .self::EN_SMS_AFTER;
            }

            $this->send_sms(
                $phoneCode.$phone,
                $smsText
            );
        } else {
            // send verification URL to email
            $subject = '【创合秒租】'.$this->before('@', $email).'，用户支付密码设置';
            $this->sendEmail($subject, $email, $this->before('@', $email),
                'Emails/user_payment_check_email_verification.html.twig',
                array(
                    'code' => $code,
                ));
        }
    }
}