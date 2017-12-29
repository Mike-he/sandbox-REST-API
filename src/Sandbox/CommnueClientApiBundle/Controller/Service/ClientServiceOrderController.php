<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Service;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Error\Error;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Sandbox\ApiBundle\Entity\Service\ServiceOrder;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sandbox\ApiBundle\Entity\Service\Service;

class ClientServiceOrderController extends PaymentController
{
    const SERVICE_NOT_AVAILABLE_CODE = 400001;
    const SERVICE_NOT_AVAILABLE_MESSAGE = 'Service Is Not Available';
    const SERVICE_REGISTRATION_NOT_AVAILABLE_CODE = 400002;
    const SERVICE_REGISTRATION_NOT_AVAILABLE_MESSAGE = 'Event Registration Is Not Available';
    const WRONG_SERVICE_ORDER_STATUS_CODE = 400003;
    const WRONG_SERVICE_ORDER_STATUS_MESSAGE = 'Wrong Order Status';
    const SERVICE_ORDER_EXIST_CODE = 400004;
    const SERVICE_ORDER_EXIST_MESSAGE = 'Service Order Already Exists';

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/service/orders")
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

        $serviceOrder = new ServiceOrder();
        $customerId = null;

        if ($service->getSalesCompanyId()) {
            $customerId = $this->get('sandbox_api.sales_customer')->createCustomer(
                $service,
                $service->getSalesCompanyId()
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
        $serviceOrder->setCustomerId();

        if ($service->getSalesCompanyId()) {
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

        // get event order
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
    public function payEventOrderAction(
        Request $request,
        $id
    ) {
        // get event order
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