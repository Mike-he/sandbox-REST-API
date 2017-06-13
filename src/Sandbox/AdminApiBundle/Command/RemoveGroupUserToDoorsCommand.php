<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Constants\DoorAccessConstants;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveGroupUserToDoorsCommand extends ContainerAwareCommand
{
    use DoorAccessTrait;

    protected function configure()
    {
        $this->setName('sandbox:api-bundle:remove_group_user_to_doors')
            ->setDescription('Check Group Users and remove user to doors');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $doorDepartmentUsers = $em->getRepository('SandboxApiBundle:Door\DoorDepartmentUsers')->findAll();
        foreach ($doorDepartmentUsers as $doorDepartmentUser) {
            $base = $doorDepartmentUser->getBuildingServer();
            $user = $doorDepartmentUser->getUserId();

            $userInfo = $em->getRepository('SandboxApiBundle:User\UserView')->find($user);
            $userName = $userInfo->getName();
            $cardNo = $userInfo->getCardNo();

            $this->setEmployeeCard(
                $base,
                $user,
                $userName,
                $cardNo,
                DoorAccessConstants::METHOD_DELETE
            );
        }

        $output->writeln('Finished !');
    }
}
