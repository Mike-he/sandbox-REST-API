<?php

namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;

class SalesUserRepository extends EntityRepository
{
    /**
     * @param $buildingIds
     *
     * @return array
     */
    public function getSalesUsers(
        $buildingIds
    ) {
        $query = $this->createQueryBuilder('su')
            ->select('su.userId')
            ->where('su.buildingId IN (:buildingIds)')
            ->setParameter('buildingIds', $buildingIds);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $shopIds
     *
     * @return array
     */
    public function getShopUser(
        $userId,
        $shopIds
    ) {
        $query = $this->createQueryBuilder('su')
            ->where('su.shopId IN (:shopIds)')
            ->andWhere('su.userId = :userId')
            ->setParameter('userId', $userId)
            ->setParameter('shopIds', $shopIds);

        return $query->getQuery()->getResult();
    }

    public function getDistinctUsers()
    {
        $query = $this->createQueryBuilder('su')
            ->select('
                DISTINCT su.userId, su.companyId
            ');

        return $query->getQuery()->getResult();
    }
}
