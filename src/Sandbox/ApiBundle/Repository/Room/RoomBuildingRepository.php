<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;

class RoomBuildingRepository extends EntityRepository
{
    /**
     * Get list of room buildings.
     *
     * @param int    $cityId
     * @param string $query
     *
     * @return array
     */
    public function getRoomBuildings(
        $cityId,
        $query
    ) {
        $notFirst = false;
        $buildingsQuery = $this->createQueryBuilder('rb');

        // query by key words
        if (!is_null($query)) {
            $buildingsQuery->where('rb.name LIKE :query')
                ->andWhere('rb.address LIKE :query')
                ->setParameter('query', $query.'%');

            $notFirst = true;
        }

        // query by city id
        if (!is_null($cityId)) {
            if ($notFirst) {
                $buildingsQuery->andWhere('rb.cityId = :cityId');
            } else {
                $buildingsQuery->where('rb.cityId = :cityId');
            }
            $buildingsQuery->setParameter('cityId', $cityId);
        }

        // order by creation date
        $buildingsQuery->orderBy('rb.creationDate', 'DESC');

        return $buildingsQuery->getQuery()->getResult();
    }

    /**
     * @param string $lat
     * @param string $lng
     * @param int    $range
     *
     * @return array
     */
    public function findNearbyBuildings(
        $lat,
        $lng,
        $range
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT rb,
                  (
                    6371
                    * acos(cos(radians(:latitude)) * cos(radians(rb.lat))
                    * cos(radians(rb.lng) - radians(:longitude))
                    + sin(radians(:latitude)) * sin(radians(rb.lat)))
                    ) as HIDDEN distance
                    FROM SandboxApiBundle:Room\RoomBuilding rb
                    WHERE rb.status = :accept
                    AND rb.visible = TRUE
                    AND rb.isDeleted = FALSE
                    HAVING distance < :range
                    ORDER BY distance ASC
                '
            )
            ->setParameter('latitude', $lat)
            ->setParameter('longitude', $lng)
            ->setParameter('range', $range)
            ->setParameter('accept', RoomBuilding::STATUS_ACCEPT);

        return $query->getResult();
    }

    /**
     * @return array
     */
    public function getDistinctServers()
    {
        $query = $this->createQueryBuilder('rb')
            ->select('DISTINCT rb.server')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $companyId
     *
     * @return array
     */
    public function getBuildingsByCompany(
        $companyId
    ) {
        $query = $this->createQueryBuilder('b')
            ->select('b.id')
            ->where('b.companyId = :companyId')
            ->setParameter('companyId', $companyId);

        return $query->getQuery()->getArrayResult();
    }

    //-------------------- sales room repository --------------------//

    /**
     * Get list of room buildings.
     *
     * @param int    $cityId
     * @param string $query
     * @param array  $myBuildingIds
     *
     * @return array
     */
    public function getSalesRoomBuildings(
        $cityId,
        $query,
        $myBuildingIds = null
    ) {
        $notFirst = false;
        $buildingsQuery = $this->createQueryBuilder('rb');

        // query by key words
        if (!is_null($query)) {
            $buildingsQuery->where('rb.name LIKE :query')
                ->andWhere('rb.address LIKE :query')
                ->setParameter('query', $query.'%');

            $notFirst = true;
        }

        // query by city id
        if (!is_null($cityId)) {
            if ($notFirst) {
                $buildingsQuery->andWhere('rb.cityId = :cityId');
            } else {
                $buildingsQuery->where('rb.cityId = :cityId');
            }
            $buildingsQuery->setParameter('cityId', $cityId);
        }

        // filter by building id
        if (!is_null($myBuildingIds)) {
            $buildingsQuery->andWhere('rb.id IN (:ids)');
            $buildingsQuery->setParameter('ids', $myBuildingIds);
        }

        // filter by building delete
        $buildingsQuery->andWhere('rb.isDeleted = FALSE');

        // order by creation date
        $buildingsQuery->orderBy('rb.creationDate', 'DESC');

        return $buildingsQuery->getQuery()->getResult();
    }

    /**
     * @param $cityId
     * @param $myBuildingIds
     * @param $companyId
     *
     * @return array
     */
    public function getLocationRoomBuildings(
        $cityId = null,
        $myBuildingIds = null,
        $companyId = null
    ) {
        $notFirst = false;
        $buildingsQuery = $this->createQueryBuilder('rb');

        // query by city id
        if (!is_null($cityId)) {
            $buildingsQuery->where('rb.cityId = :cityId');
            $buildingsQuery->setParameter('cityId', $cityId);

            $notFirst = true;
        }

        // filter by building id
        if (!is_null($myBuildingIds)) {
            if ($notFirst) {
                $buildingsQuery->andWhere('rb.id IN (:ids)');
            } else {
                $buildingsQuery->where('rb.id IN (:ids)');
            }
            $buildingsQuery->setParameter('ids', $myBuildingIds);
        }

        // filter by company id
        if (!is_null($companyId)) {
            if ($notFirst) {
                $buildingsQuery->andWhere('rb.companyId = :companyId');
            } else {
                $buildingsQuery->where('rb.companyId = :companyId');
            }
            $buildingsQuery->setParameter('companyId', $companyId);
        }

        // filter by building delete
        $buildingsQuery->andWhere('rb.isDeleted = FALSE');

        // order by creation date
        $buildingsQuery->orderBy('rb.creationDate', 'DESC');

        return $buildingsQuery->getQuery()->getResult();
    }

    /**
     * @param $adminCompanyId
     *
     * @return array
     */
    public function countSalesBuildings(
        $adminCompanyId
    ) {
        $query = $this->createQueryBuilder('b')
            ->select('COUNT (b.id)')
            ->where('b.companyId = :companyId')
            ->andWhere('b.isDeleted = FALSE')
            ->setParameter('companyId', $adminCompanyId);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $myShopIds
     *
     * @return array
     */
    public function getLocationBuildingByShop(
        $myShopIds
    ) {
        $query = $this->createQueryBuilder('b')
            ->leftJoin('SandboxApiBundle:Shop\Shop', 's', 'WITH', 's.building = b.id')
            ->andWhere('s.id IN (:shopIds)')
            ->setParameter('shopIds', $myShopIds);

        // filter by building delete
        $query->andWhere('b.isDeleted = FALSE');
        $query->andWhere('b.visible = TRUE');
        $query->andWhere('b.status = :accept');
        $query->setParameter('accept', RoomBuilding::STATUS_ACCEPT);

        // order by creation date
        $query->orderBy('b.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }
}
