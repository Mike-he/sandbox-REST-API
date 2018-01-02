<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Service;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Error\Error;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Sandbox\ApiBundle\Entity\Service\ServiceForm;
use Sandbox\ApiBundle\Entity\Service\ServiceOrder;
use Sandbox\ApiBundle\Entity\Service\ServicePurchaseForm;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sandbox\ApiBundle\Entity\Service\Service;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClientServiceOrderController extends PaymentController
{
    const SERVICE_NOT_AVAILABLE_CODE = 400001;
    const SERVICE_NOT_AVAILABLE_MESSAGE = 'Service Is Not Available';
    const SERVICE_PURCHASE_NOT_AVAILABLE_CODE = 400002;
    const SERVICE_PURCHASE_NOT_AVAILABLE_MESSAGE = 'Service Purchase Is Not Available';
    const WRONG_SERVICE_ORDER_STATUS_CODE = 400003;
    const WRONG_SERVICE_ORDER_STATUS_MESSAGE = 'Wrong Order Status';
    const SERVICE_ORDER_EXIST_CODE = 400004;
    const SERVICE_ORDER_EXIST_MESSAGE = 'Service Order Already Exists';
    const ERROR_SERVICE_INVALID = 'Invalid Service Form';
    const ERROR_INVALID_PHONE_CODE = 400005;
    const ERROR_INVALID_PHONE_MESSAGE = 'Invalid phone';
    const ERROR_INVALID_EMAIL_CODE = 400006;
    const ERROR_INVALID_EMAIL_MESSAGE = 'Invalid email';
    const ERROR_INVALID_RADIO_CODE = 400007;
    const ERROR_INVALID_RADIO_MESSAGE = 'Invalid radio';
    const ERROR_INVALID_CHECKBOX_CODE = 400008;
    const ERROR_INVALID_CHECKBOX_MESSAGE = 'Invalid checkbox';
    const ERROR_MISSING_USER_INPUT_CODE = 400009;
    const ERROR_MISSING_USER_INPUT_MESSAGE = 'Missing user input';
    const ERROR_OVER_LIMIT_NUMBER_CODE = 400010;
    const ERROR_OVER_LIMIT_NUMBER_MESSAGE = 'Over purchaselimit number';

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/service/{id}/orders")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postServiceOrderController(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();
        $now = new \DateTime();

        $service = $this->getDoctrine()->getManager()
            ->getRepository('SandboxApiBundle:Service\Service')
            ->findOneBy(array(
                'id'=>$id,
                'visible' => true
            ));
        $this->throwNotFoundIfNull($service, self::NOT_FOUND_MESSAGE);
        $salesCompanyId = $service->getSalesCompanyId();

        $serviceOrder = new ServiceOrder();
        $customerId = null;

        if ($salesCompanyId) {
            $customerId = $this->get('sandbox_api.sales_customer')->createCustomer(
                $userId,
                $salesCompanyId
            );
        }

        $error = new Error();
        $this->checkIfAvailable(
            $userId,
            $service,
            $now,
            $error
        );

        if (!is_null($error->getCode())) {
            return $this->customErrorView(
                400,
                $error->getCode(),
                $error->getMessage()
            );
        }

        // generate order number
        $orderNumber = $this->getOrderNumber(ServiceOrder::LETTER_HEAD);

        $serviceOrder->setUserId($userId);
        $serviceOrder->setService($service);
        $serviceOrder->setOrderNumber($orderNumber);
        $serviceOrder->setPrice($service->getPrice());
        $serviceOrder->setCompanyId($salesCompanyId);
        $serviceOrder->setCustomerId($customerId);

        if ($salesCompanyId) {
            $serviceOrder->setCustomerId($customerId);
        }

        // set status
        if (0 == $serviceOrder->getPrice()) {
            $serviceOrder->setStatus(ServiceOrder::STATUS_PAID);
        } else {
            $serviceOrder->setStatus(ServiceOrder::STATUS_UNPAID);
        }

        $em = $this->getDoctrine()->getManager();

        $em->persist($serviceOrder);
        $em->flush();

        $view = new View();
        $view->setData(array(
            'order_id' => $serviceOrder->getId(),
        ));

        $this->handlePurchaseForm(
            $serviceOrder,
            $request,
            $em
        );

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/service/orders/{id}/remaining")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getServiceOrderRemainingTimeAction(
        Request $request,
        $id
    ) {
        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime();

        // get service order
        $order = $this->getRepo('Service\ServiceOrder')->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $status = $order->getStatus();
        $minutes = 0;
        $seconds = 0;

        if ('unpaid' == $status) {
            $creationDate = $order->getCreationDate();
            $remainingTime = $now->diff($creationDate);
            $minutes = $remainingTime->i;
            $seconds = $remainingTime->s;
            $minutes = 4 - $minutes;
            $seconds = 59 - $seconds;
            if ($minutes < 0) {
                $minutes = 0;
                $seconds = 0;
                $em->remove($order);

                $purchaseForms = $this->getDoctrine()->getRepository('SandboxApiBundle:Service\ServicePurchaseForm')
                    ->findByOrder($order);
                foreach ($purchaseForms as $purchaseForm){
                    $em->remove($purchaseForm);
                }
                $em->flush();
            }
        }

        $view = new View();
        $view->setData(
            [
                'remainingMinutes' => $minutes,
                'remainingSeconds' => $seconds,
            ]
        );

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/service/orders/{id}/pay")
     * @Method({"POST"})
     *
     * @return View
     */
    public function payServiceOrderAction(
        Request $request,
        $id
    ) {
        // get service order
        $order = $this->getRepo('Service\ServiceOrder')->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        // check if request user is the same as order user
        if ($this->getUserId() != $order->getUserId()) {
            return $this->customErrorView(
                400,
                self::WRONG_SERVICE_ORDER_STATUS_CODE,
                self::WRONG_SERVICE_ORDER_STATUS_MESSAGE
            );
        }

        $requestContent = json_decode($request->getContent(), true);
        $channel = $requestContent['channel'];
        $token = '';
        $smsId = '';
        $smsCode = '';
        $openId = null;

        if (self::PAYMENT_CHANNEL_ACCOUNT === $channel) {
            return $this->payByAccount(
                $order,
                $channel
            );
        } elseif (ProductOrder::CHANNEL_WECHAT_PUB == $channel) {
            $wechat = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:ThirdParty\WeChat')
                ->findOneBy(
                    [
                        'userId' => $order->getUserId(),
                        'loginFrom' => ThirdPartyOAuthWeChatData::DATA_FROM_WEBSITE,
                    ]
                );
            $this->throwNotFoundIfNull($wechat, self::NOT_FOUND_MESSAGE);

            $openId = $wechat->getOpenId();
        }

        $orderNumber = $order->getOrderNumber();
        $charge = $this->payForOrder(
            $token,
            $smsId,
            $smsCode,
            $orderNumber,
            $order->getPrice(),
            $channel,
            ServiceOrder::PAYMENT_SUBJECT,
            ServiceOrder::PAYMENT_BODY,
            $openId
        );
        $charge = json_decode($charge, true);

        return new View($charge);
    }

    /**
     * @param $id
     *
     * @Route("/service/{id}/form")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getServiceFormAction(
        $id
    ) {
        $service = $this->getDoctrine()->getRepository('SandboxApiBundle:Service\Service')->find($id);
        $this->throwNotFoundIfNull($service, self::NOT_FOUND_MESSAGE);

        $serviceForm = $this->getDoctrine()->getRepository('SandboxApiBundle:Service\ServiceForm')->findByService($service);

        return new View($serviceForm);
    }

    /**
     * @param $userId
     * @param Service $service
     * @param $now
     * @param $error
     */
    private function checkIfAvailable(
        $userId,
        $service,
        $now,
        $error
    ) {
        $serviceEnd = $service->getServiceEndDate();

        if(
            $serviceEnd < $now ||
            !$service->isCharge() ||
            is_null($service->getPrice()) ||
            false == $service->isVisible()
        ) {
            $error->setCode(self::SERVICE_NOT_AVAILABLE_CODE);
            $error->setMessage(self::SERVICE_NOT_AVAILABLE_MESSAGE);
        }

        // check service order exists
        $serviceId = $service->getId();
        $order = $this->getDoctrine()->getManager()
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->getUserLastOrder(
                $userId,
                $serviceId
            );
        if(!is_null($order)){
            $error->setCode(self::SERVICE_ORDER_EXIST_CODE);
            $error->setMessage(self::SERVICE_ORDER_EXIST_MESSAGE);
        }
    }

    /**
     * @param $serviceOrder
     * @param $request
     * @param $em
     * @return View
     */
    private function handlePurchaseForm(
        $serviceOrder,
        $request,
        $em
    ) {
        $requestContent = json_decode($request->getContent(), true);

        foreach ($requestContent as $form) {
            $userInput = $form['user_input'];
            if (is_null($userInput)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_MISSING_USER_INPUT_CODE,
                    self::ERROR_MISSING_USER_INPUT_MESSAGE
                );
            }

            $serviceForm = $this->getRepo('Service\ServiceForm')->find($form['id']);
            if (is_null($serviceForm)) {
                throw new BadRequestHttpException(self::ERROR_SERVICE_INVALID);
            }

            // check if user input is legal
            $formType = $serviceForm->getType();
            $formId = $serviceForm->getId();

            if (ServiceForm::TYPE_PHONE == $formType) {
                if (!is_numeric($userInput)) {
                    return $this->customErrorView(
                        400,
                        self::ERROR_INVALID_PHONE_CODE,
                        self::ERROR_INVALID_PHONE_MESSAGE
                    );
                }
            } elseif (ServiceForm::TYPE_EMAIL == $formType) {
                if (!filter_var($userInput, FILTER_VALIDATE_EMAIL)) {
                    return $this->customErrorView(
                        400,
                        self::ERROR_INVALID_EMAIL_CODE,
                        self::ERROR_INVALID_EMAIL_MESSAGE
                    );
                }
            } elseif (ServiceForm::TYPE_RADIO == $formType) {
                $formOption = $this->getRepo('Service\ServiceFormOption')->findOneBy(array(
                    'id' => (int) $userInput,
                    'formId' => $formId,
                ));
                if (is_null($formOption)) {
                    return $this->customErrorView(
                        400,
                        self::ERROR_INVALID_RADIO_CODE,
                        self::ERROR_INVALID_RADIO_MESSAGE
                    );
                }
            } elseif (ServiceForm::TYPE_CHECKBOX == $formType) {
                $delimiter = ',';
                $ids = explode($delimiter, $userInput);

                foreach ($ids as $id) {
                    $formOption = $this->getRepo('Service\ServiceFormOption')->findOneBy(array(
                        'id' => (int) $id,
                        'formId' => $formId,
                    ));
                    if (is_null($formOption)) {
                        return $this->customErrorView(
                            400,
                            self::ERROR_INVALID_CHECKBOX_CODE,
                            self::ERROR_INVALID_CHECKBOX_MESSAGE
                        );
                    }
                }
            }
            $purchaseForm = new ServicePurchaseForm();

            $purchaseForm->setOrder($serviceOrder);
            $purchaseForm->setForm($serviceForm);
            $purchaseForm->setUserInput($userInput);

            $em->persist($purchaseForm);
            $em->flush();
        }
    }

    /**
     * @param ServiceOrder $order
     * @param            $channel
     *
     * @return View
     */
    private function payByAccount(
        $order,
        $channel
    ) {
        $price = $order->getPrice();
        $orderNumber = $order->getOrderNumber();
        $balance = $this->postBalanceChange(
            $order->getUserId(),
            (-1) * $price,
            $orderNumber,
            self::PAYMENT_CHANNEL_ACCOUNT,
            $price
        );
        if (is_null($balance)) {
            return $this->customErrorView(
                400,
                self::INSUFFICIENT_FUNDS_CODE,
                self::INSUFFICIENT_FUNDS_MESSAGE
            );
        }

        $order->setStatus(self::STATUS_PAID);
        $order->setPaymentDate(new \DateTime());
        $order->setPayChannel($channel);
        $order->setModificationDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $view = new View();

        return $view->setData(
            array(
                'balance' => $balance,
                'channel' => self::PAYMENT_CHANNEL_ACCOUNT,
            )
        );
    }
}