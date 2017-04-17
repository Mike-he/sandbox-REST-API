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

                $base = $building->getServer();

                if ($base) {
                    $this->repealRoomOrder(
                        $building->getServer(),
                        $membership->getAccessNo()
                    );
                }
            }

            $em->remove($membership);
        }
        $em->flush();

        //Second step: remove Door Access
        $groups = $em->getRepository('SandboxApiBundle:User\UserGroup')
            ->findBy(array('type' => UserGroup::TYPE_CARD));

        $now = new \DateTime('now');

        $removeData = array();
        foreach ($groups as $group) {
            $groupId = $group->getId();

            $groupUsers = $em->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->findFinishedUsers($groupId, $now);

            $removeUsers = array();
            foreach ($groupUsers as $groupUser) {
                if ($groupUser->getUserId() == 1) {
                    continue;
                }
                $removeUsers[] = $groupUser->getUserId();

                $em->remove($groupUser);
            }

            $removeData[] = array(
                'group_id' => $groupId,
                'users' => $removeUsers,
            );
        }

        $em->flush();

        foreach ($removeData as $data) {
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
                        // set action of door access to delete
                        $this->setAccessActionToDelete(
                            $accessNo,
                            $user,
                            DoorAccessConstants::METHOD_DELETE
                        );

                        $empUser = ['empid' => $user];
                        array_push($userArray, $empUser);
                    }
                    $em->flush();
                }

                if (!empty($userArray)) {
                    $this->deleteEmployeeToOrder(
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
