<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializePositionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sandbox:api-bundle:initialize:position')
            ->setDescription('initialize position');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $salesCompanies = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->findAll();

        foreach ($salesCompanies as $salesCompany) {
            $position = $em->getRepository('SandboxApiBundle:Admin\AdminPosition')
                ->findOneBy(array(
                    'platform' => 'sales',
                    'salesCompanyId' => $salesCompany->getId(),
                    'isSuperAdmin' => true,
                ));

            if (!$position) {
                $now = new \DateTime('now');
                $icon = $em->getRepository('SandboxApiBundle:Admin\AdminPositionIcons')->find(1);
                $position = new AdminPosition();
                $position->setName('超级管理员');
                $position->setPlatform(AdminPosition::PLATFORM_SALES);
                $position->setIsSuperAdmin(true);
                $position->setIcon($icon);
                $position->setSalesCompany($salesCompany);
                $position->setCreationDate($now);
                $position->setModificationDate($now);
                $em->persist($position);
            }
        }
        $em->flush();

        $output->writeln('Initial Success!');
    }
}
