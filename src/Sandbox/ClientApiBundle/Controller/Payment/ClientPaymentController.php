<?php

namespace Sandbox\ClientApiBundle\Controller\Payment;

use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentServiceBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\MembershipCard\MembershipOrder;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Order\TopUpOrder;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Traits\FinanceTrait;
use Sandbox\ApiBundle\Traits\SendNotification;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Rest controller for Client Orders.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientPaymentController extends PaymentController
{
    const PINGPLUSPLUS_SIGNATURE_HEADER = 'x_pingplusplus_signature';
    const PINGPLUSPLUS_RSA_PATH = '/rsa_public_key.pem';

    use FinanceTrait;
    use SendNotification;

    /**
     * @Post("/payment/webhooks")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getWebhooksAction(
        Request $request
    ) {
        // verify webhooks signature
        $signatureHeaderKey = 'http_'.self::PINGPLUSPLUS_SIGNATURE_HEADER;

        $headers = array_change_key_case($_SERVER, CASE_LOWER);
        if (!array_key_exists($signatureHeaderKey, $headers)) {
            return new Response();
        }

        $signature = $headers[$signatureHeaderKey];
        $pub_key_path = __DIR__.self::PINGPLUSPLUS_RSA_PATH;
        $rawData = file_get_contents('php://input');

        $result = $this->verify_signature($rawData, $signature, $pub_key_path);

        if ($result !== 1) {
            return new Response();
        }

        // handle payment webhooks
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);
        $type = $data['type'];
        $object = $data['data']['object'];

        if ('refund.succeeded' == $type) {
            if ('succeeded' == $object['status'] && true == $object['succeed']) {
                // update order refund status
                $this->updateRefundStatus($object);
            }

            return new Response();
        }

        if ($type != 'charge.succeeded' || $object['paid'] != true) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $chargeId = $object['id'];
        $price = $object['amount'] / 100;
        $orderNumber = $object['order_no'];
        $channel = $object['channel'];
        $body = $object['body'];
        $orderType = $orderNumber[0];

        switch ($orderType) {
            case 'P':
                $bodyArray = json_decode($body, true);
                $userId = $bodyArray['user_id'];

                $order = $this->setProductOrder(
                    $orderNumber,
                    $channel,
                    $userId
                );

                $balance = $this->postBalanceChange(
                    $order->getUserId(),
                    0,
                    $orderNumber,
                    $channel,
                    $price
                );

                $orderMap = ProductOrder::PRODUCT_MAP;

                break;
//            case 'V':
//                $productId = $myCharge->getOrderId();
//                $order = $this->setMembershipOrder(
//                    $body,
//                    $productId,
//                    $price,
//                    $orderNumber
//                );
//                $this->postAccountUpgrade(
//                    $body,
//                    $productId,
//                    $orderNumber
//                );
//                $amount = $this->postConsumeBalance(
//                    $body,
//                    $price,
//                    $orderNumber
//                );
//                $balance = $this->postBalanceChange(
//                    $body,
//                    0,
//                    $orderNumber,
//                    $channel,
//                    $price
//                );

//                break;
            case 'T':
                $this->setTopUpOrder(
                    $body,
                    $price,
                    $orderNumber,
                    $channel
                );
                $balance = $this->postBalanceChange(
                    $body,
                    $price,
                    $orderNumber,
                    $channel,
                    $price
                );
                $amount = $this->postConsumeBalance(
                    $body,
                    $price,
                    $orderNumber
                );

                $orderMap = TopUpOrder::TOP_UP_MAP;

                break;
//            case 'F':
//                $data = $this->getJsonData(
//                    $orderNumber,
//                    $channel,
//                    $chargeId,
//                    true
//                );

//                $result = $this->foodPaymentCallback($data);

//                $amount = $this->postConsumeBalance(
//                    $body,
//                    $price,
//                    $orderNumber
//                );
//                $balance = $this->postBalanceChange(
//                    $body,
//                    0,
//                    $orderNumber,
//                    $channel,
//                    $price
//                );

//                break;
            case 'S':
                $order = $this->setShopOrderStatus(
                    $orderNumber,
                    $channel
                );

                $balance = $this->postBalanceChange(
                    $order->getUserId(),
                    0,
                    $orderNumber,
                    $channel,
                    $price
                );

                $orderMap = ShopOrder::SHOP_MAP;

                break;
            case 'E':
                $order = $this->setEventOrderStatus(
                    $orderNumber,
                    $channel
                );

                $this->postBalanceChange(
                    $order->getUserId(),
                    0,
                    $orderNumber,
                    $channel,
                    $price
                );

                $orderMap = EventOrder::EVENT_MAP;

                break;
            case 'B':
                $bodyArray = json_decode($body, true);
                $userId = $bodyArray['user_id'];

                $bill = $this->setLeaseBillStatus(
                    $orderNumber,
                    $channel,
                    $userId,
                    $price
                );

                $this->generateLongRentServiceFee(
                    $bill,
                    FinanceLongRentServiceBill::TYPE_BILL_SERVICE_FEE
                );

                $orderMap = LeaseBill::BILL_MAP;
                break;
            case 'M':
                $bodyArray = json_decode($body, true);
                $userId = $bodyArray['user_id'];
                $specificationId = $bodyArray['specification_id'];

                $membershipOrder = $this->setMembershipOrder(
                    $userId,
                    $specificationId,
                    $price,
                    $orderNumber,
                    $channel
                );

                // add invoice amount
                if (!$membershipOrder->isSalesInvoice()) {
                    $this->postConsumeBalance(
                        $membershipOrder->getUser(),
                        $price,
                        $orderNumber
                    );

                    $membershipOrder->setInvoiced(true);

                    $em = $this->getDoctrine()->getManager();
                    $em->flush();
                }

                $orderMap = MembershipOrder::TOP_UP_MAP;
                break;
            default:
                $orderMap = null;

                break;
        }

        if (!is_null($orderMap)) {
            $this->createOrderMap(
                $orderNumber,
                $chargeId,
                $orderMap
            );
        }

        return new Response();
    }

    /**
     * @Post("/payment/create")
     *
     * @param Request $request
     *
     * @return View
     */
    public function createPaymentAction(
        Request $request
    ) {
        $serverId = $this->getGlobal('server_order_id');
        $requestContent = json_decode($request->getContent(), true);
        $subject = $requestContent['subject'];
        $orderNo = $requestContent['order_no']."$serverId";
        $amount = $requestContent['amount'];
        $channel = $requestContent['channel'];
        $userId = $this->getUserId();
        $token = '';
        $smsId = '';
        $smsCode = '';
        $openId = null;

        if ($channel === self::PAYMENT_CHANNEL_ACCOUNT) {
            return $this->accountPayment(
                $userId,
                $orderNo,
                $amount
            );
        } elseif ($channel == ProductOrder::CHANNEL_WECHAT_PUB) {
            $wechat = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:ThirdParty\WeChat')
                ->findOneBy(
                    [
                        'userId' => $userId,
                        'loginFrom' => ThirdPartyOAuthWeChatData::DATA_FROM_WEBSITE,
                    ]
                );
            $this->throwNotFoundIfNull($wechat, self::NOT_FOUND_MESSAGE);

            $openId = $wechat->getOpenId();
        }

        $chargeJson = $this->payForOrder(
            $token,
            $smsId,
            $smsCode,
            $orderNo,
            $amount,
            $channel,
            $subject,
            "$userId",
            $openId
        );

        $charge = json_decode($chargeJson, true);

        return new View($charge);
    }

    /**
     * @Post("/payment/notification")
     *
     * @param Request $request
     *
     * @return View
     */
    public function createPaymentNotificationAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $content = json_decode($request->getContent(), true);
        if (!array_key_exists('target_user_id', $content)) {
            throw new NotFoundHttpException(self::NOT_FOUND_MESSAGE);
        }

        $targetUserId = $content['target_user_id'];
        $targetUser = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($targetUserId);
        $this->throwNotFoundIfNull($targetUser, self::NOT_FOUND_MESSAGE);

        $bodyZh = $this->get('translator')->trans(
            ProductOrderMessage::PAYMENT_NOTIFICATION_MESSAGE,
            [],
            null,
            'zh'
        );

        $bodyEn = $this->get('translator')->trans(
            ProductOrderMessage::PAYMENT_NOTIFICATION_MESSAGE,
            [],
            null,
            'en'
        );

        $apns = $this->setApnsJsonDataArray($bodyZh, $bodyEn);

        // get content array
        $contentArray = $this->getDefaultContentArray(
            self::PAYMENT_NOTIFICATION_TYPE,
            self::PAYMENT_NOTIFICATION_ACTION,
            $user,
            $apns
        );

        $zhData = $this->getJpushData(
            [$targetUserId],
            ['lang_zh'],
            $bodyZh,
            '创合秒租',
            $contentArray
        );

        $enData = $this->getJpushData(
            [$targetUserId],
            ['lang_en'],
            $bodyEn,
            'Sandbox3',
            $contentArray
        );

        $this->sendJpushNotification($zhData);
        $this->sendJpushNotification($enData);
    }

    /**
     * @param $object
     */
    private function updateRefundStatus(
        $object
    ) {
        $chargeId = $object['charge'];

        $myCharge = $this->getRepo('Order\OrderMap')->findOneBy(
            [
                'chargeId' => $chargeId,
            ]
        );
        $this->throwNotFoundIfNull($myCharge, self::NOT_FOUND_MESSAGE);

        $type = $myCharge->getType();
        $orderNumber = $myCharge->getOrderNumber();

        if ($type == ProductOrder::PRODUCT_MAP) {
            $path = ProductOrder::ENTITY_PATH;
        } elseif ($type == ShopOrder::SHOP_MAP) {
            $path = ShopOrder::ENTITY_PATH;
        } else {
            return;
        }

        $order = $this->getRepo($path)->findOneByOrderNumber($orderNumber);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $order->setRefunded(true);
        $order->setNeedToRefund(false);
        $order->setModificationDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * @param $raw_data
     * @param $signature
     * @param $pub_key_path
     *
     * @return int
     */
    private function verify_signature($raw_data, $signature, $pub_key_path)
    {
        $pub_key_contents = file_get_contents($pub_key_path);

        return openssl_verify(
            $raw_data,
            base64_decode($signature),
            $pub_key_contents,
            OPENSSL_ALGO_SHA256
        );
    }
}
