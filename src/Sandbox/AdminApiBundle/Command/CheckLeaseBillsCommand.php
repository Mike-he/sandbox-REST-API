<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Traits\SendNotification;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckLeaseBillsCommand extends ContainerAwareCommand
{
    use SendNotification;

    protected function configure()
    {
        $this->setName('sandbox:api-bundle:check_lease_bills')
            ->setDescription('Check Lease Bills');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $now = new \DateTime('now');
        $now->setTime(23, 59, 59);

        $leases = $em->getRepository('SandboxApiBundle:Lease\Lease')
            ->findBy(
                array(
                    'status' => Lease::LEASE_STATUS_PERFORMING,
                    'endDate' => $now,
                )
            );

        foreach ($leases as $lease) {
            $billCount = $em->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease,
                    null,
                    LeaseBill::STATUS_UNPAID
                );

            $leaseId = $lease->getId();
            $urlParam = 'ptype=billsList&status=unpaid&leasesId='.$leaseId;
            $contentArray = $this->generateLeaseContentArray($urlParam);
            // send Jpush notification
            if ($billCount == 0) {
                $this->generateJpushNotification(
                    [
                        $lease->getSupervisorId(),
                    ],
                    LeaseConstants::LEASE_ENDED_WITHOUT_UNPAID_BILLS_MESSAGE,
                    null,
                    $contentArray
                );
            } else {
                $this->generateJpushNotification(
                    [
                        $lease->getSupervisorId(),
                    ],
                    LeaseConstants::LEASE_ENDED_WITH_UNPAID_BILLS_MESSAGE,
                    null,
                    $contentArray
                );
            }
        }

        $output->writeln('Success!');
    }
}
