<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentServiceBill;
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

        $amount = $this->calculateAmount($bill, $companyId);

        $serviceFee = $em->getRepository('SandboxApiBundle:Finance\FinanceLongRentServiceBill')
            ->findOneBy(
                array(
                    'bill' => $bill,
                    'type' => $type,
                )
            );

        if (!$serviceFee) {
            $serviceFee = new FinanceLongRentServiceBill();
            $serviceFee->setSerialNumber($serialNumber);
            $serviceFee->setAmount($amount);
            $serviceFee->setType($type);
            $serviceFee->setCompanyId($companyId);
            $serviceFee->setBill($bill);

            $em->persist($serviceFee);
            $em->flush();
        }
    }

    /**
     * @param $bill
     * @param $companyId
     *
     * @return mixed
     */
    private function calculateAmount(
        $bill,
        $companyId
    ) {
        $serviceInfo = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
            ->getCompanyServiceByType(
                $companyId,
                Room::TYPE_LONG_TERM
            );

        $serviceFee = $serviceInfo->getServiceFee() / 100;

        $amount = $bill->getRevisedAmount() * $serviceFee;

        return $amount;
    }
}
