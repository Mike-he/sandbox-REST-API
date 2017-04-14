<?php

namespace Sandbox\ApiBundle\Service;

use Sandbox\ApiBundle\Entity\User\UserGroupHasUser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GroupUsersService.
 */
class GroupUsersService
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $em
     * @param $group
     * @param $user
     * @param $type
     * @param $start
     * @param $end
     * @param $orderNumber
     */
    public function storeGroupUser(
        $em,
        $group,
        $user,
        $type,
        $start = null,
        $end = null,
        $orderNumber = null
    ) {
        $groupUser = $this->container->get('doctrine')
            ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
            ->findOneBy(
                array(
                    'groupId' => $group,
                    'userId' => $user,
                    'type' => $type,
                    'orderNumber' => $orderNumber,
                )
            );

        if (is_null($groupUser)) {
            $groupUser = new UserGroupHasUser();
            $groupUser->setGroupId($group);
            $groupUser->setUserId($user);
            $groupUser->setType($type);
            $groupUser->setStartDate($start);
            $groupUser->setOrderNumber($orderNumber);
        }

        $groupUser->setEndDate($end);

        $em->persist($groupUser);
    }

    /**
     * @param $em
     * @param $group
     * @param $user
     * @param $type
     * @param $orderNumber
     */
    public function removeGroupUser(
        $em,
        $group,
        $user,
        $type,
        $orderNumber
    ) {
        $groupUser = $this->container->get('doctrine')
            ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
            ->findOneBy(
                array(
                    'groupId' => $group,
                    'userId' => $user,
                    'type' => $type,
                    'orderNumber' => $orderNumber,
                )
            );

        if ($groupUser) {
            $em->remove($groupUser);
        }

        $this->removeDoorAccess($group, $user);
    }

    /**
     * @param $group
     * @param $user
     */
    public function removeDoorAccess(
        $group,
        $user
    ) {
        $groupUser = $this->container->get('doctrine')
            ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
            ->findOneBy(
                array(
                    'group' => $group,
                    'user' => $user,
                )
            );

        if (!$groupUser) {
            //todo: remove door access
        }
    }
}
