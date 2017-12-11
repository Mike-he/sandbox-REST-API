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
        $buildingsQuery = $this->createQueryBuilder('rb')
            ->where('rb.isDeleted = FALSE');

        // query by city id
        if (!is_null($cityId)) {
            $buildingsQuery->andWhere('rb.cityId = :cityId')
                ->setParameter('cityId', $cityId);
        }

        // filter by building id
        if (!is_null($myBuildingIds)) {
            $buildingsQuery->andWhere('rb.id IN (:ids)')
                ->setParameter('ids', $myBuildingIds);
        }

        // filter by company id
        if (!is_null($companyId)) {
            $buildingsQuery->andWhere('rb.companyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        // filter by building status
        if (!is_null($status)) {
            $buildingsQuery->andWhere('rb.status = :status')
                ->setParameter('status', $status);
        }

        // filter by building visible
        if (!is_null($visible)) {
            $buildingsQuery->andWhere('rb.visible = :visible')
                ->setParameter('visible', $visible);
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

    /**
     * @param $cityId
     * @param $queryText
     * @param $roomTypes
     * @param $sortBy
     * @param $buildingTags
     * @param $buildingServices
     * @param $lng
     * @param $lat
     * @param null $excludeIds
     * @param $ids
     *
     * @return array
     */
    public function searchBuildings(
        $cityId,
        $queryText,
        $roomTypes,
        $sortBy,
        $buildingTags,
        $buildingServices,
        $lng,
        $lat,
        $excludeIds,
        $ids
    ) {
        $buildingsQuery = $this->createQueryBuilder('rb')
            ->leftJoin(
                'SandboxApiBundle:Room\Room',
                'r',
                'WITH',
                'rb.id = r.building'
            );

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
                    'SandboxApiBundle:Room\RoomTypes',
                    'rt',
                    'WITH',
                    'rt.name = r.type'
                )
                ->leftJoin(
                    'SandboxApiBundle:Product\Product',
                    'p',
                    'WITH',
                    'r.id = p.room'
                )
                ->andWhere('rt.id IN (:spaceTypes)')
                ->andWhere('p.isDeleted = FALSE')
                ->andWhere('p.visible = TRUE')
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
                ->andWhere('rb.name LIKE :query OR r.name LIKE :query')
                ->setParameter('query', '%'.$queryText.'%');
        }

        // 0 means user disable location
        if (0 == $lat && 0 == $lng) {
            $buildingsQuery->select('
                rb.id,
                rb.name,
                rb.evaluationStar as evaluation_star,
                rb.avatar,
                rb.districtId as district_id,
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
                rb.districtId as district_id,
                rb.lat,
                rb.lng,
                (rb.orderEvaluationNumber + rb.buildingEvaluationNumber) as total_evaluation_number
            ')
            ->setParameter('latitude', $lat)
            ->setParameter('longitude', $lng);

            // sort by distance and evaluation
            switch ($sortBy) {
                case 'smart':
                    $buildingsQuery->orderBy('evaluation_star', 'DESC')
                        ->addOrderBy('total_evaluation_number', 'DESC')
                        ->addOrderBy('distance', 'ASC');
                    break;
                case 'distance':
                    $buildingsQuery->orderBy('distance', 'ASC');
                    break;
                case 'star':
                    $buildingsQuery->orderBy('rb.evaluationStar', 'DESC');
                    break;
                default:
                    $buildingsQuery->orderBy('evaluation_star', 'DESC')
                        ->addOrderBy('total_evaluation_number', 'DESC')
                        ->addOrderBy('distance', 'ASC');
            }
        }

        // filter the companies we don't want to show
        if (!is_null($excludeIds) && !empty($excludeIds)) {
            $buildingsQuery->andWhere('rb.companyId NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }

        // filter by ids
        if (!is_null($ids)) {
            $buildingsQuery->andWhere('rb.id IN (:ids)')
                ->setParameter('ids', $ids);
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

    /**
     * @param $lat
     * @param $lng
     * @param $range
     * @param $excludeIds
     * @param $roomType
     * @param $buildingTags
     * @param $buildingServices
     * @param $propertyTypes
     *
     * @return array
     */
    public function findClientCommunities(
        $lat,
        $lng,
        $range,
        $excludeIds,
        $roomType,
        $buildingTags,
        $buildingServices,
        $propertyTypes,
        $cityId = null,
        $districtId = null
    ) {
        $query = $this->createQueryBuilder('rb')
            ->select('
                rb.id,
                (
                    6371
                    * acos(cos(radians(:latitude)) * cos(radians(rb.lat))
                    * cos(radians(rb.lng) - radians(:longitude))
                    + sin(radians(:latitude)) * sin(radians(rb.lat)))
                ) as HIDDEN distance
            ')
            ->where('rb.status = :accept')
            ->andWhere('rb.visible = TRUE')
            ->andWhere('rb.isDeleted = FALSE')
            ->andWhere('rb.companyId NOT IN (:ids)')
            ->orderBy('distance', 'ASC')
            ->setParameter('latitude', $lat)
            ->setParameter('longitude', $lng)
            ->setParameter('ids', $excludeIds)
            ->setParameter('accept', RoomBuilding::STATUS_ACCEPT);

        if (!is_null($range)) {
            $query->having('distance < :range')
                ->setParameter('range', $range);
        }

        // filter by room types
        if (!is_null($roomType) && !empty($roomType)) {
            $query
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
                ->leftJoin(
                    'SandboxApiBundle:Product\Product',
                    'p',
                    'WITH',
                    'r.id = p.room'
                )
                ->andWhere('rt.name = :spaceTypes')
                ->andWhere('p.isDeleted = FALSE')
                ->andWhere('p.visible = TRUE')
                ->setParameter('spaceTypes', $roomType);
        }

        // filter by building tags
        if (!is_null($buildingTags) && !empty($buildingTags)) {
            $query->leftJoin(
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
            $query->leftJoin(
                    'SandboxApiBundle:Room\RoomBuildingServiceBinding',
                    'rbs',
                    'WITH',
                    'rb.id = rbs.building'
                )
                ->andWhere('rbs.service IN (:buildingServices)')
                ->setParameter('buildingServices', $buildingServices);
        }

        // filter by property types
        if ($propertyTypes) {
            $query->andWhere('rb.propertyTypeId IN (:propertyTypeIds)')
                ->setParameter('propertyTypeIds', $propertyTypes);
        }

        if (!is_null($cityId) && !empty($cityId)) {
            $query->andWhere('rb.city = :cityId')
                ->setParameter('cityId', $cityId);
        }

        if (!is_null($districtId)) {
            $query->andWhere('rb.districtId = :districtId')
                ->setParameter('districtId', $districtId);
        }

        $ids = $query->getQuery()->getScalarResult();
        $ids = array_unique(array_map('current', $ids));

        return $ids;
    }

    public function getMinProductLeasingSetByBuilding(
        $buildingId
    ) {
        $query = $this->createQueryBuilder('rb')
            ->leftJoin(
                'SandboxApiBundle:Room\Room',
                'r',
                'WITH',
                'rb.id = r.building'
            )
            ->leftJoin(
                'SandboxApiBundle:Product\Product',
                'p',
                'WITH',
                'r.id = p.room'
            )
            ->leftJoin(
                'SandboxApiBundle:Product\ProductLeasingSet',
                's',
                'WITH',
                's.product = p.id'
            )
            ->select('p.id')
            ->where('r.buildingId = :building')
            ->andWhere('p.id IS NOT NULL')
            ->setParameter('building', $buildingId);

        $productIds = $query->getQuery()->getResult();
        $productIds = array_map('current', $productIds);

        $leasingSetQuery = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('min(s.basePrice) as min_base_price, min(s.unitPrice) as min_unit_price')
            ->from('SandboxApiBundle:Product\ProductLeasingSet', 's')
            ->where('s.product IN (:productIds)')
            ->setParameter('productIds', $productIds)
            ->setMaxResults(1);

        return $leasingSetQuery->getQuery()->getOneOrNullResult();
    }

    /**
     * Get list of room buildings.
     *
     * @param string $query
     * @param array  $myBuildingIds
     *
     * @return array
     */
    public function getMySalesBuildings(
        $query,
        $myBuildingIds
    ) {
        $buildingsQuery = $this->createQueryBuilder('rb')
            ->where('rb.name LIKE :query')
            ->setParameter('query', '%'.$query.'%');

        // filter by building id
        if (!is_null($myBuildingIds)) {
            $buildingsQuery->andWhere('rb.id IN (:ids)');
            $buildingsQuery->setParameter('ids', $myBuildingIds);
        }

        // filter by building delete
        $buildingsQuery->andWhere('rb.isDeleted = FALSE')
            ->andWhere('rb.visible = TRUE');

        // order by creation date
        $buildingsQuery->orderBy('rb.creationDate', 'DESC');

        return $buildingsQuery->getQuery()->getResult();
    }

    /**
     * @param $companyId
     * @param $buidingName
     *
     * @return array
     */
    public function getCompanyBuildingsByName(
        $companyId, $buildingName
    ) {
        $query = $this->createQueryBuilder('b')
            ->select('b.id')
            ->where('b.isDeleted = FALSE')
            ->andWhere('b.status = :status')
            ->andWhere('b.companyId = :companyId')
            ->andWhere('b.name =:name')
            ->setParameter('status', 'accept')
            ->setParameter('companyId', $companyId)
            ->setParameter('name', $buildingName);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $commnueStatus
     * @param $search
     *
     * @return array
     */
    public function getAllCommnueRoomBuildings(
        $commnueStatus,
        $search
    ) {
        $query = $this->createQueryBuilder('rb')
            ->leftJoin('SandboxApiBundle:Room\Room','r','WITH','rb.id = r.building')
            ->select(
                'rb.id',
                'rb.name',
                'rb.commnueStatus',
                'COUNT(r.id) as roomNumber'
            )
            ->where('rb.isDeleted = FALSE');

        if(!is_null($commnueStatus)){
            $query->andWhere('rb.commnueStatus = :commnueStatus')
                ->setParameter('commnueStatus',$commnueStatus);
        }else{
            $query->andWhere('rb.commnueStatus != :commnueStatus')
                ->setParameter('commnueStatus',RoomBuilding::FREEZON);
        }

        if(!is_null($search)){
            $query->andWhere('rb.name LIKE :search')
                ->setParameter('search','%'.$search.'%');
        }

        $query = $query->groupBy('rb.id');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $builingIds
     * @param $limit
     * @return array
     */
    public function getExtraHotCommnueClientBuilding(
        $builingIds,
        $limit
    ) {
        $query = $this->createQueryBuilder('rb')
            ->select('rb.id')
            ->where('rb.id NOT IN (:ids)')
            ->setParameter('ids', $builingIds)
            ->setMaxResults($limit);

        $ids = $query->getQuery()->getScalarResult();
        return array_unique(array_map('current', $ids));
    }

    /*
     * @param $commnueStatus
     * @return mixed
     */
    public function getCommueDiffStatusCounts(
        $commnueStatus
    ) {
        $query = $this->createQueryBuilder('rb')
            ->select(
                'COUNT(rb.id) as counts'
            )
            ->where('rb.commnueStatus = :commnueStatus')
            ->setParameter('commnueStatus',$commnueStatus);

        return $query->getQuery()->getSingleScalarResult();
     }

    /**
     * @param $userId
     * @param $lat
     * @param $lng
     * @param null $buildingIds
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function getCommnueClientCommunityBuilding(
        $userId,
        $lat,
        $lng,
        $buildingIds = null,
        $limit = null,
        $offset = null
    ) {
        $query = $this->createQueryBuilder('rb')
            ->leftJoin('SandboxApiBundle:Room\Room','r','WITH','r.buildingId = rb.id')
                      ->select('
                        rb.id,
                        rb.name,
                        COUNT(r.id) as room_number,
                        (6371
                            * acos(cos(radians(:latitude)) * cos(radians(rb.lat))
                            * cos(radians(rb.lng) - radians(:longitude))
                            + sin(radians(:latitude)) * sin(radians(rb.lat)))
                        ) as distance,
                        rb.evaluationStar,
                        rb.avatar,
                        rb.address,
                        rb.lat,
                        rb.lng,
                        (rb.orderEvaluationNumber + rb.buildingEvaluationNumber) as total_comments_amount
                    ')
                        ->where('rb.commnueStatus != :commnuestatus')
                        ->setParameter('commnuestatus', RoomBuilding::FREEZON)
                        ->setParameter('latitude', $lat)
                        ->setParameter('longitude', $lng)
                        ->groupBy('rb.id');

        if(!is_null($buildingIds)){
            $query->andWhere('rb.id IN (:ids)')
                ->setParameter('ids',$buildingIds);
        }

        if(is_null($userId)){
            $query->orderBy('distance','ASC');
        }else{
            $query->orderBy('room_number','DESC');
        }

        if(!is_null($limit) && !is_null($offset)){
            $query->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $query->getQuery()->getResult();
    }
}
