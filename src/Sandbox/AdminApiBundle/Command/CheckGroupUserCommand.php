<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Constants\DoorAccessConstants;
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
        $this->setName('sandbox:api-bundle:group_user_check')
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
            $removeUserId = $removeUser['user'];
            $groupId = $removeUser['group'];
            $groupUser = $em->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->checkUsingOrder(
                    $groupId,
                    $removeUserId,
                    $now
                );

            if ($groupUser) {
                continue;
            }

            $group = $em->getRepository('SandboxApiBundle:User\UserGroup')->find($groupId);
            $card = $em->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')->find($group->getCard());
            $accessNo = $card->getAccessNo();

            $buildingIds = $em->getRepository('SandboxApiBundle:User\UserGroupDoors')
                ->getBuildingIdsByGroup(
                    $removeUser['group']
                );

            foreach ($buildingIds as $buildingId) {
                $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->find($buildingId);

                $base = $building->getServer();

                if ($base) {
                    // set action of door access to delete
                    $this->setAccessActionToDelete(
                        $accessNo,
                        $removeUserId,
                        DoorAccessConstants::METHOD_DELETE
                    );

                    $em->flush();

                    $this->deleteEmployeeToOrder(
                        $base,
                        $accessNo,
                        array(
                            array(
                                'empid' => "$removeUserId",
                            ),
                        )
                    );
                }
            }
        }

        //Third step: addEmployeeToOrder
        foreach ($addUsers as $addUser) {
            $userId = $addUser->getId();
            $groupId = $addUser->getGroupId();

            $groupUser = $em->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->checkUsingOrder(
                    $userId,
                    $now
                );

            if ($groupUser) {
                continue;
            }

            $group = $em->getRepository('SandboxApiBundle:User\UserGroup')->find($groupId);
            $accessNo = $group->getCard()->getAccessNo();

            $buildingIds = $em->getRepository('SandboxApiBundle:User\UserGroupDoors')
                ->getBuildingIdsByGroup(
                    $groupId
                );

            foreach ($buildingIds as $buildingId) {
                $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->find($buildingId);

                $base = $building->getServer();
                if ($base) {
                    $this->storeDoorAccess(
                        $em,
                        $accessNo,
                        $userId,
                        $buildingId,
                        null,
                        $addUser->getStartDate(),
                        $addUser->getEndDate()
                    );

                    $em->flush();

                    $this->addEmployeeToOrder(
                        $base,
                        $accessNo,
                        array(
                            array(
                                'empid' => "$userId",
                            ),
                        )
                    );
                }
            }
        }

        $output->writeln('Finished !');
    }
}
