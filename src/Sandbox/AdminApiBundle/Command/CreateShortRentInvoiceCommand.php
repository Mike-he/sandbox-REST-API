<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Entity\Finance\FinanceSalesWalletFlow;
use Sandbox\ApiBundle\Entity\Finance\FinanceShortRentInvoice;
use Sandbox\ApiBundle\Entity\Finance\FinanceSummary;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Service\ServiceOrder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateShortRentInvoiceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('create:short_rent_invoice')
            ->setDescription('create short rent invoice')
            ->addArgument('my_argument', InputArgument::OPTIONAL, 'Argument description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTime();

        $firstDate = clone $now;
        $firstDate->modify('first day of last month');
        $firstDate->setTime(0, 0, 0);
        $lastDate = clone $now;
        $lastDate->modify('last day of last month');
        $lastDate->setTime(23, 59, 59);

        $em = $this->getContainer()->get('doctrine')->getManager();

        $companyArray = $this->setFinanceShortRentInvoice(
            $firstDate,
            $lastDate
        );

        $companyArray = $this->setLongBillSummary(
            $firstDate,
            $lastDate,
            $companyArray
        );

        $companyArray = $this->setMembershipOrderSummary(
            $firstDate,
            $lastDate,
            $companyArray
        );

        $companyArray = $this->setServiceOrderSummary(
            $firstDate,
            $lastDate,
            $companyArray
        );

        $this->setEventOrderSummary(
            $em,
            $firstDate,
            $lastDate,
            $companyArray
        );

        $output->writeln('Success!');
    }

    /**
     * @param $firstDate
     * @param $lastDate
     *
     * @return array
     */
    private function setFinanceShortRentInvoice(
        $firstDate,
        $lastDate
    ) {
        $orders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getCompletedOrders($firstDate, $lastDate);

        $companyArray = [];
        foreach ($orders as $order) {
            $companyId = $order['companyId'];
            $amount = $order['discountPrice'] * (1 - $order['serviceFee'] / 100);

            if (!array_key_exists($companyId, $companyArray)) {
                $companyArray[$companyId] = [
                    'short_rent_balance' => $amount,
                    'short_rent_count' => 1,
                    'long_rent_balance' => 0,
                    'long_rent_service_balance' => 0,
                    'long_rent_count' => 0,
                    'event_order_balance' => 0,
                    'event_order_count' => 0,
                    'membership_order_balance' => 0,
                    'membership_order_count' => 0,
                    'service_order_balance' => 0,
                    'service_order_count' => 0,
                ];
            } else {
                $companyArray[$companyId]['short_rent_balance'] += $amount;
                ++$companyArray[$companyId]['short_rent_count'];
            }
        }

        return $companyArray;
    }

    /**
     * @param $firstDate
     * @param $lastDate
     * @param $companyArray
     *
     * @return mixed
     */
    private function setLongBillSummary(
        $firstDate,
        $lastDate,
        $companyArray
    ) {
        // long rent orders
        $longBills = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBillsByDates($firstDate, $lastDate);

        foreach ($longBills as $longBill) {
            /** @var LeaseBill $longBill */
            $incomeAmount = $longBill->getRevisedAmount();
            $serviceAmount = 0;

            $serviceBill = $this->getContainer()
                ->get('doctrine')
                ->getRepository('SandboxApiBundle:Finance\FinanceLongRentServiceBill')
                ->findOneBy([
                    'orderNumber' => $longBill->getSerialNumber(),
                ]);
            if (!is_null($serviceBill)) {
                $serviceAmount = $serviceBill->getAmount();
            }

            $companyId = $longBill->getLease()->getCompanyId();

            if (!array_key_exists($companyId, $companyArray)) {
                $companyArray[$companyId] = [
                    'short_rent_balance' => 0,
                    'short_rent_count' => 0,
                    'long_rent_balance' => $incomeAmount,
                    'long_rent_service_balance' => $serviceAmount,
                    'long_rent_count' => 1,
                    'event_order_balance' => 0,
                    'event_order_count' => 0,
                    'membership_order_balance' => 0,
                    'membership_order_count' => 0,
                    'service_order_balance' => 0,
                    'service_order_count' => 0,
                ];
            } else {
                $companyArray[$companyId]['long_rent_balance'] += $incomeAmount;
                $companyArray[$companyId]['long_rent_service_balance'] += $serviceAmount;
                ++$companyArray[$companyId]['long_rent_count'];
            }
        }

        return $companyArray;
    }

    /**
     * @param $firstDate
     * @param $lastDate
     * @param $companyArray
     *
     * @return mixed
     */
    private function setMembershipOrderSummary(
        $firstDate,
        $lastDate,
        $companyArray
    ) {
        $membershipOrders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getMembershipOrdersByDate(
                $firstDate,
                $lastDate
            );

        foreach ($membershipOrders as $membershipOrder) {
            $amount = $membershipOrder->getPrice() * (1 - $membershipOrder->getServiceFee() / 100);
            $card = $membershipOrder->getCard();
            $companyId = $card->getCompanyId();

            if (!array_key_exists($companyId, $companyArray)) {
                $companyArray[$companyId] = [
                    'short_rent_balance' => 0,
                    'short_rent_count' => 0,
                    'long_rent_balance' => 0,
                    'long_rent_service_balance' => 0,
                    'long_rent_count' => 0,
                    'event_order_balance' => 0,
                    'event_order_count' => 0,
                    'membership_order_balance' => $amount,
                    'membership_order_count' => 1,
                    'service_order_balance' => 0,
                    'service_order_count' => 0,
                ];
            } else {
                $companyArray[$companyId]['membership_order_balance'] += $amount;
                ++$companyArray[$companyId]['membership_order_count'];
            }
        }

        return $companyArray;
    }

    /**
     * @param $firstDate
     * @param $lastDate
     * @param $companyArray
     *
     * @return mixed
     */
    private function setServiceOrderSummary(
        $firstDate,
        $lastDate,
        $companyArray
    ) {
        $serviceOrders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->getServiceOrdersByDate(
                $firstDate,
                $lastDate
            );

        foreach ($serviceOrders as $serviceOrder) {
            /** @var ServiceOrder $serviceOrder */
            $amount = $serviceOrder->getPrice();
            $companyId = $serviceOrder->getCompanyId();

            if (!array_key_exists($companyId, $companyArray)) {
                $companyArray[$companyId] = [
                    'short_rent_balance' => 0,
                    'short_rent_count' => 0,
                    'long_rent_balance' => 0,
                    'long_rent_service_balance' => 0,
                    'long_rent_count' => 0,
                    'event_order_balance' => 0,
                    'event_order_count' => 0,
                    'membership_order_balance' => 0,
                    'membership_order_count' => 0,
                    'service_order_balance' => $amount,
                    'service_order_count' => 1,
                ];
            } else {
                $companyArray[$companyId]['service_order_balance'] += $amount;
                ++$companyArray[$companyId]['service_order_count'];
            }
        }

        return $companyArray;
    }

    /**
     * @param $em
     * @param $firstDate
     * @param $lastDate
     * @param $companyArray
     */
    private function setEventOrderSummary(
        $em,
        $firstDate,
        $lastDate,
        $companyArray
    ) {
        // event orders
        $events = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->getSumEventOrders(
                $firstDate,
                $lastDate
            );

        foreach ($events as $event) {
            $price = $event['price'];
            $companyId = $event['salesCompanyId'];

            if (!array_key_exists($companyId, $companyArray)) {
                $companyArray[$companyId] = [
                    'short_rent_balance' => 0,
                    'short_rent_count' => 0,
                    'long_rent_balance' => 0,
                    'long_rent_service_balance' => 0,
                    'long_rent_count' => 0,
                    'event_order_balance' => $price,
                    'event_order_count' => 1,
                    'membership_order_balance' => 0,
                    'membership_order_count' => 0,
                    'service_order_balance' => 0,
                    'service_order_count' => 0,
                ];
            } else {
                $companyArray[$companyId]['event_order_balance'] += $price;
                ++$companyArray[$companyId]['event_order_count'];
            }
        }

        foreach ($companyArray as $key => $value) {
            $summary = new FinanceSummary();
            $summary->setCompanyId((int) $key);
            $summary->setShortRentBalance($value['short_rent_balance']);
            $summary->setShortRentCount((int) $value['short_rent_count']);
            $summary->setLongRentBalance($value['long_rent_balance']);
            $summary->setLongRentCount((int) $value['long_rent_count']);
            $summary->setLongRentBillBalance($value['long_rent_service_balance']);
            $summary->setLongRentBillCount((int) $value['long_rent_count']);
            $summary->setEventOrderBalance($value['event_order_balance']);
            $summary->setEventOrderCount((int) $value['event_order_count']);
            $summary->setMembershipOrderBalance($value['membership_order_balance']);
            $summary->setMembershipOrderCount((int) $value['membership_order_count']);
            $summary->setServiceOrderBalance($value['service_order_balance']);
            $summary->setServiceOrderCount((int) $value['service_order_count']);
            $summary->setSummaryDate($lastDate);

            $preorderAmount = $this->getContainer()
                ->get('doctrine')
                ->getRepository('SandboxApiBundle:Order\ProductOrder')
                ->sumCompletedPreorder($firstDate, $lastDate, $key);

            $monthAmount = $value['short_rent_balance'] + $value['event_order_balance'] + $value['membership_order_balance'] - $preorderAmount;

            $invoice = new FinanceShortRentInvoice();
            $invoice->setAmount($monthAmount);
            $invoice->setCompanyId((int) $key);
            $invoice->setCreationDate($lastDate);

            $em->persist($invoice);
            $em->persist($summary);

            $wallet = $this->getContainer()
                ->get('doctrine')
                ->getRepository('SandboxApiBundle:Finance\FinanceSalesWallet')
                ->findOneBy(['companyId' => $key]);

            if (!is_null($wallet)) {
                $withdrawableAmount = $wallet->getWithdrawableAmount();
                $totalAmount = $wallet->getTotalAmount();

                $incomingWithdrawAmount = $withdrawableAmount + $monthAmount;

                $wallet->setWithdrawableAmount($incomingWithdrawAmount);
                $wallet->setTotalAmount($totalAmount + $monthAmount);

                $this->getContainer()->get('sandbox_api.sales_wallet')
                    ->generateSalesWalletFlows(
                    FinanceSalesWalletFlow::MONTHLY_ORDER_AMOUNT,
                    "+$monthAmount",
                    $key,
                    null,
                    $incomingWithdrawAmount
                );
            }
        }

        $em->flush();
    }
}
