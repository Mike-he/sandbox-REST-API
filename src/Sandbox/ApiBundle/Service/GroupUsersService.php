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
     * @param $groupId
     * @param $user
     * @param $type
     * @param $start
     * @param $end
     * @param $orderNumber
     * @param $customerId
     */
    public function storeGroupUser(
        $em,
        $groupId,
        $user,
        $type,
        $start = null,
        $end = null,
        $orderNumber = null,
        $customerId = null
    ) {
        if (!$customerId) {
            $groupUsers = $this->container->get('doctrine')
                ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->findBy(
                    array(
                        'groupId' => $groupId,
                        'userId' => $user,
                        'type' => $type,
                        'orderNumber' => $orderNumber,
                    )
                );

            $group = $this->container->get('doctrine')
                ->getRepository('SandboxApiBundle:User\UserGroup')
                ->find($groupId);

            $customer = $this->container->get('doctrine')
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(array(
                    'companyId' => $group->getCompanyId(),
                    'userId' => $user,
                ));

            $customerId = $customer ? $customer->getUserId() : null;
        } else {
            $groupUsers = $this->container->get('doctrine')
                ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->findBy(
                    array(
                        'groupId' => $groupId,
                        'customerId' => $customerId,
                        'type' => $type,
                        'orderNumber' => $orderNumber,
                    )
                );
        }

        if (empty($groupUsers) || is_null($groupUsers)) {
            $groupUser = new UserGroupHasUser();
            $groupUser->setGroupId($groupId);
            $groupUser->setUserId($user);
            $groupUser->setType($type);
            $groupUser->setStartDate($start);
            $groupUser->setOrderNumber($orderNumber);
            $groupUser->setCustomerId($customerId);
            $groupUser->setEndDate($end);
            $em->persist($groupUser);
        } else {
            foreach ($groupUsers as $groupUser) {
                $groupUser->setEndDate($end);
                $em->persist($groupUser);
            }
        }
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
