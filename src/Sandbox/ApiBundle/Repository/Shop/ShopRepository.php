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
     * @param bool|true $active
     * @param bool|true $online
     * @param array     $shopIds
     *
     * @return array
     */
    public function getShopByBuilding(
        $buildingId,
        $active = false,
        $online = false,
        $shopIds = null
    ) {
        $notFirst = false;

        $query = $this->createQueryBuilder('s')
            ->orderBy('s.creationDate', 'ASC');

        if (!is_null($buildingId) && !empty($buildingId)) {
            $query = $query->where('s.buildingId = :buildingId')
                ->setParameter('buildingId', $buildingId);
            $notFirst = true;
        }

        // filter active shops
        if ($active) {
            $where = 's.active = :active';
            $this->addWhereQuery($query, $notFirst, $where);

            $query->setParameter('active', true);
            $notFirst = true;
        }

        // filter online shops
        if ($online) {
            $where = 's.online = :online';
            $this->addWhereQuery($query, $notFirst, $where);

            $query->setParameter('online', true);
            $notFirst = true;
        }

        // filter by shop ids
        if (!is_null($shopIds)) {
            $where = 's.id IN (:shopIds)';
            $this->addWhereQuery($query, $notFirst, $where);

            $query->setParameter('shopIds', $shopIds);
            $notFirst = true;
        }

        // filter by shop deleted
        $where = 's.isDeleted = FALSE';
        $this->addWhereQuery($query, $notFirst, $where);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $shopId
     * @param bool|true $active
     * @param bool|true $online
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
            ->andWhere('s.isDeleted = FALSE')
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

    /**
     * @param $buildingId
     *
     * @return array
     */
    public function getMyShopByBuilding(
        $buildingId
    ) {
        $query = $this->createQueryBuilder('s')
            ->select('s.id as shopId')
            ->where('s.buildingId = :buildingId')
            ->orderBy('s.creationDate', 'ASC')
            ->setParameter('buildingId', $buildingId);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $building
     *
     * @return mixed
     */
    public function countsShopByBuilding(
        $building
    ) {
        $query = $this->createQueryBuilder('s')
            ->select('COUNT(s)')
            ->where('s.building = :building')
            ->setParameter('building', $building);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param QueryBuilder $query
     * @param bool         $notFirst
     * @param string       $where
     */
    private function addWhereQuery(
        $query,
        $notFirst,
        $where
    ) {
        if ($notFirst) {
            $query->andWhere($where);
        } else {
            $query->where($where);
        }
    }

    /**
     * @param $building
     */
    public function setShopOffline(
        $building
    ) {
        $query = $this->createQueryBuilder('s')
            ->update()
            ->set('s.online', 'FALSE')
            ->set('s.close', 'TRUE')
            ->where('s.building = :building')
            ->setParameter('building', $building)
            ->getQuery();

        $query->execute();
    }

    /**
     * @param $building
     */
    public function setShopDeleted(
        $building
    ) {
        $query = $this->createQueryBuilder('s')
            ->update()
            ->set('s.online', 'FALSE')
            ->set('s.close', 'TRUE')
            ->set('s.isDeleted', 'TRUE')
            ->where('s.building = :building')
            ->setParameter('building', $building)
            ->getQuery();

        $query->execute();
    }

    /**
     * @param $companyId
     * 
     * @return array
     */
    public function getShopIdsByCompany(
        $companyId
    ) {
        $query = $this->createQueryBuilder('s')
            ->select('s.id')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = s.buildingId')
            ->where('b.companyId = :companyId')
            ->andWhere('s.isDeleted = FALSE')
            ->setParameter('companyId', $companyId);

        $ids = $query->getQuery()->getResult();
        $ids = array_map('current', $ids);

        return $ids;
    }
}
