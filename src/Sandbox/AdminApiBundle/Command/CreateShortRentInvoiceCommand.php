<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Entity\Finance\FinanceShortRentInvoice;
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
        $this->setFinanceShortRentInvoice();
    }

    /**
     * Set status cancelled and restock inventory.
     */
    private function setFinanceShortRentInvoice()
    {
        $now = new \DateTime();
        $date = clone $now;
        $date->modify('first day of this month');
        $date->setTime(0, 0, 0);

        $orders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getCompletedOrders($date, $now);

        $companyArray = [];
        foreach ($orders as $order) {
            $companyId = $order['companyId'];
            $amount = $order['discountPrice'] * (1 - $order['serviceFee'] / 100);

            if (!array_key_exists($companyId, $companyArray)) {
                $companyArray[$companyId] = [
                    'amount' => $amount,
                ];
            } else {
                $companyArray[$companyId] = [
                    'amount' => $amount + $companyArray[$companyId]['amount'],
                ];
            }
        }

        $em = $this->getContainer()->get('doctrine')->getManager();

        foreach ($companyArray as $key => $value) {
            $invoice = new FinanceShortRentInvoice();
            $invoice->setAmount($value['amount']);
            $invoice->setCompanyId((int) $key);

            $em->persist($invoice);
        }

        $em->flush();
    }
}
