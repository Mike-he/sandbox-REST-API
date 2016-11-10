<?php

namespace Sandbox\ClientApiBundle\Controller\Order;

use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Order\TopUpOrder;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;

/**
 * Rest controller for Client TopUpOrders.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientTopUpOrderController extends PaymentController
{
    /**
     * Get all orders for current user.
     *
     * @Get("/topup/orders/my")
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Offset of page"
     * )
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getUserTopUpOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $orders = $this->getRepo('Order\TopUpOrder')->findBy(
            ['userId' => $userId],
            ['creationDate' => 'DESC'],
            $limit,
            $offset
        );

        $view = new View($orders);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_order']));

        return $view;
    }

    /**
     * @Post("/topup/orders")
     *
     * @Annotations\QueryParam(
     *    name="price",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="top up price"
     * )
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     */
    public function payTopUpAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $requestContent = json_decode($request->getContent(), true);
        $channel = $requestContent['channel'];

        if (!array_key_exists('price', $requestContent)) {
            $price = $paramFetcher->get('price');
        } else {
            $price = $requestContent['price'];
        }

        $token = '';
        $smsId = '';
        $smsCode = '';
        $openId = null;

        if (is_null($price) || empty($price)) {
            return $this->customErrorView(
                400,
                self::NO_PRICE_CODE,
                self::NO_PRICE_MESSAGE
            );
        }

        $orderNumber = $this->getOrderNumber(self::TOPUP_ORDER_LETTER_HEAD);

        if ($channel == ProductOrder::CHANNEL_WECHAT_PUB) {
            $wechat = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:ThirdParty\WeChat')
                ->findOneBy(
                    [
                        'userId' => $this->getUserId(),
                        'loginFrom' => ThirdPartyOAuthWeChatData::DATA_FROM_WEBSITE,
                    ]
                );
            $this->throwNotFoundIfNull($wechat, self::NOT_FOUND_MESSAGE);

            $openId = $wechat->getOpenId();
        }

        $charge = $this->payForOrder(
            $token,
            $smsId,
            $smsCode,
            $orderNumber,
            $price,
            $channel,
            TopUpOrder::PAYMENT_SUBJECT,
            $this->getUserId(),
            $openId
        );

        $charge = json_decode($charge, true);

        return new View($charge);
    }

    /**
     * @Get("/topup/orders/{orderNumber}")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getOneTopUpOrderByOrderNumberAction(
        Request $request,
        $orderNumber
    ) {
        $userId = $this->getUserId();

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\TopUpOrder')
            ->findOneBy(
                [
                    'orderNumber' => $orderNumber,
                    'userId' => $userId,
                ]
            );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $view = new View($order);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_order']));

        return $view;
    }

    /**
     * @Get("/topup/orders/{id}")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getOneTopUpOrderByIdAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\TopUpOrder')
            ->findOneBy(
                [
                    'id' => $id,
                    'userId' => $userId,
                ]
            );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $view = new View($order);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_order']));

        return $view;
    }
}
