<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\Finance\FinanceDashboard;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentServiceBill;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Room\Room;

/**
 * Finance Trait.
 *
 * @category Sandbox
 *
 * @author   Feng Li <feng.li@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait FinanceTrait
{
    /**
     * @param $bill
     * @param $type
     */
    private function generateLongRentServiceFee(
        $bill,
        $type
    ) {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $date = round(microtime(true) * 1000);

        $serialNumber = FinanceLongRentServiceBill::SERVICE_FEE_LETTER_HEAD.$date;
        $companyId = $bill->getLease()->getProduct()->getRoom()->getBuilding()->getCompany()->getId();

        $fee = $this->getCompanyServiceFee($companyId);

        $serviceBill = $em->getRepository('SandboxApiBundle:Finance\FinanceLongRentServiceBill')
            ->findOneBy(
                array(
                    'bill' => $bill,
                    'type' => $type,
                )
            );

        $amount = ($bill->getRevisedAmount() * $fee) / 100;
        if (!$serviceBill) {
            $serviceBill = new FinanceLongRentServiceBill();
            $serviceBill->setSerialNumber($serialNumber);
            $serviceBill->setServiceFee($fee);
            $serviceBill->setAmount($amount);
            $serviceBill->setType($type);
            $serviceBill->setCompanyId($companyId);
            $serviceBill->setBill($bill);

            $em->persist($serviceBill);
            $em->flush();
        }

        $wallet = $em->getRepository('SandboxApiBundle:Finance\FinanceSalesWallet')
            ->findOneBy(['companyId' => $companyId]);
        if (!is_null($wallet)) {
            $totalAmount = $wallet->getTotalAmount();
            $billAmount = $wallet->getBillAmount();
            $withdrawAmount = $wallet->getWithdrawableAmount();

            $wallet->setTotalAmount($totalAmount + $bill->getRevisedAmount());
            $wallet->setBillAmount($billAmount + $amount);
            $wallet->setWithdrawableAmount($withdrawAmount + $bill->getRevisedAmount() - $amount);

            $em->flush();
        }
    }

    /**
     * @param $companyId
     *
     * @return mixed
     */
    private function getCompanyServiceFee(
        $companyId
    ) {
        $serviceInfo = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
            ->getCompanyServiceByType(
                $companyId,
                Room::TYPE_LONG_TERM
            );

        $serviceFee = $serviceInfo ? $serviceInfo->getServiceFee() : 0;

        return $serviceFee;
    }

    /**
     * @param $year
     * @param $month
     * @param $startDate
     * @param $endDate
     */
    private function generateFinanceDashboardSummary(
        $year,
        $month,
        $startDate,
        $endDate
    ) {
        // cash flow part
        $em = $this->getContainer()->get('doctrine')->getManager();

        $incomingTotalAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate
            );

        $incomingTotalAmountDashboard = new FinanceDashboard();
        $incomingTotalAmountDashboard->setTimePeriod($year.'-'.$month);
        $incomingTotalAmountDashboard->setParameterKey('incoming_total_amount');
        $incomingTotalAmountDashboard->setParameterValue((string) $incomingTotalAmount);
        $incomingTotalAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($incomingTotalAmountDashboard);

        $wxIncomingAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxIncomingAmountDashboard = new FinanceDashboard();
        $wxIncomingAmountDashboard->setTimePeriod($year.'-'.$month);
        $wxIncomingAmountDashboard->setParameterKey('incoming_wx_amount');
        $wxIncomingAmountDashboard->setParameterValue((string) $wxIncomingAmount);
        $wxIncomingAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxIncomingAmountDashboard);

        $wxPubIncomingAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT_PUB
            );

        $wxPubIncomingAmountDashboard = new FinanceDashboard();
        $wxPubIncomingAmountDashboard->setTimePeriod($year.'-'.$month);
        $wxPubIncomingAmountDashboard->setParameterKey('incoming_wx_pub_amount');
        $wxPubIncomingAmountDashboard->setParameterValue((string) $wxPubIncomingAmount);
        $wxPubIncomingAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxPubIncomingAmountDashboard);

        $alipayIncomingAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $alipayIncomingAmountDashboard = new FinanceDashboard();
        $alipayIncomingAmountDashboard->setTimePeriod($year.'-'.$month);
        $alipayIncomingAmountDashboard->setParameterKey('incoming_alipay_amount');
        $alipayIncomingAmountDashboard->setParameterValue((string) $alipayIncomingAmount);
        $alipayIncomingAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($alipayIncomingAmountDashboard);

        $upacpIncomingAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_UNIONPAY
            );

        $upacpIncomingAmountDashboard = new FinanceDashboard();
        $upacpIncomingAmountDashboard->setTimePeriod($year.'-'.$month);
        $upacpIncomingAmountDashboard->setParameterKey('incoming_upacp_amount');
        $upacpIncomingAmountDashboard->setParameterValue((string) $upacpIncomingAmount);
        $upacpIncomingAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($upacpIncomingAmountDashboard);

        $offlineIncomingAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_OFFLINE
            );

        $offlineIncomingAmountDashboard = new FinanceDashboard();
        $offlineIncomingAmountDashboard->setTimePeriod($year.'-'.$month);
        $offlineIncomingAmountDashboard->setParameterKey('incoming_offline_amount');
        $offlineIncomingAmountDashboard->setParameterValue((string) $offlineIncomingAmount);
        $offlineIncomingAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($offlineIncomingAmountDashboard);

        $incomingTotalCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate
            );

        $incomingTotalCountDashboard = new FinanceDashboard();
        $incomingTotalCountDashboard->setTimePeriod($year.'-'.$month);
        $incomingTotalCountDashboard->setParameterKey('incoming_total_count');
        $incomingTotalCountDashboard->setParameterValue((string) $incomingTotalCount);
        $incomingTotalCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($incomingTotalCountDashboard);

        $wxIncomingCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxIncomingCountDashboard = new FinanceDashboard();
        $wxIncomingCountDashboard->setTimePeriod($year.'-'.$month);
        $wxIncomingCountDashboard->setParameterKey('incoming_wx_count');
        $wxIncomingCountDashboard->setParameterValue((string) $wxIncomingCount);
        $wxIncomingCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxIncomingCountDashboard);

        $wxPubIncomingCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT_PUB
            );

        $wxPubIncomingCountDashboard = new FinanceDashboard();
        $wxPubIncomingCountDashboard->setTimePeriod($year.'-'.$month);
        $wxPubIncomingCountDashboard->setParameterKey('incoming_wx_pub_count');
        $wxPubIncomingCountDashboard->setParameterValue((string) $wxPubIncomingCount);
        $wxPubIncomingCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxPubIncomingCountDashboard);

        $alipayIncomingCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $alipayIncomingCountDashboard = new FinanceDashboard();
        $alipayIncomingCountDashboard->setTimePeriod($year.'-'.$month);
        $alipayIncomingCountDashboard->setParameterKey('incoming_alipay_count');
        $alipayIncomingCountDashboard->setParameterValue((string) $alipayIncomingCount);
        $alipayIncomingCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($alipayIncomingCountDashboard);

        $upacpIncomingCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_UNIONPAY
            );

        $upacpIncomingCountDashboard = new FinanceDashboard();
        $upacpIncomingCountDashboard->setTimePeriod($year.'-'.$month);
        $upacpIncomingCountDashboard->setParameterKey('incoming_upacp_count');
        $upacpIncomingCountDashboard->setParameterValue((string) $upacpIncomingCount);
        $upacpIncomingCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($upacpIncomingCountDashboard);

        $offlineIncomingCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_OFFLINE
            );

        $offlineIncomingCountDashboard = new FinanceDashboard();
        $offlineIncomingCountDashboard->setTimePeriod($year.'-'.$month);
        $offlineIncomingCountDashboard->setParameterKey('incoming_offline_count');
        $offlineIncomingCountDashboard->setParameterValue((string) $offlineIncomingCount);
        $offlineIncomingCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($offlineIncomingCountDashboard);

        // refund amount
        $totalRefundedAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate
            );

        $totalRefundedAmountDashboard = new FinanceDashboard();
        $totalRefundedAmountDashboard->setTimePeriod($year.'-'.$month);
        $totalRefundedAmountDashboard->setParameterKey('refunded_total_amount');
        $totalRefundedAmountDashboard->setParameterValue((string) $totalRefundedAmount);
        $totalRefundedAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($totalRefundedAmountDashboard);

        $wxRefundedAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxRefundedAmountDashboard = new FinanceDashboard();
        $wxRefundedAmountDashboard->setTimePeriod($year.'-'.$month);
        $wxRefundedAmountDashboard->setParameterKey('refunded_wx_amount');
        $wxRefundedAmountDashboard->setParameterValue((string) $wxRefundedAmount);
        $wxRefundedAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxRefundedAmountDashboard);

        $wxPubRefundedAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT_PUB
            );

        $wxPubRefundedAmountDashboard = new FinanceDashboard();
        $wxPubRefundedAmountDashboard->setTimePeriod($year.'-'.$month);
        $wxPubRefundedAmountDashboard->setParameterKey('refunded_wx_pub_amount');
        $wxPubRefundedAmountDashboard->setParameterValue((string) $wxPubRefundedAmount);
        $wxPubRefundedAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxRefundedAmountDashboard);

        $alipayRefundedAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $alipayRefundedAmountDashboard = new FinanceDashboard();
        $alipayRefundedAmountDashboard->setTimePeriod($year.'-'.$month);
        $alipayRefundedAmountDashboard->setParameterKey('refunded_alipay_amount');
        $alipayRefundedAmountDashboard->setParameterValue((string) $alipayRefundedAmount);
        $alipayRefundedAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($alipayRefundedAmountDashboard);

        $upacpRefundedAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_UNIONPAY
            );

        $upacpRefundedAmountDashboard = new FinanceDashboard();
        $upacpRefundedAmountDashboard->setTimePeriod($year.'-'.$month);
        $upacpRefundedAmountDashboard->setParameterKey('refunded_upacp_amount');
        $upacpRefundedAmountDashboard->setParameterValue((string) $upacpRefundedAmount);
        $upacpRefundedAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($upacpRefundedAmountDashboard);

        $offlineRefundedAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_OFFLINE
            );

        $offlineRefundedAmountDashboard = new FinanceDashboard();
        $offlineRefundedAmountDashboard->setTimePeriod($year.'-'.$month);
        $offlineRefundedAmountDashboard->setParameterKey('refunded_offline_amount');
        $offlineRefundedAmountDashboard->setParameterValue((string) $offlineRefundedAmount);
        $offlineRefundedAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($offlineRefundedAmountDashboard);

        $totalRefundedCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate
            );

        $totalRefundedCountDashboard = new FinanceDashboard();
        $totalRefundedCountDashboard->setTimePeriod($year.'-'.$month);
        $totalRefundedCountDashboard->setParameterKey('refunded_total_count');
        $totalRefundedCountDashboard->setParameterValue((string) $totalRefundedCount);
        $totalRefundedCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($totalRefundedCountDashboard);

        $wxRefundedCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxRefundedCountDashboard = new FinanceDashboard();
        $wxRefundedCountDashboard->setTimePeriod($year.'-'.$month);
        $wxRefundedCountDashboard->setParameterKey('refunded_wx_count');
        $wxRefundedCountDashboard->setParameterValue((string) $wxRefundedCount);
        $wxRefundedCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxRefundedCountDashboard);

        $wxPubRefundedCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT_PUB
            );

        $wxPubRefundedCountDashboard = new FinanceDashboard();
        $wxPubRefundedCountDashboard->setTimePeriod($year.'-'.$month);
        $wxPubRefundedCountDashboard->setParameterKey('refunded_wx_pub_count');
        $wxPubRefundedCountDashboard->setParameterValue((string) $wxPubRefundedCount);
        $wxPubRefundedCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxPubRefundedCountDashboard);

        $alipayRefundedCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $alipayRefundedCountDashboard = new FinanceDashboard();
        $alipayRefundedCountDashboard->setTimePeriod($year.'-'.$month);
        $alipayRefundedCountDashboard->setParameterKey('refunded_alipay_count');
        $alipayRefundedCountDashboard->setParameterValue((string) $alipayRefundedCount);
        $alipayRefundedCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($alipayRefundedCountDashboard);

        $upacpRefundedCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_UNIONPAY
            );

        $upacpRefundedCountDashboard = new FinanceDashboard();
        $upacpRefundedCountDashboard->setTimePeriod($year.'-'.$month);
        $upacpRefundedCountDashboard->setParameterKey('refunded_upacp_count');
        $upacpRefundedCountDashboard->setParameterValue((string) $upacpRefundedCount);
        $upacpRefundedCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($upacpRefundedCountDashboard);

        $offlineRefundedCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_OFFLINE
            );

        $offlineRefundedCountDashboard = new FinanceDashboard();
        $offlineRefundedCountDashboard->setTimePeriod($year.'-'.$month);
        $offlineRefundedCountDashboard->setParameterKey('refunded_offline_count');
        $offlineRefundedCountDashboard->setParameterValue((string) $offlineRefundedCount);
        $offlineRefundedCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($offlineRefundedCountDashboard);

        $sumAmountDashboard = new FinanceDashboard();
        $sumAmountDashboard->setTimePeriod($year.'-'.$month);
        $sumAmountDashboard->setParameterKey('sum_amount');
        $sumAmountDashboard->setParameterValue((string) $incomingTotalAmount - $totalRefundedAmount);
        $sumAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($sumAmountDashboard);

        // balance flow part
        $topUpTotalAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getTopUpAmount(
                $startDate,
                $endDate
            );

        $topUpTotalAmountDashboard = new FinanceDashboard();
        $topUpTotalAmountDashboard->setTimePeriod($year.'-'.$month);
        $topUpTotalAmountDashboard->setParameterKey('total_top_up_amount');
        $topUpTotalAmountDashboard->setParameterValue((string) $topUpTotalAmount);
        $topUpTotalAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($topUpTotalAmountDashboard);

        $wxTopUpAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getTopUpAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxTopUpAmountDashboard = new FinanceDashboard();
        $wxTopUpAmountDashboard->setTimePeriod($year.'-'.$month);
        $wxTopUpAmountDashboard->setParameterKey('wx_top_up_amount');
        $wxTopUpAmountDashboard->setParameterValue((string) $wxTopUpAmount);
        $wxTopUpAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($wxTopUpAmountDashboard);

        $alipayTopUpAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getTopUpAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $alipayTopUpAmountDashboard = new FinanceDashboard();
        $alipayTopUpAmountDashboard->setTimePeriod($year.'-'.$month);
        $alipayTopUpAmountDashboard->setParameterKey('alipay_top_up_amount');
        $alipayTopUpAmountDashboard->setParameterValue((string) $alipayTopUpAmount);
        $alipayTopUpAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($alipayTopUpAmountDashboard);

        $upacpTopUpAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getTopUpAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );
        $upacpTopUpAmountDashboard = new FinanceDashboard();
        $upacpTopUpAmountDashboard->setTimePeriod($year.'-'.$month);
        $upacpTopUpAmountDashboard->setParameterKey('upacp_top_up_amount');
        $upacpTopUpAmountDashboard->setParameterValue((string) $upacpTopUpAmount);
        $upacpTopUpAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($upacpTopUpAmountDashboard);

        $refundToAccountAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedToBalanceAmount(
                $startDate,
                $endDate
            );
        $refundToAccountAmountDashboard = new FinanceDashboard();
        $refundToAccountAmountDashboard->setTimePeriod($year.'-'.$month);
        $refundToAccountAmountDashboard->setParameterKey('refund_to_account_amount');
        $refundToAccountAmountDashboard->setParameterValue((string) $refundToAccountAmount);
        $refundToAccountAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($refundToAccountAmountDashboard);

        $topUpTotalCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countTopUpOrder(
                $startDate,
                $endDate
            );
        $topUpTotalCountDashboard = new FinanceDashboard();
        $topUpTotalCountDashboard->setTimePeriod($year.'-'.$month);
        $topUpTotalCountDashboard->setParameterKey('total_top_up_count');
        $topUpTotalCountDashboard->setParameterValue((string) $topUpTotalCount);
        $topUpTotalCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($topUpTotalCountDashboard);

        $wxTopUpCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countTopUpOrder(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );
        $wxTopUpCountDashboard = new FinanceDashboard();
        $wxTopUpCountDashboard->setTimePeriod($year.'-'.$month);
        $wxTopUpCountDashboard->setParameterKey('wx_top_up_count');
        $wxTopUpCountDashboard->setParameterValue((string) $wxTopUpCount);
        $wxTopUpCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($wxTopUpCountDashboard);

        $alipayTopUpCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countTopUpOrder(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );
        $alipayTopUpCountDashboard = new FinanceDashboard();
        $alipayTopUpCountDashboard->setTimePeriod($year.'-'.$month);
        $alipayTopUpCountDashboard->setParameterKey('alipay_top_up_count');
        $alipayTopUpCountDashboard->setParameterValue((string) $alipayTopUpCount);
        $alipayTopUpCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($alipayTopUpCountDashboard);

        $upacpTopUpCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countTopUpOrder(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );
        $upacpTopUpCountDashboard = new FinanceDashboard();
        $upacpTopUpCountDashboard->setTimePeriod($year.'-'.$month);
        $upacpTopUpCountDashboard->setParameterKey('upacp_top_up_count');
        $upacpTopUpCountDashboard->setParameterValue((string) $upacpTopUpCount);
        $upacpTopUpCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($upacpTopUpCountDashboard);

        $refundToAccountCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedToBalance(
                $startDate,
                $endDate
            );
        $refundToAccountCountDashboard = new FinanceDashboard();
        $refundToAccountCountDashboard->setTimePeriod($year.'-'.$month);
        $refundToAccountCountDashboard->setParameterKey('refund_to_account_count');
        $refundToAccountCountDashboard->setParameterValue((string) $refundToAccountCount);
        $refundToAccountCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($refundToAccountCountDashboard);

        $spaceOrderExpendAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->spaceOrderByAccountAmount(
                $startDate,
                $endDate
            );
        $spaceOrderExpendAmountDashboard = new FinanceDashboard();
        $spaceOrderExpendAmountDashboard->setTimePeriod($year.'-'.$month);
        $spaceOrderExpendAmountDashboard->setParameterKey('space_expend_amount');
        $spaceOrderExpendAmountDashboard->setParameterValue((string) $spaceOrderExpendAmount);
        $spaceOrderExpendAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($spaceOrderExpendAmountDashboard);

        $shopOrderExpendAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->shopOrderByAccountAmount(
                $startDate,
                $endDate
            );
        $shopOrderExpendAmountDashboard = new FinanceDashboard();
        $shopOrderExpendAmountDashboard->setTimePeriod($year.'-'.$month);
        $shopOrderExpendAmountDashboard->setParameterKey('shop_expend_amount');
        $shopOrderExpendAmountDashboard->setParameterValue((string) $shopOrderExpendAmount);
        $shopOrderExpendAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($shopOrderExpendAmountDashboard);

        $activityOrderExpendAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->activityOrderByAccountAmount(
                $startDate,
                $endDate
            );
        $activityOrderExpendAmountDashboard = new FinanceDashboard();
        $activityOrderExpendAmountDashboard->setTimePeriod($year.'-'.$month);
        $activityOrderExpendAmountDashboard->setParameterKey('activity_expend_amount');
        $activityOrderExpendAmountDashboard->setParameterValue((string) $activityOrderExpendAmount);
        $activityOrderExpendAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($activityOrderExpendAmountDashboard);

        $totalExpendAmount = $spaceOrderExpendAmount + $shopOrderExpendAmount + $activityOrderExpendAmount;

        $totalExpendAmountDashboard = new FinanceDashboard();
        $totalExpendAmountDashboard->setTimePeriod($year.'-'.$month);
        $totalExpendAmountDashboard->setParameterKey('total_expend_amount');
        $totalExpendAmountDashboard->setParameterValue((string) $totalExpendAmount);
        $totalExpendAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($totalExpendAmountDashboard);

        $spaceOrderExpendCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countSpaceOrderByAccount(
                $startDate,
                $endDate
            );
        $spaceOrderExpendCountDashboard = new FinanceDashboard();
        $spaceOrderExpendCountDashboard->setTimePeriod($year.'-'.$month);
        $spaceOrderExpendCountDashboard->setParameterKey('space_expend_count');
        $spaceOrderExpendCountDashboard->setParameterValue((string) $spaceOrderExpendCount);
        $spaceOrderExpendCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($spaceOrderExpendCountDashboard);

        $shopOrderExpendCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countShopOrderByAccount(
                $startDate,
                $endDate
            );
        $shopOrderExpendCountDashboard = new FinanceDashboard();
        $shopOrderExpendCountDashboard->setTimePeriod($year.'-'.$month);
        $shopOrderExpendCountDashboard->setParameterKey('shop_expend_count');
        $shopOrderExpendCountDashboard->setParameterValue((string) $shopOrderExpendCount);
        $shopOrderExpendCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($shopOrderExpendCountDashboard);

        $activityOrderExpendCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countActivityOrderByAccount(
                $startDate,
                $endDate
            );
        $activityOrderExpendCountDashboard = new FinanceDashboard();
        $activityOrderExpendCountDashboard->setTimePeriod($year.'-'.$month);
        $activityOrderExpendCountDashboard->setParameterKey('activity_expend_count');
        $activityOrderExpendCountDashboard->setParameterValue((string) $activityOrderExpendCount);
        $activityOrderExpendCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($activityOrderExpendCountDashboard);

        $totalExpendCount = $spaceOrderExpendCount + $shopOrderExpendCount + $activityOrderExpendCount;

        $totalExpendCountDashboard = new FinanceDashboard();
        $totalExpendCountDashboard->setTimePeriod($year.'-'.$month);
        $totalExpendCountDashboard->setParameterKey('total_expend_count');
        $totalExpendCountDashboard->setParameterValue((string) $totalExpendCount);
        $totalExpendCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($totalExpendCountDashboard);

        // add last total balance
        $lastTotalBalance = $this->getLastTotalBalance(
            $startDate,
            $endDate
        );
        $lastTotalBalanceDashboard = new FinanceDashboard();
        $lastTotalBalanceDashboard->setTimePeriod($year.'-'.$month);
        $lastTotalBalanceDashboard->setParameterKey('total_balance');
        $lastTotalBalanceDashboard->setParameterValue((string) $lastTotalBalance);
        $lastTotalBalanceDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($lastTotalBalanceDashboard);

        $em->flush();
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return mixed
     */
    private function getLastTotalBalance(
        $startDate,
        $endDate
    ) {
        $globals = $this->getContainer()->get('twig')->getGlobals();
        $crmURL = $globals['crm_api_url'];
        $adminBalanceURL = $globals['crm_api_admin_dashboard_balance'];
        $url = $crmURL.$adminBalanceURL.'?startDate='.$startDate->format('Y-m-d').'&endDate='.$endDate->format('Y-m-d');
        $ch = curl_init($url);

        $response = $this->callBalanceAPI($ch, 'GET');
        $balance = json_decode($response, true);

        return $balance['last_total_balance'];
    }

    /**
     * @param $ch
     * @param $method
     * @param $headers
     * @param $data
     *
     * @return mixed
     */
    private function callBalanceAPI(
        $ch,
        $method,
        $headers = null,
        $data = null
    ) {
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        } elseif ($method === 'PUT' || $method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        if (is_null($headers)) {
            $headers = array();
        }
        $headers[] = 'Accept: application/json';

        if (!is_null($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $headers[] = 'Content-Type: application/json';
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        return curl_exec($ch);
    }
}
