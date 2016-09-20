<?php

namespace Sandbox\ApiBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;

class AdminPositionUserBindingRepository extends EntityRepository
{

    public function getBindUser(
        $positions,
        $building = null,
        $shop = null,
        $search
    ) {
        $query = $this->createQueryBuilder('p')
            ->where('p.positionId in (:positions)')
            ->setParameter('positions', $positions);

        if (!is_null($building) && !empty($building)) {
            $query->andWhere('p.buildingId = :building')
                ->setParameter('building', $building);
        }

        if (!is_null($shop) && !empty($shop)) {
            $query->andWhere('p.shopId = :shop')
                ->setParameter('shop', $shop);
        }

        if (!is_null($search) ) {

        }

        $query->groupBy('p.userId');

        return $query->getQuery()->getResult();
    }

    public function getAdminList(
        $user,
        $positions
    ) {
        $query = $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.positionId in (:positions)')
            ->setParameter('user', $user)
            ->setParameter('positions', $positions);

        return $query->getQuery()->getResult();
    }
}
