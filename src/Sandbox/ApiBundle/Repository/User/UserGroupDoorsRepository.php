<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserGroupDoorsRepository extends EntityRepository
{
    /**
     * @param $group
     *
     * @return array
     */
    public function getBuildingIdsByGroup(
        $group
    ) {
        $query = $this->createQueryBuilder('d')
            ->select('d.building')
            ->where('d.group = :group')
            ->setParameter('group', $group)
            ->groupBy('d.building');

        return $query->getQuery()->getResult();
    }
}
