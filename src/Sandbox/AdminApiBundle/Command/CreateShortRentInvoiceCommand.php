<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Entity\Finance\FinanceShortRentInvoice;
use Sandbox\ApiBundle\Entity\Finance\FinanceSummary;
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
        $now->modify('-3 day');

        $firstDate = clone $now;
        $firstDate->modify('first day of this month');
        $firstDate->setTime(0, 0, 0);
        $lastDate = clone $now;
        $lastDate->modify('last day of this month');
        $lastDate->setTime(23, 59, 59);

        $em = $this->getContainer()->get('doctrine')->getManager();

        $companyArray = $this->setFinanceShortRentInvoice(
            $em,
            $firstDate,
            $lastDate
        );

        $companyArray = $this->setFinanceSummary(
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
    }

    /**
     * @param $em
     * @param $firstDate
     * @param $lastDate
     *
     * @return array
     */
    private function setFinanceShortRentInvoice(
        $em,
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
                ];
            } else {
                $companyArray[$companyId] = [
                    'short_rent_balance' => $amount + $companyArray[$companyId]['short_rent_balance'],
                    'short_rent_count' => 1 + $companyArray[$companyId]['short_rent_count'],
                ];
            }
        }

        foreach ($companyArray as $key => $value) {
            $invoice = new FinanceShortRentInvoice();
            $invoice->setAmount($value['short_rent_balance']);
            $invoice->setCompanyId((int) $key);

            $em->persist($invoice);
        }

        $em->flush();

        return $companyArray;
    }

    /**
     * @param $firstDate
     * @param $lastDate
     * @param $companyArray
     *
     * @return mixed
     */
    private function setFinanceSummary(
        $firstDate,
        $lastDate,
        $companyArray
    ) {
        $longRents = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Finance\FinanceLongRentServiceBill')
            ->getServiceBillsByMonth($firstDate, $lastDate);

        foreach ($companyArray as $key => $value) {
            $longRentArray = [
                'long_rent_balance' => 0,
                'long_rent_service_balance' => 0,
                'long_rent_count' => 0,
                'event_order_balance' => 0,
                'event_order_count' => 0,
            ];

            $companyArray[$key] = array_merge($companyArray[$key], $longRentArray);
        }

        foreach ($longRents as $longRent) {
            $companyId = $longRent->getCompanyId();
            $serviceAmount = $longRent->getAmount();
            $incomeAmount = $longRent->getBill()->getRevisedAmount();

            if (!array_key_exists($companyId, $companyArray)) {
                $companyArray[$companyId] = [
                    'short_rent_balance' => 0,
                    'short_rent_count' => 0,
                    'long_rent_balance' => $incomeAmount,
                    'long_rent_service_balance' => $serviceAmount,
                    'long_rent_count' => 1,
                    'event_order_balance' => 0,
                    'event_order_count' => 0,
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

            $em->persist($summary);
        }

        $em->flush();
    }
}
