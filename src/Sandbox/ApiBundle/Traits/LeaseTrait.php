<?php

namespace Sandbox\ApiBundle\Traits;

use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Log\Log;

/**
 * Log Trait.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait LeaseTrait
{
    /**
     * @param Lease $lease
     */
    private function setLeaseAttributions(
        $lease
    ) {
        $changeLogs = array();
        $appointment = $lease->getProductAppointment();
        if (!is_null($appointment)) {
            $changeLogs['applicant'] = $appointment->getApplicantName();
            $changeLogs['apply_date'] = $appointment->getCreationDate();
        }

        $logConforming = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Log\Log')
            ->getLatestAdminLog(
                Log::MODULE_LEASE,
                Log::OBJECT_LEASE,
                $lease->getId(),
                array(
                    Log::ACTION_CREATE,
                )
            );
        if (!is_null($logConforming)) {
            $changeLogs['lease_conforming_admin'] = $this->getUserProfileName($logConforming->getAdminUsername());
            $changeLogs['lease_conforming_date'] = $logConforming->getCreationDate();
        }

        if (!is_null($lease->getConformedDate())) {
            $changeLogs['lease_conformed_user'] = $this->getUserProfileName($lease->getSupervisor());
            $changeLogs['lease_conformed_date'] = $lease->getConformedDate();
        }

        $logPerforming = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Log\Log')
            ->getLatestAdminLog(
                Log::MODULE_LEASE,
                Log::OBJECT_LEASE,
                $lease->getId(),
                array(
                    Log::ACTION_PERFORMING,
                )
            );
        if (!is_null($logPerforming)) {
            $changeLogs['lease_performing_admin'] = $this->getUserProfileName($logPerforming->getAdminUsername());
            $changeLogs['lease_performing_date'] = $logPerforming->getCreationDate();
        }

        $logClose = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Log\Log')
            ->getLatestAdminLog(
                Log::MODULE_LEASE,
                Log::OBJECT_LEASE,
                $lease->getId(),
                array(
                    Log::ACTION_CLOSE,
                    Log::ACTION_TERMINATE,
                    Log::ACTION_END,
                )
            );
        if (!is_null($logClose)) {
            $changeLogs['lease_close_admin'] = $this->getUserProfileName($logClose->getAdminUsername());
            $changeLogs['lease_close_date'] = $logClose->getCreationDate();
        }

        $lease->setChangeLogs($changeLogs);

        $bills = $this->getLeaseBillRepo()->findBy(array(
            'lease' => $lease,
            'type' => LeaseBill::TYPE_LEASE,
        ));
        $lease->setBills($bills);

        $totalLeaseBills = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBills(
                $lease,
                LeaseBill::TYPE_LEASE
            );
        $lease->setTotalLeaseBillsAmount($totalLeaseBills);

        $paidLeaseBills = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBills(
                $lease,
                LeaseBill::TYPE_LEASE,
                [LeaseBill::STATUS_UNPAID, LeaseBill::STATUS_PAID]
            );
        $lease->setPaidLeaseBillsAmount($paidLeaseBills);

        $otherBills = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBills(
                $lease,
                LeaseBill::TYPE_OTHER
            );
        $lease->setOtherBillsAmount($otherBills);

        $pendingLeaseBill = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->sumBillsFees(
                $lease,
                LeaseBill::STATUS_PENDING
            );
        $pendingLeaseBill = is_null($pendingLeaseBill) ? 0 : $pendingLeaseBill;
        $lease->setPushedLeaseBillsFees($pendingLeaseBill);
    }
    
    /**
     * @param $userId
     *
     * @return string
     */
    private function getUserProfileName(
        $userId
    ) {
        $user = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:User\UserView')
            ->find($userId);

        if (is_null($user)) {
            return '';
        }

        return $user->getName();
    }
}
