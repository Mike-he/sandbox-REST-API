<?php

namespace Sandbox\ApiBundle\Repository\Shop;

use Doctrine\ORM\EntityRepository;

/**
 * ShopRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ShopRepository extends EntityRepository
{
    /**
     * @param $buildingId
     * @param bool|true $allowed
     *
     * @return array
     */
    public function getShopByBuilding(
        $buildingId,
        $active = false,
        $online = false
    ) {
        $query = $this->createQueryBuilder('s')
            ->where('s.buildingId = :buildingId')
            ->orderBy('s.creationDate', 'ASC')
            ->setParameter('buildingId', $buildingId);

        // filter active shops
        if ($active) {
            $query = $query->andWhere('s.active = :active')
                ->setParameter('active', true);
        }

        // filter online shops
        if ($online) {
            $query = $query->andWhere('s.online = :online')
                ->setParameter('online', true);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $shopId
     * @param bool|true $allowed
     *
     * @return array
     */
    public function getShopById(
        $shopId,
        $active = false,
        $online = false
    ) {
        $query = $this->createQueryBuilder('s')
            ->where('s.id = :shopId')
            ->setParameter('shopId', $shopId);

        // check if only can see shops currently open
        if ($active) {
            $query = $query->andWhere('s.active = :active')
                ->setParameter('active', true);
        }

        // filter online shops
        if ($online) {
            $query = $query->andWhere('s.online = :online')
                ->setParameter('online', true);
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $buildingId
     * @param bool|true $allowed
     *
     * @return array
     */
    public function getShopByBuildingForPage(
        $buildingId,
        $allowed = true
    ) {
        $query = $this->createQueryBuilder('s')
            ->select(
                's.online',
                's.close',
                's.active',
                's.description',
                's.id',
                's.name',
                's.startHour',
                's.endHour'
            )
            ->where('s.buildingId = :buildingId')
            ->orderBy('s.creationDate', 'ASC')
            ->setParameter('buildingId', $buildingId);

        // check if only can see shops currently active
        if (!$allowed) {
            $query = $query->andWhere('s.active = :active')
                ->setParameter('active', true);
        }

        return $query->getQuery()->getResult();
    }
}
