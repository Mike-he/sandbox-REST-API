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

        $fee = $this->getCompanyServiceFee($companyId);

        $serviceBill = $em->getRepository('SandboxApiBundle:Finance\FinanceLongRentServiceBill')
            ->findOneBy(
                array(
                    'bill' => $bill,
                    'type' => $type,
                )
            );

        if (!$serviceBill) {
            $amount = ($bill->getRevisedAmount() * $fee) / 100;

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
}
