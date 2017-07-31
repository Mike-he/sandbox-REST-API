<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sandbox\ApiBundle\Traits\SendNotification;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckLeaseExpireInCommand extends ContainerAwareCommand
{
    use SendNotification;

    protected function configure()
    {
        $this->setName('sandbox:api-bundle:check_lease_expire_in')
            ->setDescription('Check Lease Expire In Status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $em = $this->getContainer()->get('doctrine')->getManager();
//
//        $leases = $em->getRepository('SandboxApiBundle:Lease\Lease')
//            ->findBy(
//                array(
//                    'status' => Lease::LEASE_STATUS_CONFIRMING,
//                )
//            );
//
//        $expireInParameter = $em->getRepository('SandboxApiBundle:Parameter\Parameter')
//            ->findOneBy(array(
//                'key' => Parameter::KEY_LEASE_CONFIRM_EXPIRE_IN,
//            ));
//
//        foreach ($leases as $lease) {
//            $modificationDate = $lease->getModificationDate();
//            $leaseExpireInDate = $modificationDate->add(new \DateInterval('P'.$expireInParameter->getValue()));
//
//            $now = new \DateTime('now');
//
//            if ($now > $leaseExpireInDate) {
//                $lease->setStatus(Lease::LEASE_STATUS_EXPIRED);
//
//                $leaseId = $lease->getId();
//                $urlParam = 'ptype=leasesDetail&leasesId='.$leaseId;
//                $contentArray = $this->generateLeaseContentArray($urlParam);
//
//                // send Jpush notification
//                $this->generateJpushNotification(
//                    [
//                        $lease->getSupervisorId(),
//                    ],
//                    LeaseConstants::LEASE_EXPIRED_MESSAGE,
//                    null,
//                    $contentArray
//                );
//            }
//        }
//        $em->flush();

        $output->writeln('Success!');
    }
}
