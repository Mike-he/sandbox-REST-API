<?php

namespace Sandbox\ApiBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;

class AdminPositionUserBindingRepository extends EntityRepository
{
    /**
     * @param $userId
     * @param $positionIds
     *
     * @return array
     */
    public function getPositionBindings(
        $userId,
        $positionIds
    ) {
        $query = $this->createQueryBuilder('pb')
            ->where('pb.userId = :userId')
            ->andWhere('pb.positionId IN (:positionIds)')
            ->setParameter('userId', $userId)
            ->setParameter('positionIds', $positionIds);

        return $query->getQuery()->getResult();
    }
}
