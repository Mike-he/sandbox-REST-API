<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\Finance\FinanceDashboard;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentServiceBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Constants\FinanceDashboardConstants;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;

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

        $date = round(microtime(true) * 1000).rand(1000, 9999);

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
        }

        $channel = $bill->getPayChannel();
        $wallet = $em->getRepository('SandboxApiBundle:Finance\FinanceSalesWallet')
            ->findOneBy(['companyId' => $companyId]);

        if (!is_null($wallet)) {
            $totalAmount = $wallet->getTotalAmount();
            $billAmount = $wallet->getBillAmount();
            $withdrawAmount = $wallet->getWithdrawableAmount();

            $wallet->setBillAmount($billAmount + $amount);

            if ($channel == LeaseBill::CHANNEL_SALES_OFFLINE) {
                $wallet->setWithdrawableAmount($withdrawAmount - $amount);
            } else {
                $wallet->setTotalAmount($totalAmount + $bill->getRevisedAmount());
                $wallet->setWithdrawableAmount($withdrawAmount + $bill->getRevisedAmount() - $amount);
            }
        }

        $em->flush();
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
                SalesCompanyServiceInfos::TRADE_TYPE_LONGTERM
            );

        $serviceFee = $serviceInfo ? $serviceInfo->getServiceFee() : 0;

        return $serviceFee;
    }

    /**
     * @param string    $year
     * @param string    $month
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     */
    private function generateFinanceDashboardSummary(
        $year,
        $month,
        $startDate,
        $endDate
    ) {
        // cash flow part
        $em = $this->getContainer()->get('doctrine')->getManager();

        $cashDashboard = $this->generateCashFlowArray($startDate, $endDate);

        $incomingTotalAmount = $cashDashboard[FinanceDashboardConstants::INCOMING_TOTAL_AMOUNT];

        $incomingTotalAmountDashboard = new FinanceDashboard();
        $incomingTotalAmountDashboard->setTimePeriod($year.'-'.$month);
        $incomingTotalAmountDashboard->setParameterKey('incoming_total_amount');
        $incomingTotalAmountDashboard->setParameterValue((string) $incomingTotalAmount);
        $incomingTotalAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($incomingTotalAmountDashboard);

        $wxIncomingAmount = $cashDashboard[FinanceDashboardConstants::INCOMING_WX_AMOUNT];

        $wxIncomingAmountDashboard = new FinanceDashboard();
        $wxIncomingAmountDashboard->setTimePeriod($year.'-'.$month);
        $wxIncomingAmountDashboard->setParameterKey('incoming_wx_amount');
        $wxIncomingAmountDashboard->setParameterValue((string) $wxIncomingAmount);
        $wxIncomingAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxIncomingAmountDashboard);

        $wxPubIncomingAmount = $cashDashboard[FinanceDashboardConstants::INCOMING_WX_PUB_AMOUNT];

        $wxPubIncomingAmountDashboard = new FinanceDashboard();
        $wxPubIncomingAmountDashboard->setTimePeriod($year.'-'.$month);
        $wxPubIncomingAmountDashboard->setParameterKey('incoming_wx_pub_amount');
        $wxPubIncomingAmountDashboard->setParameterValue((string) $wxPubIncomingAmount);
        $wxPubIncomingAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxPubIncomingAmountDashboard);

        $alipayIncomingAmount = $cashDashboard[FinanceDashboardConstants::INCOMING_ALIPAY_AMOUNT];

        $alipayIncomingAmountDashboard = new FinanceDashboard();
        $alipayIncomingAmountDashboard->setTimePeriod($year.'-'.$month);
        $alipayIncomingAmountDashboard->setParameterKey('incoming_alipay_amount');
        $alipayIncomingAmountDashboard->setParameterValue((string) $alipayIncomingAmount);
        $alipayIncomingAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($alipayIncomingAmountDashboard);

        $upacpIncomingAmount = $cashDashboard[FinanceDashboardConstants::INCOMING_UPACP_AMOUNT];

        $upacpIncomingAmountDashboard = new FinanceDashboard();
        $upacpIncomingAmountDashboard->setTimePeriod($year.'-'.$month);
        $upacpIncomingAmountDashboard->setParameterKey('incoming_upacp_amount');
        $upacpIncomingAmountDashboard->setParameterValue((string) $upacpIncomingAmount);
        $upacpIncomingAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($upacpIncomingAmountDashboard);

        $offlineIncomingAmount = $cashDashboard[FinanceDashboardConstants::INCOMING_OFFLINE_AMOUNT];

        $offlineIncomingAmountDashboard = new FinanceDashboard();
        $offlineIncomingAmountDashboard->setTimePeriod($year.'-'.$month);
        $offlineIncomingAmountDashboard->setParameterKey('incoming_offline_amount');
        $offlineIncomingAmountDashboard->setParameterValue((string) $offlineIncomingAmount);
        $offlineIncomingAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($offlineIncomingAmountDashboard);

        $incomingTotalCount = $cashDashboard[FinanceDashboardConstants::INCOMING_TOTAL_COUNT];

        $incomingTotalCountDashboard = new FinanceDashboard();
        $incomingTotalCountDashboard->setTimePeriod($year.'-'.$month);
        $incomingTotalCountDashboard->setParameterKey('incoming_total_count');
        $incomingTotalCountDashboard->setParameterValue((string) $incomingTotalCount);
        $incomingTotalCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($incomingTotalCountDashboard);

        $wxIncomingCount = $cashDashboard[FinanceDashboardConstants::INCOMING_WX_COUNT];

        $wxIncomingCountDashboard = new FinanceDashboard();
        $wxIncomingCountDashboard->setTimePeriod($year.'-'.$month);
        $wxIncomingCountDashboard->setParameterKey('incoming_wx_count');
        $wxIncomingCountDashboard->setParameterValue((string) $wxIncomingCount);
        $wxIncomingCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxIncomingCountDashboard);

        $wxPubIncomingCount = $cashDashboard[FinanceDashboardConstants::INCOMING_WX_PUB_COUNT];

        $wxPubIncomingCountDashboard = new FinanceDashboard();
        $wxPubIncomingCountDashboard->setTimePeriod($year.'-'.$month);
        $wxPubIncomingCountDashboard->setParameterKey('incoming_wx_pub_count');
        $wxPubIncomingCountDashboard->setParameterValue((string) $wxPubIncomingCount);
        $wxPubIncomingCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxPubIncomingCountDashboard);

        $alipayIncomingCount = $cashDashboard[FinanceDashboardConstants::INCOMING_ALIPAY_COUNT];

        $alipayIncomingCountDashboard = new FinanceDashboard();
        $alipayIncomingCountDashboard->setTimePeriod($year.'-'.$month);
        $alipayIncomingCountDashboard->setParameterKey('incoming_alipay_count');
        $alipayIncomingCountDashboard->setParameterValue((string) $alipayIncomingCount);
        $alipayIncomingCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($alipayIncomingCountDashboard);

        $upacpIncomingCount = $cashDashboard[FinanceDashboardConstants::INCOMING_UPACP_COUNT];

        $upacpIncomingCountDashboard = new FinanceDashboard();
        $upacpIncomingCountDashboard->setTimePeriod($year.'-'.$month);
        $upacpIncomingCountDashboard->setParameterKey('incoming_upacp_count');
        $upacpIncomingCountDashboard->setParameterValue((string) $upacpIncomingCount);
        $upacpIncomingCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($upacpIncomingCountDashboard);

        $offlineIncomingCount = $cashDashboard[FinanceDashboardConstants::INCOMING_OFFLINE_COUNT];

        $offlineIncomingCountDashboard = new FinanceDashboard();
        $offlineIncomingCountDashboard->setTimePeriod($year.'-'.$month);
        $offlineIncomingCountDashboard->setParameterKey('incoming_offline_count');
        $offlineIncomingCountDashboard->setParameterValue((string) $offlineIncomingCount);
        $offlineIncomingCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($offlineIncomingCountDashboard);

        // refund amount
        $totalRefundedAmount = $cashDashboard[FinanceDashboardConstants::REFUNDED_TOTAL_AMOUNT];

        $totalRefundedAmountDashboard = new FinanceDashboard();
        $totalRefundedAmountDashboard->setTimePeriod($year.'-'.$month);
        $totalRefundedAmountDashboard->setParameterKey('refunded_total_amount');
        $totalRefundedAmountDashboard->setParameterValue((string) $totalRefundedAmount);
        $totalRefundedAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($totalRefundedAmountDashboard);

        $wxRefundedAmount = $cashDashboard[FinanceDashboardConstants::REFUNDED_WX_AMOUNT];

        $wxRefundedAmountDashboard = new FinanceDashboard();
        $wxRefundedAmountDashboard->setTimePeriod($year.'-'.$month);
        $wxRefundedAmountDashboard->setParameterKey('refunded_wx_amount');
        $wxRefundedAmountDashboard->setParameterValue((string) $wxRefundedAmount);
        $wxRefundedAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxRefundedAmountDashboard);

        $wxPubRefundedAmount = $cashDashboard[FinanceDashboardConstants::REFUNDED_WX_PUB_AMOUNT];

        $wxPubRefundedAmountDashboard = new FinanceDashboard();
        $wxPubRefundedAmountDashboard->setTimePeriod($year.'-'.$month);
        $wxPubRefundedAmountDashboard->setParameterKey('refunded_wx_pub_amount');
        $wxPubRefundedAmountDashboard->setParameterValue((string) $wxPubRefundedAmount);
        $wxPubRefundedAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxPubRefundedAmountDashboard);

        $alipayRefundedAmount = $cashDashboard[FinanceDashboardConstants::REFUNDED_ALIPAY_AMOUNT];

        $alipayRefundedAmountDashboard = new FinanceDashboard();
        $alipayRefundedAmountDashboard->setTimePeriod($year.'-'.$month);
        $alipayRefundedAmountDashboard->setParameterKey('refunded_alipay_amount');
        $alipayRefundedAmountDashboard->setParameterValue((string) $alipayRefundedAmount);
        $alipayRefundedAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($alipayRefundedAmountDashboard);

        $upacpRefundedAmount = $cashDashboard[FinanceDashboardConstants::REFUNDED_UPACP_AMOUNT];

        $upacpRefundedAmountDashboard = new FinanceDashboard();
        $upacpRefundedAmountDashboard->setTimePeriod($year.'-'.$month);
        $upacpRefundedAmountDashboard->setParameterKey('refunded_upacp_amount');
        $upacpRefundedAmountDashboard->setParameterValue((string) $upacpRefundedAmount);
        $upacpRefundedAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($upacpRefundedAmountDashboard);

        $offlineRefundedAmount = $cashDashboard[FinanceDashboardConstants::REFUNDED_OFFLINE_AMOUNT];

        $offlineRefundedAmountDashboard = new FinanceDashboard();
        $offlineRefundedAmountDashboard->setTimePeriod($year.'-'.$month);
        $offlineRefundedAmountDashboard->setParameterKey('refunded_offline_amount');
        $offlineRefundedAmountDashboard->setParameterValue((string) $offlineRefundedAmount);
        $offlineRefundedAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($offlineRefundedAmountDashboard);

        $totalRefundedCount = $cashDashboard[FinanceDashboardConstants::REFUNDED_TOTAL_COUNT];

        $totalRefundedCountDashboard = new FinanceDashboard();
        $totalRefundedCountDashboard->setTimePeriod($year.'-'.$month);
        $totalRefundedCountDashboard->setParameterKey('refunded_total_count');
        $totalRefundedCountDashboard->setParameterValue((string) $totalRefundedCount);
        $totalRefundedCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($totalRefundedCountDashboard);

        $wxRefundedCount = $cashDashboard[FinanceDashboardConstants::REFUNDED_WX_COUNT];

        $wxRefundedCountDashboard = new FinanceDashboard();
        $wxRefundedCountDashboard->setTimePeriod($year.'-'.$month);
        $wxRefundedCountDashboard->setParameterKey('refunded_wx_count');
        $wxRefundedCountDashboard->setParameterValue((string) $wxRefundedCount);
        $wxRefundedCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxRefundedCountDashboard);

        $wxPubRefundedCount = $cashDashboard[FinanceDashboardConstants::REFUNDED_WX_PUB_COUNT];

        $wxPubRefundedCountDashboard = new FinanceDashboard();
        $wxPubRefundedCountDashboard->setTimePeriod($year.'-'.$month);
        $wxPubRefundedCountDashboard->setParameterKey('refunded_wx_pub_count');
        $wxPubRefundedCountDashboard->setParameterValue((string) $wxPubRefundedCount);
        $wxPubRefundedCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($wxPubRefundedCountDashboard);

        $alipayRefundedCount = $cashDashboard[FinanceDashboardConstants::REFUNDED_ALIPAY_COUNT];

        $alipayRefundedCountDashboard = new FinanceDashboard();
        $alipayRefundedCountDashboard->setTimePeriod($year.'-'.$month);
        $alipayRefundedCountDashboard->setParameterKey('refunded_alipay_count');
        $alipayRefundedCountDashboard->setParameterValue((string) $alipayRefundedCount);
        $alipayRefundedCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($alipayRefundedCountDashboard);

        $upacpRefundedCount = $cashDashboard[FinanceDashboardConstants::REFUNDED_UPACP_COUNT];

        $upacpRefundedCountDashboard = new FinanceDashboard();
        $upacpRefundedCountDashboard->setTimePeriod($year.'-'.$month);
        $upacpRefundedCountDashboard->setParameterKey('refunded_upacp_count');
        $upacpRefundedCountDashboard->setParameterValue((string) $upacpRefundedCount);
        $upacpRefundedCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($upacpRefundedCountDashboard);

        $offlineRefundedCount = $cashDashboard[FinanceDashboardConstants::REFUNDED_OFFLINE_COUNT];

        $offlineRefundedCountDashboard = new FinanceDashboard();
        $offlineRefundedCountDashboard->setTimePeriod($year.'-'.$month);
        $offlineRefundedCountDashboard->setParameterKey('refunded_offline_count');
        $offlineRefundedCountDashboard->setParameterValue((string) $offlineRefundedCount);
        $offlineRefundedCountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($offlineRefundedCountDashboard);

        $sumAmountDashboard = new FinanceDashboard();
        $sumAmountDashboard->setTimePeriod($year.'-'.$month);
        $sumAmountDashboard->setParameterKey('sum_amount');
        $sumAmountDashboard->setParameterValue((string) $cashDashboard[FinanceDashboardConstants::SUM_AMOUNT]);
        $sumAmountDashboard->setType(FinanceDashboard::TYPE_CASH_FLOW);
        $em->persist($sumAmountDashboard);

        // balance flow part
        $balanceDashboard = $this->generateBalanceFlowArray(
            $startDate,
            $endDate
        );

        $topUpTotalAmount = $balanceDashboard[FinanceDashboardConstants::TOTAL_TOP_UP_AMOUNT];

        $topUpTotalAmountDashboard = new FinanceDashboard();
        $topUpTotalAmountDashboard->setTimePeriod($year.'-'.$month);
        $topUpTotalAmountDashboard->setParameterKey('total_top_up_amount');
        $topUpTotalAmountDashboard->setParameterValue((string) $topUpTotalAmount);
        $topUpTotalAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($topUpTotalAmountDashboard);

        $wxTopUpAmount = $balanceDashboard[FinanceDashboardConstants::WX_TOPUP_AMOUNT];

        $wxTopUpAmountDashboard = new FinanceDashboard();
        $wxTopUpAmountDashboard->setTimePeriod($year.'-'.$month);
        $wxTopUpAmountDashboard->setParameterKey('wx_top_up_amount');
        $wxTopUpAmountDashboard->setParameterValue((string) $wxTopUpAmount);
        $wxTopUpAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($wxTopUpAmountDashboard);

        $wxPubTopUpAmount = $balanceDashboard[FinanceDashboardConstants::WX_PUB_TOP_UP_AMOUNT];

        $wxPubTopUpAmountDashboard = new FinanceDashboard();
        $wxPubTopUpAmountDashboard->setTimePeriod($year.'-'.$month);
        $wxPubTopUpAmountDashboard->setParameterKey('wx_pub_top_up_amount');
        $wxPubTopUpAmountDashboard->setParameterValue((string) $wxPubTopUpAmount);
        $wxPubTopUpAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($wxPubTopUpAmountDashboard);

        $alipayTopUpAmount = $balanceDashboard[FinanceDashboardConstants::ALIPAY_TOP_UP_AMOUNT];

        $alipayTopUpAmountDashboard = new FinanceDashboard();
        $alipayTopUpAmountDashboard->setTimePeriod($year.'-'.$month);
        $alipayTopUpAmountDashboard->setParameterKey('alipay_top_up_amount');
        $alipayTopUpAmountDashboard->setParameterValue((string) $alipayTopUpAmount);
        $alipayTopUpAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($alipayTopUpAmountDashboard);

        $upacpTopUpAmount = $balanceDashboard[FinanceDashboardConstants::UPACP_TOP_UP_AMOUNT];

        $upacpTopUpAmountDashboard = new FinanceDashboard();
        $upacpTopUpAmountDashboard->setTimePeriod($year.'-'.$month);
        $upacpTopUpAmountDashboard->setParameterKey('upacp_top_up_amount');
        $upacpTopUpAmountDashboard->setParameterValue((string) $upacpTopUpAmount);
        $upacpTopUpAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($upacpTopUpAmountDashboard);

        $refundToAccountAmount = $balanceDashboard[FinanceDashboardConstants::REFUND_TO_ACCOUNT_AMOUNT];

        $refundToAccountAmountDashboard = new FinanceDashboard();
        $refundToAccountAmountDashboard->setTimePeriod($year.'-'.$month);
        $refundToAccountAmountDashboard->setParameterKey('refund_to_account_amount');
        $refundToAccountAmountDashboard->setParameterValue((string) $refundToAccountAmount);
        $refundToAccountAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($refundToAccountAmountDashboard);

        $accountRefundToAccountAmount = $balanceDashboard[FinanceDashboardConstants::ACCOUNT_REFUND_TO_ACCOUNT_AMOUNT];

        $accountRefundToAccountAmountDashboard = new FinanceDashboard();
        $accountRefundToAccountAmountDashboard->setTimePeriod($year.'-'.$month);
        $accountRefundToAccountAmountDashboard->setParameterKey(FinanceDashboardConstants::ACCOUNT_REFUND_TO_ACCOUNT_AMOUNT);
        $accountRefundToAccountAmountDashboard->setParameterValue((string) $accountRefundToAccountAmount);
        $accountRefundToAccountAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($accountRefundToAccountAmountDashboard);

        $topUpTotalCount = $balanceDashboard[FinanceDashboardConstants::TOTAL_TOP_UP_COUNT];

        $topUpTotalCountDashboard = new FinanceDashboard();
        $topUpTotalCountDashboard->setTimePeriod($year.'-'.$month);
        $topUpTotalCountDashboard->setParameterKey('total_top_up_count');
        $topUpTotalCountDashboard->setParameterValue((string) $topUpTotalCount);
        $topUpTotalCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($topUpTotalCountDashboard);

        $wxTopUpCount = $balanceDashboard[FinanceDashboardConstants::WX_TOP_UP_COUNT];

        $wxTopUpCountDashboard = new FinanceDashboard();
        $wxTopUpCountDashboard->setTimePeriod($year.'-'.$month);
        $wxTopUpCountDashboard->setParameterKey('wx_top_up_count');
        $wxTopUpCountDashboard->setParameterValue((string) $wxTopUpCount);
        $wxTopUpCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($wxTopUpCountDashboard);

        $wxPubTopUpCount = $balanceDashboard[FinanceDashboardConstants::WX_PUB_TOP_UP_COUNT];

        $wxPubTopUpCountDashboard = new FinanceDashboard();
        $wxPubTopUpCountDashboard->setTimePeriod($year.'-'.$month);
        $wxPubTopUpCountDashboard->setParameterKey('wx_pub_top_up_count');
        $wxPubTopUpCountDashboard->setParameterValue((string) $wxPubTopUpCount);
        $wxPubTopUpCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($wxPubTopUpCountDashboard);

        $alipayTopUpCount = $balanceDashboard[FinanceDashboardConstants::ALIPAY_TOP_UP_COUNT];

        $alipayTopUpCountDashboard = new FinanceDashboard();
        $alipayTopUpCountDashboard->setTimePeriod($year.'-'.$month);
        $alipayTopUpCountDashboard->setParameterKey('alipay_top_up_count');
        $alipayTopUpCountDashboard->setParameterValue((string) $alipayTopUpCount);
        $alipayTopUpCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($alipayTopUpCountDashboard);

        $upacpTopUpCount = $balanceDashboard[FinanceDashboardConstants::UPACP_TOP_UP_COUNT];

        $upacpTopUpCountDashboard = new FinanceDashboard();
        $upacpTopUpCountDashboard->setTimePeriod($year.'-'.$month);
        $upacpTopUpCountDashboard->setParameterKey('upacp_top_up_count');
        $upacpTopUpCountDashboard->setParameterValue((string) $upacpTopUpCount);
        $upacpTopUpCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($upacpTopUpCountDashboard);

        $refundToAccountCount = $balanceDashboard[FinanceDashboardConstants::REFUND_TO_ACCOUNT_COUNT];

        $refundToAccountCountDashboard = new FinanceDashboard();
        $refundToAccountCountDashboard->setTimePeriod($year.'-'.$month);
        $refundToAccountCountDashboard->setParameterKey('refund_to_account_count');
        $refundToAccountCountDashboard->setParameterValue((string) $refundToAccountCount);
        $refundToAccountCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($refundToAccountCountDashboard);

        $accountRefundToAccountCount = $balanceDashboard[FinanceDashboardConstants::ACCOUNT_REFUND_TO_ACCOUNT_COUNT];

        $accountRefundToAccountCountDashboard = new FinanceDashboard();
        $accountRefundToAccountCountDashboard->setTimePeriod($year.'-'.$month);
        $accountRefundToAccountCountDashboard->setParameterKey(FinanceDashboardConstants::ACCOUNT_REFUND_TO_ACCOUNT_COUNT);
        $accountRefundToAccountCountDashboard->setParameterValue((string) $accountRefundToAccountCount);
        $accountRefundToAccountCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($accountRefundToAccountCountDashboard);

        $spaceOrderExpendAmount = $balanceDashboard[FinanceDashboardConstants::SPACE_EXPEND_AMOUNT];

        $spaceOrderExpendAmountDashboard = new FinanceDashboard();
        $spaceOrderExpendAmountDashboard->setTimePeriod($year.'-'.$month);
        $spaceOrderExpendAmountDashboard->setParameterKey('space_expend_amount');
        $spaceOrderExpendAmountDashboard->setParameterValue((string) $spaceOrderExpendAmount);
        $spaceOrderExpendAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($spaceOrderExpendAmountDashboard);

        $shopOrderExpendAmount = $balanceDashboard[FinanceDashboardConstants::SHOP_EXPEND_AMOUNT];

        $shopOrderExpendAmountDashboard = new FinanceDashboard();
        $shopOrderExpendAmountDashboard->setTimePeriod($year.'-'.$month);
        $shopOrderExpendAmountDashboard->setParameterKey('shop_expend_amount');
        $shopOrderExpendAmountDashboard->setParameterValue((string) $shopOrderExpendAmount);
        $shopOrderExpendAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($shopOrderExpendAmountDashboard);

        $activityOrderExpendAmount = $balanceDashboard[FinanceDashboardConstants::ACTIVITY_EXPEND_AMOUNT];

        $activityOrderExpendAmountDashboard = new FinanceDashboard();
        $activityOrderExpendAmountDashboard->setTimePeriod($year.'-'.$month);
        $activityOrderExpendAmountDashboard->setParameterKey('activity_expend_amount');
        $activityOrderExpendAmountDashboard->setParameterValue((string) $activityOrderExpendAmount);
        $activityOrderExpendAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($activityOrderExpendAmountDashboard);

        $membershipCardOrderExpendAmount = $balanceDashboard[FinanceDashboardConstants::MEMBERSHIP_CARD_EXPEND_AMOUNT];

        $membershipCardOrderExpendAmountDashboard = new FinanceDashboard();
        $membershipCardOrderExpendAmountDashboard->setTimePeriod($year.'-'.$month);
        $membershipCardOrderExpendAmountDashboard->setParameterKey('membership_card_expend_amount');
        $membershipCardOrderExpendAmountDashboard->setParameterValue((string) $membershipCardOrderExpendAmount);
        $membershipCardOrderExpendAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($membershipCardOrderExpendAmountDashboard);

        $totalExpendAmount = $balanceDashboard[FinanceDashboardConstants::TOTAL_EXPEND_AMOUNT];

        $totalExpendAmountDashboard = new FinanceDashboard();
        $totalExpendAmountDashboard->setTimePeriod($year.'-'.$month);
        $totalExpendAmountDashboard->setParameterKey('total_expend_amount');
        $totalExpendAmountDashboard->setParameterValue((string) $totalExpendAmount);
        $totalExpendAmountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($totalExpendAmountDashboard);

        $spaceOrderExpendCount = $balanceDashboard[FinanceDashboardConstants::SPACE_EXPEND_COUNT];

        $spaceOrderExpendCountDashboard = new FinanceDashboard();
        $spaceOrderExpendCountDashboard->setTimePeriod($year.'-'.$month);
        $spaceOrderExpendCountDashboard->setParameterKey('space_expend_count');
        $spaceOrderExpendCountDashboard->setParameterValue((string) $spaceOrderExpendCount);
        $spaceOrderExpendCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($spaceOrderExpendCountDashboard);

        $shopOrderExpendCount = $balanceDashboard[FinanceDashboardConstants::SHOP_EXPEND_COUNT];

        $shopOrderExpendCountDashboard = new FinanceDashboard();
        $shopOrderExpendCountDashboard->setTimePeriod($year.'-'.$month);
        $shopOrderExpendCountDashboard->setParameterKey('shop_expend_count');
        $shopOrderExpendCountDashboard->setParameterValue((string) $shopOrderExpendCount);
        $shopOrderExpendCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($shopOrderExpendCountDashboard);

        $activityOrderExpendCount = $balanceDashboard[FinanceDashboardConstants::ACTIVITY_EXPEND_COUNT];

        $activityOrderExpendCountDashboard = new FinanceDashboard();
        $activityOrderExpendCountDashboard->setTimePeriod($year.'-'.$month);
        $activityOrderExpendCountDashboard->setParameterKey('activity_expend_count');
        $activityOrderExpendCountDashboard->setParameterValue((string) $activityOrderExpendCount);
        $activityOrderExpendCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($activityOrderExpendCountDashboard);

        $membershipCardOrderExpendCount = $balanceDashboard[FinanceDashboardConstants::MEMBERSHIP_CARD_EXPEND_COUNT];

        $membershipCardOrderExpendCountDashboard = new FinanceDashboard();
        $membershipCardOrderExpendCountDashboard->setTimePeriod($year.'-'.$month);
        $membershipCardOrderExpendCountDashboard->setParameterKey('membership_card_expend_count');
        $membershipCardOrderExpendCountDashboard->setParameterValue((string) $membershipCardOrderExpendCount);
        $membershipCardOrderExpendCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($membershipCardOrderExpendCountDashboard);

        $totalExpendCount = $balanceDashboard[FinanceDashboardConstants::TOTAL_EXPEND_COUNT];

        $totalExpendCountDashboard = new FinanceDashboard();
        $totalExpendCountDashboard->setTimePeriod($year.'-'.$month);
        $totalExpendCountDashboard->setParameterKey('total_expend_count');
        $totalExpendCountDashboard->setParameterValue((string) $totalExpendCount);
        $totalExpendCountDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($totalExpendCountDashboard);

        // add last total balance
        $lastTotalBalance = $balanceDashboard[FinanceDashboardConstants::TOTAL_BALANCE];

        $lastTotalBalanceDashboard = new FinanceDashboard();
        $lastTotalBalanceDashboard->setTimePeriod($year.'-'.$month);
        $lastTotalBalanceDashboard->setParameterKey('total_balance');
        $lastTotalBalanceDashboard->setParameterValue((string) $lastTotalBalance);
        $lastTotalBalanceDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($lastTotalBalanceDashboard);

        $beforeLastMonthTotalBalance = $balanceDashboard[FinanceDashboardConstants::LAST_MONTH_TOTAL_BALANCE];

        $beforeLastMonthTotalBalanceDashboard = new FinanceDashboard();
        $beforeLastMonthTotalBalanceDashboard->setTimePeriod($year.'-'.$month);
        $beforeLastMonthTotalBalanceDashboard->setParameterKey('last_month_total_balance');
        $beforeLastMonthTotalBalanceDashboard->setParameterValue((string) $beforeLastMonthTotalBalance);
        $beforeLastMonthTotalBalanceDashboard->setType(FinanceDashboard::TYPE_BALANCE_FLOW);
        $em->persist($beforeLastMonthTotalBalanceDashboard);

        $em->flush();
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    private function generateCashFlowArray(
        $startDate,
        $endDate
    ) {
        $incomingTotalAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate
            );

        $wxIncomingAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxPubIncomingAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT_PUB
            );

        $alipayIncomingAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $upacpIncomingAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_UNIONPAY
            );

        $offlineIncomingAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getIncomingTotalAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_OFFLINE
            );

        $incomingTotalCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate
            );

        $wxIncomingCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxPubIncomingCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT_PUB
            );

        $alipayIncomingCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $upacpIncomingCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_UNIONPAY
            );

        $offlineIncomingCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countIncomingOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_OFFLINE
            );

        $totalRefundedAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate
            );

        $wxRefundedAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxPubRefundedAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT_PUB
            );

        $alipayRefundedAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $upacpRefundedAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_UNIONPAY
            );

        $offlineRefundedAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedOrderAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_OFFLINE
            );

        $totalRefundedCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate
            );

        $wxRefundedCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxPubRefundedCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT_PUB
            );

        $alipayRefundedCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $upacpRefundedCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_UNIONPAY
            );

        $offlineRefundedCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedOrders(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_OFFLINE
            );

        $sumAmount = $incomingTotalAmount - $totalRefundedAmount;

        return array(
            FinanceDashboardConstants::INCOMING_TOTAL_AMOUNT => $incomingTotalAmount,
            FinanceDashboardConstants::INCOMING_WX_AMOUNT => $wxIncomingAmount,
            FinanceDashboardConstants::INCOMING_WX_PUB_AMOUNT => $wxPubIncomingAmount,
            FinanceDashboardConstants::INCOMING_ALIPAY_AMOUNT => $alipayIncomingAmount,
            FinanceDashboardConstants::INCOMING_UPACP_AMOUNT => $upacpIncomingAmount,
            FinanceDashboardConstants::INCOMING_OFFLINE_AMOUNT => $offlineIncomingAmount,
            FinanceDashboardConstants::INCOMING_TOTAL_COUNT => $incomingTotalCount,
            FinanceDashboardConstants::INCOMING_WX_COUNT => $wxIncomingCount,
            FinanceDashboardConstants::INCOMING_WX_PUB_COUNT => $wxPubIncomingCount,
            FinanceDashboardConstants::INCOMING_ALIPAY_COUNT => $alipayIncomingCount,
            FinanceDashboardConstants::INCOMING_UPACP_COUNT => $upacpIncomingCount,
            FinanceDashboardConstants::INCOMING_OFFLINE_COUNT => $offlineIncomingCount,
            FinanceDashboardConstants::REFUNDED_TOTAL_AMOUNT => $totalRefundedAmount,
            FinanceDashboardConstants::REFUNDED_WX_AMOUNT => $wxRefundedAmount,
            FinanceDashboardConstants::REFUNDED_WX_PUB_AMOUNT => $wxPubRefundedAmount,
            FinanceDashboardConstants::REFUNDED_ALIPAY_AMOUNT => $alipayRefundedAmount,
            FinanceDashboardConstants::REFUNDED_UPACP_AMOUNT => $upacpRefundedAmount,
            FinanceDashboardConstants::REFUNDED_OFFLINE_AMOUNT => $offlineRefundedAmount,
            FinanceDashboardConstants::REFUNDED_TOTAL_COUNT => $totalRefundedCount,
            FinanceDashboardConstants::REFUNDED_WX_COUNT => $wxRefundedCount,
            FinanceDashboardConstants::REFUNDED_WX_PUB_COUNT => $wxPubRefundedCount,
            FinanceDashboardConstants::REFUNDED_ALIPAY_COUNT => $alipayRefundedCount,
            FinanceDashboardConstants::REFUNDED_UPACP_COUNT => $upacpRefundedCount,
            FinanceDashboardConstants::REFUNDED_OFFLINE_COUNT => $offlineRefundedCount,
            FinanceDashboardConstants::SUM_AMOUNT => $sumAmount,
        );
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return array
     */
    private function generateBalanceFlowArray(
        $startDate,
        $endDate
    ) {
        $topUpTotalAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getTopUpAmount(
                $startDate,
                $endDate
            );

        $wxTopUpAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getTopUpAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxPubTopUpAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getTopUpAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT_PUB
            );

        $alipayTopUpAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getTopUpAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $upacpTopUpAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getTopUpAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_UNIONPAY
            );

        $refundToAccountAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRefundedToBalanceAmount(
                $startDate,
                $endDate
            );

        $accountRefundToAccountAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getTopUpAmount(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ACCOUNT
            );

        $topUpTotalCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countTopUpOrder(
                $startDate,
                $endDate
            );

        $wxTopUpCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countTopUpOrder(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT
            );

        $wxPubTopUpCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countTopUpOrder(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_WECHAT_PUB
            );

        $alipayTopUpCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countTopUpOrder(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ALIPAY
            );

        $upacpTopUpCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countTopUpOrder(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_UNIONPAY
            );

        $refundToAccountCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countRefundedToBalance(
                $startDate,
                $endDate
            );

        $accountRefundToAccountCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countTopUpOrder(
                $startDate,
                $endDate,
                ProductOrder::CHANNEL_ACCOUNT
            );

        $spaceOrderExpendAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->spaceOrderByAccountAmount(
                $startDate,
                $endDate
            );

        $shopOrderExpendAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->shopOrderByAccountAmount(
                $startDate,
                $endDate
            );

        $activityOrderExpendAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->activityOrderByAccountAmount(
                $startDate,
                $endDate
            );

        $membershipCardOrderExpendAmount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->membershipCardOrderByAccount(
                $startDate,
                $endDate
            );

        $totalExpendAmount = $spaceOrderExpendAmount + $shopOrderExpendAmount + $activityOrderExpendAmount + $membershipCardOrderExpendAmount;

        $spaceOrderExpendCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countSpaceOrderByAccount(
                $startDate,
                $endDate
            );

        $shopOrderExpendCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countShopOrderByAccount(
                $startDate,
                $endDate
            );

        $activityOrderExpendCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countActivityOrderByAccount(
                $startDate,
                $endDate
            );

        $membershipCardOrderExpendCount = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countMembershipCardOrderByAccount(
                $startDate,
                $endDate
            );

        $totalExpendCount = $spaceOrderExpendCount + $shopOrderExpendCount + $activityOrderExpendCount + $membershipCardOrderExpendCount;

        $lastTotalBalance = $this->getLastTotalBalance(
            $startDate,
            $endDate
        );

        $startDateBefore = clone $startDate;
        $startDateBefore->modify('-1 month');
        $endStringBefore = $startDateBefore->format('Y-m-t');
        $endDateBefore = new \DateTime($endStringBefore);

        $beforeLastMonthTotalBalance = $this->getLastTotalBalance(
            $startDateBefore,
            $endDateBefore
        );

        return array(
            FinanceDashboardConstants::TOTAL_TOP_UP_AMOUNT => $topUpTotalAmount,
            FinanceDashboardConstants::WX_TOPUP_AMOUNT => $wxTopUpAmount,
            FinanceDashboardConstants::WX_PUB_TOP_UP_AMOUNT => $wxPubTopUpAmount,
            FinanceDashboardConstants::ALIPAY_TOP_UP_AMOUNT => $alipayTopUpAmount,
            FinanceDashboardConstants::UPACP_TOP_UP_AMOUNT => $upacpTopUpAmount,
            FinanceDashboardConstants::REFUND_TO_ACCOUNT_AMOUNT => $refundToAccountAmount,
            FinanceDashboardConstants::ACCOUNT_REFUND_TO_ACCOUNT_AMOUNT => $accountRefundToAccountAmount,
            FinanceDashboardConstants::TOTAL_TOP_UP_COUNT => $topUpTotalCount,
            FinanceDashboardConstants::WX_TOP_UP_COUNT => $wxTopUpCount,
            FinanceDashboardConstants::WX_PUB_TOP_UP_COUNT => $wxPubTopUpCount,
            FinanceDashboardConstants::ALIPAY_TOP_UP_COUNT => $alipayTopUpCount,
            FinanceDashboardConstants::UPACP_TOP_UP_COUNT => $upacpTopUpCount,
            FinanceDashboardConstants::REFUND_TO_ACCOUNT_COUNT => $refundToAccountCount,
            FinanceDashboardConstants::ACCOUNT_REFUND_TO_ACCOUNT_COUNT => $accountRefundToAccountCount,
            FinanceDashboardConstants::SPACE_EXPEND_AMOUNT => $spaceOrderExpendAmount,
            FinanceDashboardConstants::SHOP_EXPEND_AMOUNT => $shopOrderExpendAmount,
            FinanceDashboardConstants::ACTIVITY_EXPEND_AMOUNT => $activityOrderExpendAmount,
            FinanceDashboardConstants::MEMBERSHIP_CARD_EXPEND_AMOUNT => $membershipCardOrderExpendAmount,
            FinanceDashboardConstants::TOTAL_EXPEND_AMOUNT => $totalExpendAmount,
            FinanceDashboardConstants::SPACE_EXPEND_COUNT => $spaceOrderExpendCount,
            FinanceDashboardConstants::SHOP_EXPEND_COUNT => $shopOrderExpendCount,
            FinanceDashboardConstants::ACTIVITY_EXPEND_COUNT => $activityOrderExpendCount,
            FinanceDashboardConstants::MEMBERSHIP_CARD_EXPEND_COUNT => $membershipCardOrderExpendCount,
            FinanceDashboardConstants::TOTAL_EXPEND_COUNT => $totalExpendCount,
            FinanceDashboardConstants::TOTAL_BALANCE => $lastTotalBalance,
            FinanceDashboardConstants::LAST_MONTH_TOTAL_BALANCE => $beforeLastMonthTotalBalance,
        );
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
