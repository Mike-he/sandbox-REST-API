<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Entity\User\UserGroup;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddGroupUserToDoorsCommand extends ContainerAwareCommand
{
    use DoorAccessTrait;

    protected function configure()
    {
        $this->setName('sandbox:api-bundle:add_group_user_to_doors')
            ->setDescription('Check Group Users');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $groups = $em->getRepository('SandboxApiBundle:User\UserGroup')
            ->findBy(array('type' => UserGroup::TYPE_CARD));

        $now = new \DateTime('now');

        $addData = array();
        foreach ($groups as $group) {
            $groupId = $group->getId();

            $addUsers = $em->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->findValidUsers(
                    $groupId,
                    $now
                );

            $addData[] = array(
                'group_id' => $groupId,
                'users' => $addUsers,
            );
        }

        foreach ($addData as $data) {
            $groupId = $data['group_id'];
            $users = $data['users'];

            $group = $em->getRepository('SandboxApiBundle:User\UserGroup')->find($groupId);
            $card = $em->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')->find($group->getCard());
            $accessNo = $card->getAccessNo();

            $buildingIds = $em->getRepository('SandboxApiBundle:User\UserGroupDoors')
                ->getBuildingIdsByGroup(
                    $groupId
                );

            foreach ($buildingIds as $buildingId) {
                $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->find($buildingId);

                $base = $building->getServer();

                $userArray = array();
                if ($base) {
                    foreach ($users as $user) {
                        $this->storeDoorAccess(
                            $em,
                            $accessNo,
                            $user,
                            $buildingId,
                            null,
                            $now,
                            $now
                        );

                        $empUser = ['empid' => $user];
                        array_push($userArray, $empUser);
                    }
                    $em->flush();
                }

                if (!empty($userArray)) {
                    $this->addEmployeeToOrder(
                        $base,
                        $accessNo,
                        $userArray
                    );
                }
            }
        }

        $output->writeln('Finished !');
    }
}
