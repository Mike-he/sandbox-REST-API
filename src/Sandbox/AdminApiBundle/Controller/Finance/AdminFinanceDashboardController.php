<?php

namespace Sandbox\AdminApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;

/**
 * Class AdminFinanceDashboardController.
 */
class AdminFinanceDashboardController extends AdminRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="year",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="month",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description=""
     * )
     *
     * @Route("/finance/cash_flow/dashboard")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFinanceCashFlowAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $year = $paramFetcher->get('year');
        $month = $paramFetcher->get('month');

        $startString = $year.'-'.$month.'-01';
        $startDate = new \DateTime($startString);
        $startDate->setTime(0, 0, 0);

        $endString = $startDate->format('Y-m-t');
        $endDate = new \DateTime($endString);
        $endDate->setTime(23, 59, 59);

        $incomingTotalAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate
            );

        $wxIncomingAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxPubIncomingAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT_PUB
            );

        $alipayIncomingAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $upacpIncomingAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_UNIONPAY
            );

        $offlineIncomingAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_OFFLINE
            );

        $incomingTotalCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate
            );

        $wxIncomingCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxPubIncomingCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT_PUB
            );

        $alipayIncomingCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $upacpIncomingCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_UNIONPAY
            );

        $offlineIncomingCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_OFFLINE
            );

        // refund amount
        $totalRefundedAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate
            );

        $wxRefundedAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxPubRefundedAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT_PUB
            );

        $alipayRefundedAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $upacpRefundedAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_UNIONPAY
            );

        $offlineRefundedAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_OFFLINE
            );

        $totalRefundedCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate
            );

        $wxRefundedCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxPubRefundedCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT_PUB
            );

        $alipayRefundedCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $upacpRefundedCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_UNIONPAY
            );

        $offlineRefundedCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_OFFLINE
            );

        $response = array(
            'incoming_total_amount' => $incomingTotalAmount,
            'incoming_wx_amount' => $wxIncomingAmount,
            'incoming_wx_pub_amount' => $wxPubIncomingAmount,
            'incoming_alipay_amount' => $alipayIncomingAmount,
            'incoming_upacp_amount' => $upacpIncomingAmount,
            'incoming_offline_amount' => $offlineIncomingAmount,
            'incoming_total_count' => $incomingTotalCount,
            'incoming_wx_count' => $wxIncomingCount,
            'incoming_wx_pub_count' => $wxPubIncomingCount,
            'incoming_alipay_count' => $alipayIncomingCount,
            'incoming_upacp_count' => $upacpIncomingCount,
            'incoming_offline_count' => $offlineIncomingCount,
            'refunded_total_amount' => $totalRefundedAmount,
            'refunded_wx_amount' => $wxRefundedAmount,
            'refunded_wx_pub_amount' => $wxPubRefundedAmount,
            'refunded_alipay_amount' => $alipayRefundedAmount,
            'refunded_upacp_amount' => $upacpRefundedAmount,
            'refunded_offline_amount' => $offlineRefundedAmount,
            'refunded_total_count' => $totalRefundedCount,
            'refunded_wx_count' => $wxRefundedCount,
            'refunded_wx_pub_count' => $wxPubRefundedCount,
            'refunded_alipay_count' => $alipayRefundedCount,
            'refunded_upacp_count' => $upacpRefundedCount,
            'refunded_offline_count' => $offlineRefundedCount,
            'sum_amount' => $incomingTotalAmount - $totalRefundedAmount,
        );

        return new View($response);
    }
}
