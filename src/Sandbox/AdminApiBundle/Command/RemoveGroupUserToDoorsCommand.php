<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Constants\DoorAccessConstants;
use Sandbox\ApiBundle\Entity\User\UserGroup;
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

        $groups = $em->getRepository('SandboxApiBundle:User\UserGroup')
            ->findBy(array('type' => UserGroup::TYPE_CARD));

        $now = new \DateTime('now');

        $removeUsers = array();
        foreach ($groups as $group) {
            $groupId = $group->getId();
            $groupUsers = $em->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->findFinishedUsers($groupId, $now);
            foreach ($groupUsers as $groupUser) {
                $removeUsers[] = $groupUser->getUserId();
                $em->remove($groupUser);
            }
        }

        foreach ($removeUsers as $user) {
            $doorDepartmentUsers = $em->getRepository('SandboxApiBundle:Door\DoorDepartmentUsers')
                ->findBy(array('userId' => $user));

            if ($doorDepartmentUsers) {
                $userInfo = $em->getRepository('SandboxApiBundle:User\UserView')->find($user);
                $userName = $userInfo->getName();
                $cardNo = $userInfo->getCardNo();

                foreach ($doorDepartmentUsers as $doorDepartmentUser) {
                    $base = $doorDepartmentUser->getBuildingServer();

                    $this->setMembershipEmployeeCard(
                        $base,
                        $user,
                        $userName,
                        $cardNo,
                        DoorAccessConstants::METHOD_DELETE
                    );
                }
            }
        }

        $output->writeln('Finished !');
    }
}
