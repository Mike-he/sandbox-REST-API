<?php

namespace Sandbox\ClientApiBundle\Controller\Order;

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
    const TOPUP_ORDER_LETTER_HEAD = 'T';

    /**
     * Get all orders for current user.
     *
     * @Get("/topup/orders/my")
     *
     *@Annotations\QueryParam(
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
            null,
            $limit,
            $offset
        );

        return new View($orders);
    }

    /**
     * @Post("/topup/orders")
     *
     * @param Request $request
     */
    public function payTopUpAction(
        Request $request
    ) {
        $requestContent = json_decode($request->getContent(), true);
        $price = $requestContent['price'];
        $channel = $requestContent['channel'];
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
                        'from' => ThirdPartyOAuthWeChatData::DATA_FROM_WEBSITE,
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
    public function getOneTopUpOrderAction(
        Request $request,
        $orderNumber
    ) {
        $order = $order = $this->getRepo('Order\TopUpOrder')->findOneBy(
            ['orderNumber' => $orderNumber]
        );
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }

        return new View($order);
    }
}
