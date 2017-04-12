<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Entity\User\UserGroup;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckGroupUserCommand extends ContainerAwareCommand
{
    use DoorAccessTrait;

    protected function configure()
    {
        $this->setName('group-user:check')
            ->setDescription('Check Group Users');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        //First step: remove old access no
        $memberships = $em->getRepository('SandboxApiBundle:MembershipCard\MembershipCardAccessNo')->findAll();

        foreach ($memberships as $membership) {
            $buildingIds = $em->getRepository('SandboxApiBundle:User\UserGroupDoors')
                ->getBuildingIdsByGroup(
                    null,
                    $membership->getCard()
                );

            foreach ($buildingIds as $buildingId) {
                $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->find($buildingId);

                if ($building->getServer()) {
                    $this->repealRoomOrder(
                        $building->getServer(),
                        $membership->getAccessNo()
                    );
                }
            }


            $em->remove($membership);
        }
        $em->flush();


        /*
        //Second step: remove finished group users

        $groups = $em->getRepository('SandboxApiBundle:User\UserGroup')
            ->findBy(array('type' => UserGroup::TYPE_CARD));

        $now = new \DateTime('now');

        $today = new \DateTime('now');
        $today = $today->setTime(0, 0, 0);

        foreach ($groups as $group) {
            $groupId = $group->getId();

            $groupUsers = $em->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->findFinishedUsers($groupId, $now);

            $removeUsers = array();
            foreach ($groupUsers as $groupUser) {
                $removeUsers[] = array(
                    'group' => $groupId,
                    'user' => $groupUser->getUserId(),
                );

                $em->remove($groupUser);
            }

            $addUsers = $em->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->findStartUsers($groupId, $today);
        }

        $em->flush();

        foreach ($removeUsers as $removeUser) {
            $groupUser = $em->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->checkUsingOrder(
                    $removeUser['group'],
                    $removeUser['user'],
                    $now
                );

            if (!$groupUser) {
                $buildingIds = $em->getRepository('SandboxApiBundle:User\UserGroupDoors')
                    ->getBuildingIdsByGroup(
                        $removeUser['group']
                    );

                $groupDoors = $em->getRepository('SandboxApiBundle:User\UserGroupDoors')
                    ->findOneBy(array('building' => $buildingId));

                foreach ($buildingIds as $buildingId) {
                    $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')
                        ->find($buildingId);

                    if ($building->getServer()) {
                        //todo: deleteEmployeeToOrder
                    }
                }
            }
        }

        //Third step: addEmployeeToOrder
        foreach ($addUsers as $addUser) {
            $groupUser = $em->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->checkUsingOrder(
                     $addUser->getId(),
                    $now
                );

            if (!$groupUser) {
                $buildingIds = $em->getRepository('SandboxApiBundle:User\UserGroupDoors')
                    ->getBuildingIdsByGroup(
                        $addUser->getGroupId()
                    );

                foreach ($buildingIds as $buildingId) {
                    $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')
                        ->find($buildingId);

                    if ($building->getServer()) {
                        //todo: addEmployeeToOrder
                    }
                }
            }
        }
        */
    }
}
