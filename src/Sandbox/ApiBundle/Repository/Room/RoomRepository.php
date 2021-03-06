<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Sandbox\ApiBundle\Entity\Room\RoomFloor;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RoomRepository extends EntityRepository
{
    const ROOM_STATUS_USE = 'use';

    /**
     * Get list of orders for admin.
     *
     * @param string       $type
     * @param RoomCity     $city
     * @param RoomBuilding $building
     * @param RoomFloor    $floor
     * @param string       $status
     * @param string       $sortBy
     * @param string       $direction
     * @param string       $search
     *
     * @return array
     */
    public function getRooms(
        $type,
        $city,
        $building,
        $floor,
        $status,
        $sortBy,
        $direction,
        $search
    ) {
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('r')
            ->join('SandboxApiBundle:Room\RoomFloor', 'rf', 'WITH', 'rf.id = r.floor');

        // filter by isDeleted
        $query->where('r.isDeleted = FALSE');

        // filter by type
        if (!is_null($type) && !empty($type)) {
            $query->andwhere('r.type = :type');
            $parameters['type'] = $type;
            $notFirst = true;
        }

        // filter by order status
        if (!is_null($status)) {
            if (self::ROOM_STATUS_USE == $status) {
                $where = '
                    (
                        r.orderStartDate <= :now
                        AND
                        r.orderEndDate > :now
                    )
                ';
            } else {
                $where = '
                    r.id NOT IN (
                        SELECT rv.id FROM SandboxApiBundle:Room\RoomView rv
                        WHERE
                        rv.orderStartDate <= :now
                        AND
                        rv.orderEndDate > :now
                    )
                ';
            }
            $query->andWhere($where);
            $now = new \DateTime();
            $parameters['now'] = $now;
            $notFirst = true;
        }

        // filter by city
        if (!is_null($city)) {
            $where = 'r.city = :city';
            $query->andWhere($where);
            $parameters['city'] = $city;
            $notFirst = true;
        }

        // filter by building
        if (!is_null($building)) {
            $where = 'r.building = :building';
            $query->andWhere($where);
            $parameters['building'] = $building;
            $notFirst = true;
        }

        // filter by floor
        if (!is_null($floor)) {
            $where = 'r.floor = :floor';
            $query->andWhere($where);
            $parameters['floor'] = $floor;
            $notFirst = true;
        }

        // search by
        if (!is_null($search)) {
            $where = 'r.name LIKE :search or r.number LIKE :search';
            $query->andWhere($where);
            $parameters['search'] = "%$search%";
            $notFirst = true;
        }

        // sort method
        switch ($sortBy) {
            case 'floor':
                $query->orderBy('rf.floorNumber', $direction);
                break;
            default:
                $query->orderBy('r.'.$sortBy, $direction);
                break;
        }

        // set all parameters
        if ($notFirst) {
            $query->setParameters($parameters);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Seek all users that rented one room.
     *
     * @param $roomId
     *
     * @return array
     */
    public function getRoomUsersUsage(
        $roomId
    ) {
        $query = $this->createQueryBuilder('r')
            ->select('
            	r.id,
            	up.userId as user_id,
                up.name as renter_name,
                o.startDate as start_date,
                o.endDate as end_date
            ')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Order\ProductOrder', 'o', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'o.userId = up.userId')

            ->where('up.name IS NOT NULL')
            ->andWhere('r.id = :roomId')
            ->andWhere('r.isDeleted = FALSE')
            ->andWhere('o.rejected = FALSE')
            ->setParameter('roomId', $roomId);

        return $query->getQuery()->getResult();
    }

    /**
     * check room usage status.
     *
     * @param $roomId
     *
     * @return array
     */
    public function getRoomUsageStatus(
        $roomId
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('r')
            ->select('
                o.userId,
                v.name,
                v.email,
                v.phone,
                o.startDate,
                o.endDate
            ')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Order\ProductOrder', 'o', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:User\UserView', 'v', 'WITH', 'o.userId = v.id')
            ->where('o.startDate <= :now AND o.endDate >= :now')
            ->andWhere('r.id = :roomId')
            ->andWhere('o.status = :status')
            ->setParameter('now', $now)
            ->setParameter('roomId', $roomId)
            ->setParameter('status', ProductOrder::STATUS_COMPLETED);

        return $query->getQuery()->getResult();
    }

    /**
     * @param RoomFloor $floor
     * @param string    $type
     * @param array     $myBuildingIds
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getNotProductedRooms(
        $floor,
        $type,
        $myBuildingIds
    ) {
        if (is_null($type)) {
            throw new BadRequestHttpException();
        }

        if ('fixed' != $type) {
            $query = $this->createQueryBuilder('r')
                ->where('r.floor = :floor')
                ->andWhere('
                    r.id NOT IN (
                        SELECT p.roomId
                        FROM SandboxApiBundle:Product\Product p
                        WHERE p.roomId = r.id
                        AND p.visible = true
                    )
                ')
                ->setParameter('floor', $floor);
        } else {
            $query = $this->createQueryBuilder('r')
                ->where('r.floor = :floor')
                ->andWhere('
                    r.id IN (
                        SELECT f.roomId
                        FROM SandboxApiBundle:Room\RoomFixed f
                        WHERE f.roomId = r.id
                        AND f.available = true
                    )
                ')
                ->setParameter('floor', $floor);
        }
        $query = $query->andWhere('r.type = :type')
            ->andWhere('r.isDeleted = FALSE')
            ->setParameter('type', $type);

        // filter by my buildings
        $query->andWhere('r.buildingId IN (:buildingIds)');
        $query->setParameter('buildingIds', $myBuildingIds);

        $query = $query->orderBy('r.id', 'ASC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param array        $types
     * @param RoomCity     $city
     * @param RoomBuilding $building
     * @param string       $sortBy
     * @param string       $direction
     *
     * @return array
     */
    public function getProductedRooms(
        $types,
        $city,
        $building,
        $sortBy,
        $direction
    ) {
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('r')
            ->Where('
                r.id IN (
                    SELECT p.roomId
                    FROM SandboxApiBundle:Product\Product p
                    WHERE p.roomId = r.id
                    AND p.visible = true
                )
            ');

        // filter by types
        if (!is_null($types) && !empty($types)) {
            $query->andwhere('r.type IN (:types)');
            $parameters['types'] = $types;
            $notFirst = true;
        }

        // filter by city
        if (!is_null($city)) {
            $where = 'r.city = :city';
            $query->andWhere($where);
            $parameters['city'] = $city;
            $notFirst = true;
        }

        // filter by building
        if (!is_null($building)) {
            $where = 'r.building = :building';
            $query->andWhere($where);
            $parameters['building'] = $building;
            $notFirst = true;
        }

        //sort method
        if (!is_null($sortBy)) {
            $query->orderBy('r.'.$sortBy, $direction);
        }

        //set all parameters
        if ($notFirst) {
            $query->setParameters($parameters);
        }

        return $query->getQuery()->getResult();
    }

    //-------------------- sales room repository --------------------//

    /**
     * Get list of orders for admin.
     *
     * @param string       $type
     * @param RoomCity     $city
     * @param RoomBuilding $building
     * @param RoomFloor    $floor
     * @param string       $status
     * @param string       $sortBy
     * @param string       $direction
     * @param string       $search
     * @param array        $myBuildingIds
     *
     * @return array
     */
    public function getSalesRooms(
        $type,
        $city,
        $building,
        $floor,
        $status,
        $sortBy,
        $direction,
        $search,
        $myBuildingIds
    ) {
        $parameters = [];

        $query = $this->createQueryBuilder('r')
            ->join('SandboxApiBundle:Room\RoomFloor', 'rf', 'WITH', 'rf.id = r.floor');

        // filter by isDeleted
        $query->where('r.isDeleted = FALSE');

        // filter by type
        if (!is_null($type) && !empty($type)) {
            $query->andwhere('r.type = :type');
            $parameters['type'] = $type;
        }

        // filter by order status
        if (!is_null($status)) {
            if (self::ROOM_STATUS_USE == $status) {
                $where = '
                    (
                        r.orderStartDate <= :now
                        AND
                        r.orderEndDate > :now
                    )
                ';
            } else {
                $where = '
                    r.id NOT IN (
                        SELECT rv.id FROM SandboxApiBundle:Room\RoomView rv
                        WHERE
                        rv.orderStartDate <= :now
                        AND
                        rv.orderEndDate > :now
                    )
                ';
            }
            $query->andWhere($where);
            $now = new \DateTime();
            $parameters['now'] = $now;
        }

        // filter by city
        if (!is_null($city)) {
            $where = 'r.city = :city';
            $query->andWhere($where);
            $parameters['city'] = $city;
        }

        // filter by building
        if (!is_null($building)) {
            $where = 'r.building = :building';
            $query->andWhere($where);
            $parameters['building'] = $building;
        } else {
            // filter by my sales buildings
            $query->andWhere('r.building IN (:buildingIds)');
            $parameters['buildingIds'] = $myBuildingIds;
        }

        // filter by floor
        if (!is_null($floor)) {
            $where = 'r.floor = :floor';
            $query->andWhere($where);
            $parameters['floor'] = $floor;
        }

        // search by
        if (!is_null($search)) {
            $where = 'r.name LIKE :search or r.number LIKE :search';
            $query->andWhere($where);
            $parameters['search'] = "%$search%";
        }

        // sort method
        switch ($sortBy) {
            case 'floor':
                $query->orderBy('rf.floorNumber', $direction);
                break;
            default:
                $query->orderBy('r.'.$sortBy, $direction);
                break;
        }

        // set all parameters
        $query->setParameters($parameters);

        return $query->getQuery()->getResult();
    }

    /**
     * @param array    $types
     * @param RoomCity $city
     * @param array    $buildingIds
     * @param string   $sortBy
     * @param string   $direction
     *
     * @return array
     */
    public function getSalesProductedRooms(
        $types,
        $city,
        $buildingIds,
        $sortBy,
        $direction
    ) {
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('r')
            ->Where('
                r.id IN (
                    SELECT p.roomId
                    FROM SandboxApiBundle:Product\Product p
                    WHERE p.roomId = r.id
                    AND p.visible = true
                )
            ');

        // filter by types
        if (!is_null($types) && !empty($types)) {
            $query->andwhere('r.type IN (:types)');
            $parameters['types'] = $types;
            $notFirst = true;
        }

        // filter by city
        if (!is_null($city)) {
            $where = 'r.city = :city';
            $query->andWhere($where);
            $parameters['city'] = $city;
            $notFirst = true;
        }

        // filter by building
        if (!empty($buildingIds)) {
            $where = 'r.building IN (:buildingIds)';
            $query->andWhere($where);
            $parameters['buildingIds'] = $buildingIds;
            $notFirst = true;
        }

        //sort method
        if (!is_null($sortBy)) {
            $query->orderBy('r.'.$sortBy, $direction);
        }

        //set all parameters
        if ($notFirst) {
            $query->setParameters($parameters);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Seek all users that rented one room.
     *
     * @param $roomId
     * @param $myBuildingIds
     *
     * @return array
     */
    public function getSalesRoomUsersUsage(
        $roomId,
        $myBuildingIds
    ) {
        $query = $this->createQueryBuilder('r')
            ->select('
            	r.id,
            	up.userId as user_id,
                up.name as renter_name,
                o.startDate as start_date,
                o.endDate as end_date
            ')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Order\ProductOrder', 'o', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'o.userId = up.userId')

            ->where('up.name IS NOT NULL')
            ->andWhere('r.id = :roomId')
            ->andWhere('r.isDeleted = FALSE')
            ->andWhere('o.rejected = FALSE')
            ->setParameter('roomId', $roomId);

        // filter by my buildings
        $query->andWhere('r.buildingId IN (:buildingIds)');
        $query->setParameter('buildingIds', $myBuildingIds);

        return $query->getQuery()->getResult();
    }

    /**
     * check room usage status.
     *
     * @param $roomId
     *
     * @return array
     */
    public function getSalesRoomUsageStatus(
        $roomId
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('r')
            ->select('
                o.userId,
                v.name,
                v.email,
                v.phone,
                o.startDate,
                o.endDate,
                o.appointed,
                i.userId as invited_people
            ')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Order\ProductOrder', 'o', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:Order\InvitedPeople', 'i', 'WITH', 'o.id = i.orderId')
            ->leftJoin('SandboxApiBundle:User\UserView', 'v', 'WITH', 'o.userId = v.id')
            ->where('o.startDate <= :now AND o.endDate >= :now')
            ->andWhere('r.id = :roomId')
            ->andWhere('o.status = :status')
            ->setParameter('now', $now)
            ->setParameter('roomId', $roomId)
            ->setParameter('status', ProductOrder::STATUS_COMPLETED);

        return $query->getQuery()->getResult();
    }

    /**
     * @param RoomFloor $floor
     * @param string    $type
     * @param array     $myBuildingIds
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getSalesNotProductedRooms(
        $floor,
        $type,
        $myBuildingIds
    ) {
        if (is_null($type)) {
            throw new BadRequestHttpException();
        }

        if (Room::TYPE_DESK != $type) {
            $query = $this->createQueryBuilder('r')
                ->where('r.floor = :floor')
                ->andWhere('
                    r.id NOT IN (
                        SELECT p.roomId
                        FROM SandboxApiBundle:Product\Product p
                        WHERE p.roomId = r.id
                        AND p.visible = true
                    )
                ')
                ->setParameter('floor', $floor);
        } else {
            $query = $this->createQueryBuilder('r')
                ->where('r.floor = :floor')
                ->andWhere('
                    r.id IN (
                        SELECT f.roomId
                        FROM SandboxApiBundle:Room\RoomFixed f
                        WHERE f.roomId = r.id
                        AND f.available = true
                    )
                ')
                ->setParameter('floor', $floor);
        }
        $query = $query->andWhere('r.type = :type')
            ->andWhere('r.isDeleted = FALSE')
            ->setParameter('type', $type);

        // filter by my buildings
        $query->andWhere('r.buildingId IN (:buildingIds)');
        $query->setParameter('buildingIds', $myBuildingIds);

        $query = $query->orderBy('r.id', 'ASC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $building
     * @param null $roomtype
     *
     * @return mixed
     */
    public function countsRoomByBuilding(
        $building,
        $roomtype = null
    ) {
        $query = $this->createQueryBuilder('r')
            ->select('COUNT(r)')
            ->where('r.building = :building')
            ->andWhere('r.isDeleted = FALSE')
            ->setParameter('building', $building);

        if (!is_null($roomtype) || !empty($roomtype)) {
            $query->andWhere('r.type in (:type)')
                ->setParameter('type', $roomtype);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $salesCompanyId
     * @param $buildingId
     * @param $pageLimit
     * @param $offset
     * @param $roomTypes
     * @param $visible
     * @param $noProduct
     * @param $search
     * @param null $keyword
     * @param null $keywordSearch
     * @param null $startDate
     * @param null $startDateStart
     * @param null $startDateEnd
     * @return array
     */
    public function findSpacesByBuilding(
        $salesCompanyId,
        $buildingId,
        $pageLimit,
        $offset,
        $roomTypes,
        $visible,
        $search,
        $noProduct=null,
        $keyword=null,
        $keywordSearch=null,
        $startDate=null,
        $startDateStart=null,
        $startDateEnd=null,
        $sort=null,
        $direction=null
    ) {
        $query = $this->createQueryBuilder('r')
            ->select('
                distinct
                    r.id, 
                    r.name, 
                    r.buildingId as building_id,
                    b.name as building_name,
                    c.name as sales_company_name,
                    r.type,
                    r.area, 
                    r.allowedPeople as allowed_people,
                    r.typeTag as type_tag
            ')
            ->leftJoin('r.building', 'b')
            ->leftJoin('b.company', 'c')
            ->leftJoin('SandboxApiBundle:Room\RoomTypes', 'rt', 'WITH', 'r.type = rt.name')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'r.id = p.roomId')
            ->where('r.isDeleted = FALSE')
            ->andWhere('(p.id is null OR p.isDeleted = FALSE)');

        if (!is_null($salesCompanyId)) {
            $query->andWhere('b.company = :company')
                ->setParameter('company', $salesCompanyId);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!empty($roomTypes)) {
            $query->andWhere('r.type IN (:types)')
                ->setParameter('types', $roomTypes);
        }

        if (!is_null($visible)) {
            $query->andWhere('p.visible = :visible')
                ->setParameter('visible', $visible);
        }

        if($noProduct) {
            $query->andWhere('p.roomId IS NULL');
        }

        if (!is_null($search)) {
            $query->andWhere('r.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        if(!is_null($keyword) && !is_null($keywordSearch)) {
            $keywordArray = [
                'space_name' => 'r.name',
                'building_name' => 'b.name',
                'sales_company_name' => 'c.name'
            ];

            $query->andWhere("$keywordArray[$keyword] LIKE :name")
                ->setParameter('name', '%'.$keywordSearch.'%');
        }

        if(!is_null($startDate)) {
            $query->andWhere('p.startDate = :startDate')
                ->setParameter('startDate', $startDate);
        }

        if(!is_null($startDateStart)) {
            $query->andWhere('p.startDate >= :startDateStart')
                ->setParameter('startDateStart', $startDateStart);
        }

        if(!is_null($startDateEnd)) {
            $query->andWhere('p.startDate <= :startDateEnd')
                ->setParameter('startDateEnd', $startDateEnd);
        }

        if(!is_null($sort) && !is_null($direction)) {
            $direction = strtoupper($direction);

            //上架时间正序或者倒序排序，没有产品的空间都排在有产品的空间之后
            if($sort == 'start_date')
                $query->addSelect(
                    'CASE WHEN p.startDate IS NULL THEN 1 ELSE 0 END AS HIDDEN mystartDateIsNull'
                )
                    ->orderBy('mystartDateIsNull','ASC')
                    ->addOrderBy('p.startDate',$direction);
        }

        if (!is_null($pageLimit) && !is_null($offset)) {
            $query = $query->setFirstResult($offset)
                ->setMaxResults($pageLimit);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $pageLimit
     * @param $offset
     * @param null $buildingId
     * @param bool $salesRecommend
     *
     * @return array
     */
    public function findRecommendedSpaces(
        $pageLimit,
        $offset,
        $buildingId = null,
        $salesRecommend = false
    ) {
        $query = $this->createQueryBuilder('r')
            ->select('
                distinct
                    r.id, 
                    r.name, 
                    r.buildingId as building_id,
                    b.name as building_name,
                    c.name as sales_company_name,
                    r.type,
                    r.area, 
                    r.allowedPeople as allowed_people,
                    r.typeTag as type_tag,
                    p.sortTime,
                    p.salesSortTime
            ')
            ->leftJoin('r.building', 'b')
            ->leftJoin('b.company', 'c')
            ->leftJoin('SandboxApiBundle:Room\RoomTypes', 'rt', 'WITH', 'r.type = rt.name')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'r.id = p.roomId')
            ->where('r.isDeleted = FALSE')
            ->andWhere('p.visible = TRUE');

        if (!is_null($buildingId)) {
            $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if ($salesRecommend) {
            $query->andWhere('p.salesRecommend = TRUE')
                ->orderBy('p.salesSortTime', 'DESC');
        } else {
            $query->andWhere('p.recommend = TRUE')
                ->orderBy('p.sortTime', 'DESC');
        }

        $query = $query->setFirstResult($offset)
            ->setMaxResults($pageLimit);

        return $query->getQuery()->getResult();
    }

    /**
     * @param null $buildingId
     * @param bool $salesRecommend
     *
     * @return array
     */
    public function countRecommendedSpaces(
        $buildingId = null,
        $salesRecommend = false
    ) {
        $query = $this->createQueryBuilder('r')
            ->select('COUNT(r)')
            ->leftJoin('r.building', 'b')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'r.id = p.roomId')
            ->where('r.isDeleted = FALSE')
            ->andWhere('p.visible = TRUE');

        if (!is_null($buildingId)) {
            $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if ($salesRecommend) {
            $query->andWhere('p.salesRecommend = TRUE');
        } else {
            $query->andWhere('p.recommend = TRUE');
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    public function findSpaces(
        $buildingId,
        $search,
        $mybuildings
    ) {
        $query = $this->createQueryBuilder('r')
            ->select('
                    b.id as building_id,
                    b.name as building_name,
                    b.address as building_address,
                    r.id as room_id,
                    r.name as room_name,
                    r.area as area,
                    r.allowedPeople as allowed_people,
                    p.id as product_id
            ')
            ->leftJoin('r.building', 'b')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Product\ProductRentSet', 's', 'WITH', 'p.id = s.product')
            ->where('r.isDeleted = FALSE')
            ->andWhere('p.visible = TRUE')
            ->andWhere('s.id is not null')
            ->andWhere('r.building in (:buildingIds)')
            ->setParameter('buildingIds', $mybuildings)
            ->orderBy('r.id', 'DESC');

        if (!is_null($buildingId)) {
            $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!is_null($search)) {
            $query->andWhere('r.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $salesCompanyId
     * @param $buildingId
     * @param $roomTypes
     * @param $visible
     * @param $noProduct
     * @param $search
     * @param null $keyword
     * @param null $keywordSearch
     * @param null $startDate
     * @param null $startDateStart
     * @param null $startDateEnd
     * @return mixed
     */
    public function countSpacesByBuilding(
        $salesCompanyId,
        $buildingId,
        $roomTypes,
        $visible,
        $noProduct,
        $search,
        $keyword=null,
        $keywordSearch=null,
        $startDate=null,
        $startDateStart=null,
        $startDateEnd=null
    ) {
        $query = $this->createQueryBuilder('r')
            ->select('
                count(r.id)
            ')
            ->leftJoin('r.building', 'b')
            ->leftJoin('b.company', 'c')
            ->leftJoin('SandboxApiBundle:Room\RoomTypes', 'rt', 'WITH', 'r.type = rt.name')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'r.id = p.roomId')
            ->where('r.isDeleted = FALSE')
            ->andWhere('(p.id is null OR p.isDeleted = FALSE)')
        ;

        if (!is_null($salesCompanyId)) {
            $query->andWhere('b.company = :company')
                ->setParameter('company', $salesCompanyId);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!empty($roomTypes)) {
            $query->andWhere('r.type IN (:types)')
                ->setParameter('types', $roomTypes);
        }

        if (!is_null($visible)) {
            $query->andWhere('p.visible = :visible')
                ->setParameter('visible', $visible);
        }

        if($noProduct) {
            $query->andWhere('p.roomId IS NULL')
                ->orWhere('p.isDeleted = TRUE');
        }

        if (!is_null($search)) {
            $query->andWhere('r.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        if(!is_null($keyword) && !is_null($keywordSearch)) {
            $keywordArray = [
                'space_name' => 'r.name',
                'building_name' => 'b.name',
                'sales_company_name' => 'c.name'
            ];

            $query->andWhere("$keywordArray[$keyword] LIKE :name")
                ->setParameter('name', '%'.$keywordSearch.'%');
        }

        if(!is_null($startDate)) {
            $query->andWhere('p.startDate = :startDate')
                ->setParameter('startDate', $startDate);
        }

        if(!is_null($startDateStart)) {
            $query->andWhere('p.startDate >= :startDateStart')
                ->setParameter('startDateStart', $startDateStart);
        }

        if(!is_null($startDateEnd)) {
            $query->andWhere('p.startDate <= :startDateEnd')
                ->setParameter('startDateEnd', $startDateEnd);
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return $result;
    }
}
