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
}
