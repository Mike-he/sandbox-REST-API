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
        $groupUser = new UserGroupHasUser();
        $groupUser->setGroupId($group);
        $groupUser->setUserId($user);
        $groupUser->setType($type);
        $groupUser->setStartDate($start);
        $groupUser->setEndDate($end);
        $groupUser->setOrderNumber($orderNumber);

        $em->persist($groupUser);
    }
}
