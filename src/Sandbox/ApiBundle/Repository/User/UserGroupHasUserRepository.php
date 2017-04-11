<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserGroupHasUserRepository extends EntityRepository
{
    /**
     * @param $group
     *
     * @return array
     */
    public function countUserNumber(
        $group
    ) {
        $query = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.groupId = :group')
            ->setParameter('group', $group);

        return $query->getQuery()->getSingleScalarResult();
    }

    public function getGroupsByUser(
        $user,
        $type
    ) {
        $query = $this->createQueryBuilder('u')
            ->select('
                    u.groupId as id, 
                    ug.name as name
                ')
            ->leftJoin('SandboxApiBundle:User\UserGroup', 'ug', 'WITH', 'ug.id = u.groupId')
            ->where('u.userId = :user')
            ->andWhere('u.type in (:type)')
            ->setParameter('user', $user)
            ->setParameter('type', $type);

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
