<?php

namespace Sandbox\AdminApiBundle\Controller\Payment;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;

/**
 * Rest controller for Admin Payment.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminPaymentController extends PaymentController
{
    /**
     * @Post("/payment/refund")
     *
     * @param Request $request
     *
     * @return View
     */
    public function refundPaymentAction(
        Request $request
    ) {
        // get auth
        $headers = apache_request_headers();
        $auth = $headers['Sandbox-Auth'];

        // compare auth
        $this->encodedKeysComparison($auth);

        $serverId = $this->getGlobal('server_order_id');
        $data = json_decode($request->getContent(), true);
        $subject = $data['subject'];
        $orderNo = $data['order_no']."$serverId";
        $amount = $data['amount'];
        $userId = $data['user_id'];

        $balance = $this->postBalanceChange(
            $userId,
            $amount,
            $orderNo,
            self::PAYMENT_CHANNEL_ACCOUNT,
            0,
            self::ORDER_REFUND
        );

        if (is_null($balance)) {
            return $this->customErrorView(
                500,
                self::SYSTEM_ERROR_CODE,
                self::SYSTEM_ERROR_MESSAGE
            );
        }

        return new Response();
    }
}
