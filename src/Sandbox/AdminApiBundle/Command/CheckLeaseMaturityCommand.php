<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Entity\Admin\AdminStatusLog;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckLeaseMaturityCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sandbox:api-bundle:check_lease_maturity')
            ->setDescription('Check Lease Maturity Status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $leases = $em->getRepository('SandboxApiBundle:Lease\Lease')
            ->findBy(
                array(
                    'status' => Lease::LEASE_STATUS_PERFORMING,
                )
            );

        foreach ($leases as $lease) {
            $endDate = $lease->getEndDate();
            $yesterday = new \DateTime('yesterday');
            $yesterday->setTime(23, 59, 59);

            if ($yesterday == $endDate) {
                $lease->setStatus(Lease::LEASE_STATUS_MATURED);

                $logMessage = '合同已到期';
                $this->getContainer()
                    ->get('sandbox_api.admin_status_log')
                    ->autoLog(
                        1,
                        Lease::LEASE_STATUS_MATURED,
                        $logMessage,
                        AdminStatusLog::OBJECT_LEASE,
                        $lease->getId()
                    );
            }
        }
        $em->flush();

        $output->writeln('Success!');
    }
}
