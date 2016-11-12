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
            $buildingsQuery->where('
                (rb.name LIKE :query OR
                rb.address LIKE :query)
            ')
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
     * @param array  $excludeIds
     *
     * @return array
     */
    public function findNearbyBuildings(
        $lat,
        $lng,
        $range,
        $excludeIds
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
                    AND rb.companyId NOT IN (:ids)
                    HAVING distance < :range
                    ORDER BY distance ASC
                '
            )
            ->setParameter('latitude', $lat)
            ->setParameter('longitude', $lng)
            ->setParameter('range', $range)
            ->setParameter('ids', $excludeIds)
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

    /**
     * @param $companyId
     *
     * @return array
     */
    public function getCompanyBuildings(
        $companyId
    ) {
        $query = $this->createQueryBuilder('b')
            ->where('b.isDeleted = FALSE')
            ->andWhere('b.status = :status')
            ->andWhere('b.companyId = :companyId')
            ->setParameter('status', 'accept')
            ->setParameter('companyId', $companyId);

        return $query->getQuery()->getResult();
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
            $buildingsQuery->where('
                (rb.name LIKE :query OR
                rb.address LIKE :query)
            ')
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
     * @param $status
     * @param $visible
     * @param $excludeIds
     *
     * @return array
     */
    public function getLocationRoomBuildings(
        $cityId = null,
        $myBuildingIds = null,
        $companyId = null,
        $status = null,
        $visible = null,
        $excludeIds = null
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
        if (!empty($myBuildingIds)) {
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

        // filter by building status
        if (!is_null($status)) {
            $buildingsQuery->andWhere('rb.status = :status');
            $buildingsQuery->setParameter('status', $status);
        }

        // filter by building visible
        if (!is_null($visible)) {
            $buildingsQuery->andWhere('rb.visible = :visible');
            $buildingsQuery->setParameter('visible', $visible);
        }

        if (!is_null($excludeIds) && !empty($excludeIds)) {
            $buildingsQuery->andWhere('rb.companyId NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }

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

    public function searchBuildings(
        $cityId,
        $queryText,
        $roomTypes,
        $sortBy,
        $buildingTags,
        $buildingServices,
        $lng,
        $lat,
        $excludeIds = null
    ) {
        $buildingsQuery = $this->createQueryBuilder('rb');

        // query by city id
        if (!is_null($cityId)) {
            $buildingsQuery->where('rb.cityId = :cityId');
            $buildingsQuery->setParameter('cityId', $cityId);
        }

        // filter by building delete
        $buildingsQuery->andWhere('rb.isDeleted = FALSE');

        // filter by building status
        $buildingsQuery->andWhere('rb.status = :status')
            ->setParameter('status', 'accept');

        // filter by building visible
        $buildingsQuery->andWhere('rb.visible = TRUE');

        // filter by room types
        if (!is_null($roomTypes) && !empty($roomTypes)) {
            $buildingsQuery
                ->leftJoin(
                    'SandboxApiBundle:Room\Room',
                    'r',
                    'WITH',
                    'rb.id = r.building'
                )
                ->leftJoin(
                    'SandboxApiBundle:Room\RoomTypes',
                    'rt',
                    'WITH',
                    'rt.name = r.type'
                )
                ->andWhere('rt.id IN (:spaceTypes)')
                ->setParameter('spaceTypes', $roomTypes);
        }

        // filter by building tags
        if (!is_null($buildingTags) && !empty($buildingTags)) {
            $buildingsQuery
                ->leftJoin(
                    'SandboxApiBundle:Room\RoomBuildingTagBinding',
                    'rbt',
                    'WITH',
                    'rb.id = rbt.building'
                )
                ->andWhere('rbt.tag IN (:buildingTags)')
                ->setParameter('buildingTags', $buildingTags);
        }

        // filter by building services
        if (!is_null($buildingServices) && !empty($buildingServices)) {
            $buildingsQuery
                ->leftJoin(
                    'SandboxApiBundle:Room\RoomBuildingServiceBinding',
                    'rbs',
                    'WITH',
                    'rb.id = rbs.building'
                )
                ->andWhere('rbs.service IN (:buildingServices)')
                ->setParameter('buildingServices', $buildingServices);
        }

        // filter by building name or room name
        if (!is_null($queryText) && !empty($queryText)) {
            $buildingsQuery
                ->leftJoin(
                    'SandboxApiBundle:Room\Room',
                    'r',
                    'WITH',
                    'rb.id = r.building'
                )
                ->andWhere('rb.name LIKE :query OR r.name LIKE :query')
                ->setParameter('query', '%'.$queryText.'%');
        }

        // 0 means user disable location
        if ($lat == 0 && $lng == 0) {
            $buildingsQuery->select('
                rb.id,
                rb.name,
                rb.evaluationStar as evaluation_star,
                rb.avatar,
                rb.lat,
                rb.lng,
                (rb.orderEvaluationNumber + rb.buildingEvaluationNumber) as total_evaluation_number
            ');

            $buildingsQuery->orderBy('rb.evaluationStar', 'DESC');
        } else {
            $buildingsQuery->select('
                rb.id,
                rb.name,
                (6371
                    * acos(cos(radians(:latitude)) * cos(radians(rb.lat))
                    * cos(radians(rb.lng) - radians(:longitude))
                    + sin(radians(:latitude)) * sin(radians(rb.lat)))
                ) as distance,
                rb.evaluationStar as evaluation_star,
                rb.avatar,
                rb.lat,
                rb.lng,
                (rb.orderEvaluationNumber + rb.buildingEvaluationNumber) as total_evaluation_number
            ')
            ->setParameter('latitude', $lat)
            ->setParameter('longitude', $lng);

            // sort by distance and evaluation
            switch ($sortBy) {
                case 'smart':
                    break;
                case 'star':
                    $buildingsQuery->orderBy('rb.evaluationStar', 'DESC');
                    break;
                default:
                    $buildingsQuery->orderBy('distance', 'ASC');
            }
        }

        // filter the companies we don't want to show
        if (!is_null($excludeIds) && !empty($excludeIds)) {
            $buildingsQuery->andWhere('rb.companyId NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }

        $buildingsQuery->groupBy('rb.id');

        return $buildingsQuery->getQuery()->getResult();
    }

    /**
     * @param $buildingIds
     * @param $lng
     * @param $lat
     * @param $limit
     * @param $offset
     * @param null $excludeIds
     *
     * @return array
     */
    public function getFavoriteBuildings(
        $buildingIds,
        $lng,
        $lat,
        $limit,
        $offset,
        $excludeIds = null
    ) {
        $buildingsQuery = $this->createQueryBuilder('rb');

        // filter by building delete
        $buildingsQuery->where('rb.isDeleted = FALSE');

        // filter by building status
        $buildingsQuery->andWhere('rb.status = :status')
            ->setParameter('status', 'accept');

        // filter by building visible
        $buildingsQuery->andWhere('rb.visible = TRUE');

        $buildingsQuery->select('
            rb.id,
            rb.name,
            (6371
                * acos(cos(radians(:latitude)) * cos(radians(rb.lat))
                * cos(radians(rb.lng) - radians(:longitude))
                + sin(radians(:latitude)) * sin(radians(rb.lat)))
            ) as distance,
            rb.evaluationStar as evaluation,
            rb.avatar,
            rb.lat,
            rb.lng,
            (rb.orderEvaluationNumber + rb.buildingEvaluationNumber) as total_comments_amount
        ')
            ->setParameter('latitude', $lat)
            ->setParameter('longitude', $lng);

        if (!is_null($buildingIds) && !empty($buildingIds)) {
            $buildingsQuery->andWhere('rb.id IN (:buildingIds)')
                ->setParameter('buildingIds', $buildingIds);
        }

        // filter the companies we don't want to show
        if (!is_null($excludeIds) && !empty($excludeIds)) {
            $buildingsQuery->andWhere('rb.companyId NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }

        $buildingsQuery->orderBy('distance', 'ASC')
            ->addOrderBy('rb.evaluationStar', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $buildingsQuery->getQuery()->getResult();
    }
}
