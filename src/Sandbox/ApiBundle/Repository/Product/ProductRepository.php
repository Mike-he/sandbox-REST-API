<?php

namespace Sandbox\ApiBundle\Repository\Product;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\AdminApiBundle\Data\Product\ProductRecommendPosition;
use Sandbox\ApiBundle\Entity\Room\RoomTypes;
use Sandbox\ApiBundle\Entity\User\UserFavorite;

class ProductRepository extends EntityRepository
{
    /**
     * @param $lat
     * @param $lng
     * @param $productIds
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function productSortByNearestBuilding(
        $lat,
        $lng,
        $productIds,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('
                p,
                (
                    6371
                    * acos(cos(radians(:latitude)) * cos(radians(b.lat))
                    * cos(radians(b.lng) - radians(:longitude))
                    + sin(radians(:latitude)) * sin(radians(b.lat)))
                ) as HIDDEN distance
            ')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = r.buildingId')
            ->where('p.id IN (:productIds)')
            ->andWhere('b.status = :accept')
            ->andWhere('b.visible = TRUE')
            ->andWhere('b.isDeleted = FALSE')
            ->setParameter('productIds', $productIds)
            ->setParameter('accept', RoomBuilding::STATUS_ACCEPT)
            ->setParameter('latitude', $lat)
            ->setParameter('longitude', $lng)
            ->orderBy('distance', 'ASC')
            ->addOrderBy('p.creationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $cityId
     * @param $buildingId
     * @param $allowedPeople
     * @param $startTime
     * @param $endTime
     * @param $startHour
     * @param $endHour
     * @param $type
     * @param $includeIds
     * @param $excludeIds
     *
     * @return array
     */
    public function getMeetingProductsForClient(
        $userId,
        $cityId,
        $buildingId,
        $allowedPeople,
        $startTime,
        $endTime,
        $startHour,
        $endHour,
        $type,
        $includeIds,
        $excludeIds
    ) {
        $now = new \DateTime();

        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomMeeting', 'm', 'WITH', 'p.roomId = m.room')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = r.buildingId')
            ->where('r.type = :type')
            ->andWhere('p.visible = :visible')
            ->andWhere('p.startDate <= :now AND p.endDate >= :now')
            ->setParameter('type', $type)
            ->setParameter('visible', true)
            ->setParameter('now', $now);

        if (!is_null($includeIds) && !empty($includeIds)) {
            $query->andWhere('b.companyId IN (:includeIds)')
                ->setParameter('includeIds', $includeIds);
        }

        if (!is_null($excludeIds) && !empty($excludeIds)) {
            $query->andWhere('b.companyId NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }

        if (!is_null($userId)) {
            $query->andWhere('p.visibleUserId = :userId OR p.private = :private')
                ->setParameter('userId', $userId)
                ->setParameter('private', false);
        } else {
            $query->andWhere('p.private = :private')
                ->setParameter('private', false);
        }

        if (!is_null($cityId) && !empty($cityId)) {
            $query = $query->andWhere('r.city = :cityId')
                ->setParameter('cityId', $cityId);
        }

        if (!is_null($buildingId) && !empty($buildingId)) {
            $query = $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!is_null($allowedPeople) && !empty($allowedPeople)) {
            $query = $query->andWhere('r.allowedPeople >= :allowedPeople')
                ->setParameter('allowedPeople', $allowedPeople);
        }

        if (!is_null($startTime) && !empty($startTime) && (is_null($endTime) || empty($endTime))) {
            $currentDateStart = clone $startTime;
            $currentDateStart->setTime(00, 00, 00);
            $currentDateEnd = clone $startTime;
            $currentDateEnd->setTime(23, 59, 59);

            $query = $query->andWhere('m.endHour > :startHour')
                ->andWhere('p.startDate <= :startTime AND p.endDate >= :startTime')
                ->andWhere(
                    'p.id NOT IN (
                        SELECT po.productId
                        FROM SandboxApiBundle:Order\ProductOrder po
                        WHERE po.status != :status
                        AND po.startDate >= :currentDateStart
                        AND po.endDate <= :currentDateEnd
                        AND po.endDate > :startTime
                        GROUP BY po.productId
                        HAVING (
                            (
                                (
                                    CASE
                                        WHEN time(:startHour) < m.startHour
                                        THEN hour((m.endHour - m.startHour))
                                        WHEN MIN(po.startDate) >= :startTime
                                        THEN hour((m.endHour - time(:startHour)))
                                        ELSE hour((m.endHour - time(MIN(po.startDate))))
                                    END
                                ) <= hour((sum(po.endDate) - sum(po.startDate)))
                            )
                        )
                    )'
                )
                ->setParameter('status', ProductOrder::STATUS_CANCELLED)
                ->setParameter('currentDateStart', $currentDateStart)
                ->setParameter('currentDateEnd', $currentDateEnd)
                ->setParameter('startTime', $startTime)
                ->setParameter('startHour', $startHour);
        }

        if (!is_null($startTime) && !is_null($endTime) && !empty($startTime) && !empty($endTime)) {
            $query = $query->andWhere('m.startHour <= :startHour AND m.endHour >= :endHour')
                ->andWhere('p.startDate <= :startTime')
                ->andWhere('p.endDate >= :startTime')
                ->andWhere(
                    'p.id NOT IN (
                        SELECT po.productId FROM SandboxApiBundle:Order\ProductOrder po
                        WHERE po.status != :status
                        AND
                        (
                            (po.startDate <= :startTime AND po.endDate > :startTime) OR
                            (po.startDate < :endTime AND po.endDate >= :endTime) OR
                            (po.startDate >= :startTime AND po.endDate <= :endTime)
                        )
                    )'
                )
                ->setParameter('status', ProductOrder::STATUS_CANCELLED)
                ->setParameter('startTime', $startTime)
                ->setParameter('endTime', $endTime)
                ->setParameter('startHour', $startHour)
                ->setParameter('endHour', $endHour);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $cityId
     * @param $buildingId
     * @param $allowedPeople
     * @param $startDate
     * @param $endDate
     * @param $includeIds
     * @param $excludeIds
     *
     * @return array
     */
    public function getOfficeProductsForClient(
        $userId,
        $cityId,
        $buildingId,
        $allowedPeople,
        $startDate,
        $endDate,
        $includeIds,
        $excludeIds
    ) {
        $now = new \DateTime();

        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = r.buildingId')
            ->where('r.type = :office')
            ->andWhere('p.visible = :visible')
            ->andWhere('p.startDate <= :now AND p.endDate >= :now')
            ->setParameter('office', Room::TYPE_OFFICE)
            ->setParameter('visible', true)
            ->setParameter('now', $now);

        if (!is_null($includeIds) && !empty($includeIds)) {
            $query->andWhere('b.companyId IN (:includeIds)')
                ->setParameter('includeIds', $includeIds);
        }

        if (!is_null($excludeIds) && !empty($excludeIds)) {
            $query->andWhere('b.companyId NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }

        if (!is_null($userId)) {
            $query->andWhere('p.visibleUserId = :userId OR p.private = :private')
                ->setParameter('userId', $userId)
                ->setParameter('private', false);
        } else {
            $query->andWhere('p.private = :private')
                ->setParameter('private', false);
        }

        if (!is_null($cityId) && !empty($cityId)) {
            $query = $query->andWhere('r.city = :cityId')
                ->setParameter('cityId', $cityId);
        }

        if (!is_null($buildingId) && !empty($buildingId)) {
            $query = $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!is_null($allowedPeople) && !empty($allowedPeople)) {
            $query = $query->andWhere('r.allowedPeople >= :allowedPeople')
                ->setParameter('allowedPeople', $allowedPeople);
        }

        if (!is_null($startDate) && !is_null($endDate) && !empty($startDate) && !empty($endDate)) {
            $query = $query->andWhere('p.startDate <= :startDate')
                ->andWhere('p.endDate >= :startDate')
                ->andWhere(
                    'p.id NOT IN (
                        SELECT po.productId FROM SandboxApiBundle:Order\ProductOrder po
                        WHERE po.status != :status
                        AND
                        (
                            (po.startDate <= :startDate AND po.endDate > :startDate) OR
                            (po.startDate < :endDate AND po.endDate >= :endDate) OR
                            (po.startDate >= :startDate AND po.endDate <= :endDate)
                        )
                    )'
                )
                ->setParameter('status', ProductOrder::STATUS_CANCELLED)
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $cityId
     * @param $buildingId
     * @param $allowedPeople
     * @param $startDate
     * @param $endDate
     * @param $type
     * @param $includeIds
     * @param $excludeIds
     *
     * @return array
     */
    public function getWorkspaceProductsForClient(
        $userId,
        $cityId,
        $buildingId,
        $allowedPeople,
        $startDate,
        $endDate,
        $type,
        $includeIds,
        $excludeIds
    ) {
        $now = new \DateTime();

        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = r.buildingId')
            ->where('r.type = :type')
            ->andWhere('p.visible = :visible')
            ->andWhere('p.startDate <= :now AND p.endDate >= :now')
            ->setParameter('type', $type)
            ->setParameter('visible', true)
            ->setParameter('now', $now);

        if (!is_null($includeIds) && !empty($includeIds)) {
            $query->andWhere('b.companyId IN (:includeIds)')
                ->setParameter('includeIds', $includeIds);
        }

        if (!is_null($excludeIds) && !empty($excludeIds)) {
            $query->andWhere('b.companyId NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }

        if (!is_null($userId)) {
            $query->andWhere('p.visibleUserId = :userId OR p.private = :private')
                ->setParameter('userId', $userId)
                ->setParameter('private', false);
        } else {
            $query->andWhere('p.private = :private')
                ->setParameter('private', false);
        }

        if (!is_null($cityId) && !empty($cityId)) {
            $query = $query->andWhere('r.city = :cityId')
                ->setParameter('cityId', $cityId);
        }

        if (!is_null($buildingId) && !empty($buildingId)) {
            $query = $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!is_null($allowedPeople) && !empty($allowedPeople)) {
            $query = $query->andWhere('r.allowedPeople >= :allowedPeople')
                ->setParameter('allowedPeople', $allowedPeople);
        }

        if (!is_null($startDate) && !is_null($endDate) && !empty($startDate) && !empty($endDate)) {
            $query = $query->andWhere('p.startDate <= :startDate')
                ->andWhere('p.endDate >= :startDate')
                ->andWhere(
                    '(                      
                        p.id IN (
                            SELECT po2.productId FROM SandboxApiBundle:Order\ProductOrder po2
                            WHERE po2.status != :status
                            AND (r.type = :desk)
                            AND
                            (
                                (po2.startDate <= :startDate AND po2.endDate > :startDate) OR
                                (po2.startDate < :endDate AND po2.endDate >= :endDate) OR
                                (po2.startDate >= :startDate AND po2.endDate <= :endDate)
                            )
                            GROUP BY po2.productId
                            HAVING COUNT(po2.productId) < r.allowedPeople
                        )
                    )'
                )
                ->setParameter('status', ProductOrder::STATUS_CANCELLED)
                ->setParameter('desk', Room::TYPE_DESK)
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Get all products.
     *
     * @param string       $type
     * @param RoomCity     $city
     * @param RoomBuilding $building
     * @param int          $visible
     * @param string       $sortBy
     * @param string       $direction
     * @param string       $search
     * @param bool         $recommend
     * @param int          $companyId
     * @param int          $floor
     * @param int          $minSeat
     * @param int          $maxSeat
     * @param int          $minArea
     * @param int          $maxArea
     * @param float        $minPrice
     * @param float        $maxPrice
     * @param bool         $annualRent
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAdminProducts(
        $type,
        $city,
        $building,
        $visible,
        $sortBy,
        $direction,
        $search,
        $recommend,
        $companyId = null,
        $floor = null,
        $minSeat = null,
        $maxSeat = null,
        $minArea = null,
        $maxArea = null,
        $minPrice = null,
        $maxPrice = null,
        $annualRent = null
    ) {
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('p')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'rb', 'WITH', 'r.building = rb.id');

        // only needed when searching products
        if (!is_null($search)) {
            $query->leftJoin('SandboxApiBundle:Room\RoomCity', 'rc', 'WITH', 'r.city = rc.id');
        }

        // filter by type
        if (!is_null($type)) {
            $query->where('r.type = :type');
            $parameters['type'] = $type;
            $notFirst = true;
        }

        // filter by visible
        if (!is_null($visible)) {
            $where = 'p.visible = :visible';
            $this->addWhereQuery($query, $notFirst, $where);

            $now = new \DateTime('now');

            // product off sale
            if ($visible == Product::OFF_SALE) {
                $parameters['visible'] = false;
            }
            // product on sale and in the rent time
            elseif ($visible == Product::ON_SALE) {
                $parameters['visible'] = true;
                $query->andWhere(':now BETWEEN p.startDate AND p.endDate');
                $parameters['now'] = $now;
            }
            // product ready sale and before the rent time
            elseif ($visible == Product::READY_SALE) {
                $parameters['visible'] = true;
                $query->andWhere(':now < p.startDate');
                $parameters['now'] = $now;
            }

            $notFirst = true;
        }

        // filter by city
        if (!is_null($city)) {
            $where = 'r.city = :city';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['city'] = $city;
            $notFirst = true;
        }

        // filter by building
        if (!is_null($building)) {
            $where = 'r.building = :building';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['building'] = $building;
            $notFirst = true;
        }

        // filter by company
        if (!is_null($companyId)) {
            $where = 'rb.companyId = :companyId';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['companyId'] = $companyId;
            $notFirst = true;
        }

        //Search product by city, building, room name and room number.
        if (!is_null($search)) {
            $where = '
                (r.name LIKE :search OR
                r.number LIKE :search OR
                rc.name LIKE :search OR
                rb.name LIKE :search)
            ';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['search'] = "%$search%";
            $notFirst = true;
        }

        // get only recommend products
        if ($recommend) {
            $where = 'p.recommend = :recommend';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['recommend'] = $recommend;
            $notFirst = true;
        }

        if (!is_null($floor)) {
            $query->leftJoin('SandboxApiBundle:Room\RoomFloor', 'rf', 'WITH', 'r.floorId = rf.id');
            $where = 'rf.floorNumber = :floor';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['floor'] = $floor;
            $notFirst = true;
        }

        if (!is_null($minSeat)) {
            $where = 'r.allowedPeople >= :minSeat';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['minSeat'] = $minSeat;
            $notFirst = true;
        }

        if (!is_null($maxSeat)) {
            $where = 'r.allowedPeople <= :maxSeat';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['maxSeat'] = $maxSeat;
            $notFirst = true;
        }

        if (!is_null($minArea)) {
            $where = 'r.area >= :minArea';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['minArea'] = $minArea;
            $notFirst = true;
        }

        if (!is_null($maxArea)) {
            $where = 'r.area <= :maxArea';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['maxArea'] = $maxArea;
            $notFirst = true;
        }

        if (!is_null($minPrice)) {
            $where = 'p.basePrice >= :minPrice';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['minPrice'] = $minPrice;
            $notFirst = true;
        }

        if (!is_null($maxPrice)) {
            $where = 'p.basePrice <= :maxPrice';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['maxPrice'] = $maxPrice;
            $notFirst = true;
        }

        // filters by annual rent
        if (!is_null($annualRent)) {
            $where = 'p.isAnnualRent = :annualRent';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['annualRent'] = $annualRent;
            $notFirst = true;
        }

        // get not deleted products
        $where = 'p.isDeleted = FALSE';
        $this->addWhereQuery($query, $notFirst, $where);

        // sort by by method
        switch ($sortBy) {
            case 'area':
                $query->orderBy('r.'.$sortBy, $direction);
                break;
            case 'basePrice':
                $query->orderBy('p.'.$sortBy, $direction);
                break;
            default:
                $query->orderBy('p.'.$sortBy, $direction);
                break;
        }

        // set all parameters
        if ($notFirst) {
            $query->setParameters($parameters);
        }

        $result = $query->getQuery()->getResult();

        return $result;
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
     * @param $roomId
     *
     * @return mixed
     */
    public function checkFixedRoomInProduct(
        $roomId
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.roomId = :roomId')
            ->andWhere('p.visible = :visible')
            ->setParameter('visible', true)
            ->setParameter('roomId', $roomId)
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    public function setVisibleFalse()
    {
        $now = new \DateTime();
        $nowString = (string) $now->format('Y-m-d H:i:s');
        $nowString = "'$nowString'";
        $valueFalse = 'false';

        $query = $this->createQueryBuilder('p')
            ->update()
            ->set('p.visible', $valueFalse)
            ->set('p.modificationDate', $nowString)
            ->where('p.visible = :status')
            ->andWhere('p.endDate <= :now')
            ->setParameter('now', $now)
            ->setParameter('status', true)
            ->getQuery();

        $query->execute();
    }

    /**
     * @param $building
     */
    public function setVisibleTrue(
        $building
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->where('r.building = :building')
            ->setParameter('building', $building);

        $productIds = $query->getQuery()->getResult();
        $productIds = array_map('current', $productIds);

        $now = new \DateTime();
        $nowString = (string) $now->format('Y-m-d H:i:s');
        $nowString = "'$nowString'";
        $valueTrue = 'true';

        $query = $this->createQueryBuilder('p')
            ->update()
            ->set('p.visible', $valueTrue)
            ->set('p.modificationDate', $nowString)
            ->where('p.visible = :status')
            ->andWhere('p.isDeleted = FALSE')
            ->andWhere('p.startDate <= :now')
            ->andWhere('p.endDate > :now')
            ->andWhere('p.id IN (:ids)')
            ->setParameter('now', $now)
            ->setParameter('status', false)
            ->setParameter('ids', $productIds)
            ->getQuery();

        $query->execute();
    }

    /**
     * @param Product      $product
     * @param string       $action
     * @param RoomCity     $city
     * @param RoomBuilding $building
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findSwapProduct(
        $product,
        $action,
        $building = null
    ) {
        $queryStr = 'SELECT p FROM SandboxApiBundle:Product\Product p';
        $queryStr = $queryStr.' LEFT JOIN SandboxApiBundle:Room\Room r WITH p.roomId = r.id';

        // operator
        $operator = '>';
        if ($action == ProductRecommendPosition::ACTION_DOWN) {
            $operator = '<';
        }

        // order by
        $orderBy = 'ASC';
        if ($action == ProductRecommendPosition::ACTION_DOWN) {
            $orderBy = 'DESC';
        }

        if (!is_null($building)) {
            $queryStr = $queryStr.' WHERE p.salesRecommend = :recommend AND r.building = :building';
            $queryStr = $queryStr.' AND p.salesSortTime '.$operator.' :sortTime';
            $queryStr = $queryStr.' ORDER BY p.salesSortTime '.$orderBy;

            $sortTime = $product->getSalesSortTime();
        } else {
            $queryStr = $queryStr.' WHERE p.recommend = :recommend';
            $queryStr = $queryStr.' AND p.sortTime '.$operator.' :sortTime';
            $queryStr = $queryStr.' ORDER BY p.sortTime '.$orderBy;

            $sortTime = $product->getSortTime();
        }

        // set parameters
        $query = $this->getEntityManager()->createQuery($queryStr);
        $query->setParameter('recommend', true)->setParameter('sortTime', $sortTime);

        if (!is_null($building)) {
            $query->setParameter('building', $building);
        }

        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    /**
     * @param int      $userId
     * @param RoomCity $city
     * @param array    $excludeIds
     * @param int      $limit
     * @param int      $offset
     * @param bool     $recommend
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getProductsRecommend(
        $userId,
        $city,
        $excludeIds,
        $limit,
        $offset,
        $recommend
    ) {
        $query = $this->createQueryBuilder('p')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = r.buildingId')
            ->where('p.visible = :visible')
            ->andWhere('p.recommend = :recommend')
            ->andWhere('p.startDate <= :now AND p.endDate >= :now')
            ->setParameter('visible', true)
            ->setParameter('recommend', $recommend)
            ->setParameter('now', new \DateTime('now'));

        if (!is_null($city)) {
            $query->andWhere('r.city = :city')
                ->setParameter('city', $city);
        }

        if (!is_null($userId)) {
            $query->andWhere('p.visibleUserId = :userId OR p.private = :private')
                ->setParameter('userId', $userId)
                ->setParameter('private', false);
        } else {
            $query->andWhere('p.private = :private')
                ->setParameter('private', false);
        }

        if (!is_null($excludeIds) && !empty($excludeIds)) {
            $query->andWhere('b.companyId NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }

        if ($recommend) {
            $query->orderBy('p.sortTime', 'DESC');
        } else {
            $query->orderBy('p.creationDate', 'DESC');
        }

        $query = $query->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param int      $userId
     * @param RoomCity $city
     * @param bool     $recommend
     *
     * @return int
     */
    public function getProductsRecommendCount(
        $userId,
        $city,
        $recommend
    ) {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->where('p.visible = :visible')
            ->andWhere('p.recommend = :recommend')
            ->andWhere('p.startDate <= :now AND p.endDate >= :now')
            ->setParameter('visible', true)
            ->setParameter('recommend', $recommend)
            ->setParameter('now', new \DateTime('now'));

        if (!is_null($userId)) {
            $queryBuilder->andWhere('p.visibleUserId = :userId OR p.private = :private')
                ->setParameter('userId', $userId)
                ->setParameter('private', false);
        } else {
            $queryBuilder->andWhere('p.private = :private')
                ->setParameter('private', false);
        }

        if (!is_null($city)) {
            $queryBuilder->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
                ->andWhere('r.city = :city')
                ->setParameter('city', $city);
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    //-------------------- sales repository --------------------//

    /**
     * Get all products.
     *
     * @param array        $myBuildingIds
     * @param string       $type
     * @param RoomCity     $city
     * @param RoomBuilding $building
     * @param int          $visible
     * @param string       $sortBy
     * @param string       $direction
     * @param string       $search
     * @param int          $floor
     * @param int          $minSeat
     * @param int          $maxSeat
     * @param int          $minArea
     * @param int          $maxArea
     * @param float        $minPrice
     * @param float        $maxPrice
     * @param bool         $recommend
     *
     * @return array
     */
    public function getSalesAdminProducts(
        $myBuildingIds,
        $type,
        $city,
        $building,
        $visible,
        $sortBy,
        $direction,
        $search,
        $floor,
        $minSeat,
        $maxSeat,
        $minArea,
        $maxArea,
        $minPrice,
        $maxPrice,
        $recommend = false
    ) {
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('p')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId');

        // only needed when searching products
        if (!is_null($search)) {
            $query->leftJoin('SandboxApiBundle:Room\RoomCity', 'rc', 'WITH', 'r.city = rc.id');
            $query->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'rb', 'WITH', 'r.buildingId = rb.id');
        }

        if (!is_null($floor)) {
            $query->leftJoin('SandboxApiBundle:Room\RoomFloor', 'rf', 'WITH', 'r.floorId = rf.id');
            $where = 'rf.floorNumber = :floor';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['floor'] = $floor;
            $notFirst = true;
        }

        if (!is_null($minSeat)) {
            $where = 'r.allowedPeople >= :minSeat';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['minSeat'] = $minSeat;
            $notFirst = true;
        }

        if (!is_null($maxSeat)) {
            $where = 'r.allowedPeople <= :maxSeat';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['maxSeat'] = $maxSeat;
            $notFirst = true;
        }

        if (!is_null($minArea)) {
            $where = 'r.area >= :minArea';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['minArea'] = $minArea;
            $notFirst = true;
        }

        if (!is_null($maxArea)) {
            $where = 'r.area <= :maxArea';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['maxArea'] = $maxArea;
            $notFirst = true;
        }

        if (!is_null($minPrice)) {
            $where = 'p.basePrice >= :minPrice';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['minPrice'] = $minPrice;
            $notFirst = true;
        }

        if (!is_null($maxPrice)) {
            $where = 'p.basePrice <= :maxPrice';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['maxPrice'] = $maxPrice;
            $notFirst = true;
        }

        // filter by type
        if (!is_null($type)) {
            $where = 'r.type = :type';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['type'] = $type;
            $notFirst = true;
        }

        // filter by visible
        if (!is_null($visible)) {
            $where = 'p.visible = :visible';
            $this->addWhereQuery($query, $notFirst, $where);

            $now = new \DateTime('now');

            // product off sale
            if ($visible == Product::OFF_SALE) {
                $parameters['visible'] = false;
            }
            // product on sale and in the rent time
            elseif ($visible == Product::ON_SALE) {
                $parameters['visible'] = true;
                $query->andWhere(':now BETWEEN p.startDate AND p.endDate');
                $parameters['now'] = $now;
            }
            // product ready sale and before the rent time
            elseif ($visible == Product::READY_SALE) {
                $parameters['visible'] = true;
                $query->andWhere(':now < p.startDate');
                $parameters['now'] = $now;
            }

            $notFirst = true;
        }

        // filter by city
        if (!is_null($city)) {
            $where = 'r.city = :city';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['city'] = $city;
            $notFirst = true;
        }

        // filter by building
        if (!is_null($building)) {
            $where = 'r.building = :building';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['building'] = $building;
            $notFirst = true;
        } else {
            $where = 'r.buildingId IN (:buildingIds)';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['buildingIds'] = $myBuildingIds;
            $notFirst = true;
        }

        //Search product by city, building, room name and room number.
        if (!is_null($search)) {
            $where = '
                (r.name LIKE :search OR
                r.number LIKE :search OR
                rc.name LIKE :search OR
                rb.name LIKE :search)
            ';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['search'] = "%$search%";
            $notFirst = true;
        }

        // get only recommend products
        if ($recommend) {
            $where = 'p.recommend = :recommend';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['recommend'] = $recommend;
            $notFirst = true;
        }

        // get not deleted products
        $where = 'p.isDeleted = FALSE';
        $this->addWhereQuery($query, $notFirst, $where);

        // sort by by method
        switch ($sortBy) {
            case 'area':
                $query->orderBy('r.'.$sortBy, $direction);
                break;
            case 'basePrice':
                $query->orderBy('p.'.$sortBy, $direction);
                break;
            default:
                $query->orderBy('p.'.$sortBy, $direction);
                break;
        }

        // set all parameters
        if ($notFirst) {
            $query->setParameters($parameters);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param RoomBuilding $building
     *
     * @return array
     */
    public function getSalesProductsByBuilding(
        $building
    ) {
        $query = $this->createQueryBuilder('p')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->where('r.building = :building')
            ->setParameter('building', $building);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $building
     * @param $visible
     * @param $roomtype
     *
     * @return mixed
     */
    public function countsProductByBuilding(
        $building,
        $visible = null,
        $roomtype = null
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->where('r.building = :building')
            ->andWhere('p.isDeleted = FALSE')
            ->setParameter('building', $building);

        if (!is_null($roomtype) || !empty($roomtype)) {
            $query->andWhere('r.type in (:type)')
                ->setParameter('type', $roomtype);
        }

        if (!is_null($visible)) {
            $query->andWhere('p.visible = :visible')
                ->setParameter('visible', $visible);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $building
     * @param $userId
     *
     * @return int
     */
    public function countRoomsWithProductByBuilding(
        $building,
        $userId
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('count(distinct r.id)')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->where('r.building = :building')
            ->andWhere('p.isDeleted = FALSE')
            ->andWhere('p.visible = TRUE')
            ->setParameter('building', $building);

        if (!is_null($userId)) {
            $query->andWhere('p.visibleUserId = :userId OR p.private = :private')
                ->setParameter('userId', $userId)
                ->setParameter('private', false);
        } else {
            $query->andWhere('p.private = :private')
                ->setParameter('private', false);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $lat
     * @param $lng
     * @param $productIds
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function findFavoriteProducts(
        $lat,
        $lng,
        $productIds,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('
                p as product,
                (
                    6371
                    * acos(cos(radians(:latitude)) * cos(radians(b.lat))
                    * cos(radians(b.lng) - radians(:longitude))
                    + sin(radians(:latitude)) * sin(radians(b.lat)))
                ) as distance
            ')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = r.buildingId')
            ->where('p.id IN (:productIds)')
            ->andWhere('p.isDeleted = FALSE')
            ->andWhere('p.visible = TRUE')
            ->andWhere('b.status = :accept')
            ->andWhere('b.visible = TRUE')
            ->andWhere('b.isDeleted = FALSE')
            ->setParameter('productIds', $productIds)
            ->setParameter('accept', RoomBuilding::STATUS_ACCEPT)
            ->setParameter('latitude', $lat)
            ->setParameter('longitude', $lng)
            ->orderBy('distance', 'ASC')
            ->addOrderBy('p.creationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $buildingId
     * @param $userId
     * @param $limit
     * @param $offset
     * @param $includeIds
     *
     * @return array
     */
    public function getAllProductsForOneBuildingOrCompany(
        $buildingId,
        $userId,
        $limit,
        $offset,
        $includeIds,
        $recommend
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->where('p.visible = TRUE')
            ->andWhere('p.isDeleted = FALSE')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
        ;

        if (!is_null($buildingId)) {
            $query->andWhere('r.buildingId = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if ($recommend) {
            $query->orderBy('p.salesRecommend', 'DESC')
                ->addOrderBy('p.salesSortTime', 'DESC')
                ->addOrderBy('p.creationDate', 'DESC');
        } else {
            $query->orderBy('p.recommend', 'DESC')
                ->addOrderBy('p.creationDate', 'DESC');
        }

        if (!is_null($includeIds) && !empty($includeIds)) {
            $query->leftjoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = r.buildingId')
                ->andWhere('b.companyId IN (:ids)')
                ->setParameter('ids', $includeIds);
        }

        if (!is_null($userId)) {
            $query->andWhere('p.visibleUserId = :userId OR p.private = :private')
                ->setParameter('userId', $userId)
                ->setParameter('private', false);
        } else {
            $query->andWhere('p.private = :private')
                ->setParameter('private', false);
        }

        return $query->getQuery()->getResult();
    }

    public function countsProductByType(
        $building,
        $type,
        $visible = null
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->leftJoin('p.room', 'r')
            ->where('p.isDeleted = FALSE')
            ->andWhere('r.isDeleted = FALSE')
            ->andWhere('r.building = :building')
            ->andWhere('r.type = :type')
            ->setParameter('building', $building)
            ->setParameter('type', $type);

        if (!is_null($visible)) {
            $query->andWhere('p.visible = :visible')
                ->setParameter('visible', $visible);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param id
     *
     * @return array
     */
    public function getLongTermProductById(
        $id
    ) {
        $query = $this->createQueryBuilder('p')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->where('p.id = :id')
            ->andWhere('p.visible = TRUE')
            ->andWhere('p.isDeleted = FALSE')
            ->andWhere('p.appointment = TRUE')
            ->andWhere('r.type = :office')
            ->setParameter('id', $id)
            ->setParameter('office', Room::TYPE_OFFICE);

        return $query->getQuery()->getOneOrNullResult();
    }

    public function findProductsByType(
        $company,
        $type
    ) {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.room', 'r')
            ->leftJoin('r.building', 'b')
            ->where('p.isDeleted = FALSE')
            ->where('p.visible = TRUE')
            ->andWhere('r.isDeleted = FALSE')
            ->andWhere('b.company = :company')
            ->andWhere('r.type = :type')
            ->setParameter('company', $company)
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $company
     * @param $type
     * @param $building
     * @param $search
     * @param $visible
     *
     * @return array
     */
    public function findProductIdsByRoomType(
        $company,
        $type,
        $building,
        $search,
        $visible
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('
                    p.id,
                    p.visible,
                    r.id as room_id,
                    r.name as room_name,
                    r.type as room_type,
                    r.allowedPeople as allowed_people,
                    r.area,
                    r.typeTag as type_tag,
                    rm.startHour as start_hour,
                    rm.endHour as end_hour
                ')
            ->leftJoin('p.room', 'r')
            ->leftJoin('r.building', 'b')
            ->leftJoin('SandboxApiBundle:Room\RoomMeeting', 'rm', 'WITH', 'r.id = rm.room')
            ->where('p.isDeleted = FALSE')
            ->andWhere('r.isDeleted = FALSE')
            ->andWhere('b.company = :company')
            ->andWhere('r.type = :type')
            ->setParameter('company', $company)
            ->setParameter('type', $type);

        if ($building) {
            $query->andWhere('b.id = :building')
                ->setParameter('building', $building);
        }

        if ($search) {
            $query->andWhere('r.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        if (!is_null($visible)) {
            $query->andWhere('p.visible = :visible')
                ->setParameter('visible', $visible);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $userId
     * @param $buildingIds
     * @param $minAllowedPeople
     * @param $maxAllowedPeople
     * @param $startTime
     * @param $endTime
     * @param $startHour
     * @param $endHour
     * @param $type
     * @param $includeIds
     * @param $excludeIds
     * @param $isFavorite
     *
     * @return array
     */
    public function getMeetingProductsForClientCommunities(
        $userId,
        $buildingIds,
        $minAllowedPeople,
        $maxAllowedPeople,
        $startTime,
        $endTime,
        $startHour,
        $endHour,
        $type,
        $includeIds,
        $excludeIds,
        $unit,
        $isFavorite,
        $minBasePrice,
        $maxBasePrice,
        $roomTypeTags,
        $startDateString,
        $endDateString,
        $search
    ) {
        $now = new \DateTime();

        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomMeeting', 'm', 'WITH', 'p.roomId = m.room')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = r.buildingId')
            ->where('r.type = :type')
            ->andWhere('p.visible = :visible')
            ->andWhere('p.startDate <= :now AND p.endDate >= :now')
            ->setParameter('type', $type)
            ->setParameter('visible', true)
            ->setParameter('now', $now);

        if (!is_null($includeIds) && !empty($includeIds)) {
            $query->andWhere('b.companyId IN (:includeIds)')
                ->setParameter('includeIds', $includeIds);
        }

        if (!is_null($excludeIds) && !empty($excludeIds)) {
            $query->andWhere('b.companyId NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }

        if (!is_null($userId)) {
            $query->andWhere('p.visibleUserId = :userId OR p.private = :private')
                ->setParameter('private', false);

            if ($isFavorite) {
                $query->leftJoin('SandboxApiBundle:User\UserFavorite', 'uf', 'WITH', 'uf.objectId = p.id')
                    ->andWhere('uf.userId = :userId')
                    ->andWhere('uf.object = :product')
                    ->setParameter('product', UserFavorite::OBJECT_PRODUCT);
            }

            $query->setParameter('userId', $userId);
        } else {
            $query->andWhere('p.private = :private')
                ->setParameter('private', false);
        }

        if (!is_null($buildingIds)) {
            $query->andWhere('r.building IN (:buildingIds)')
                ->setParameter('buildingIds', $buildingIds);
        }

        if (!is_null($minAllowedPeople) && !empty($minAllowedPeople)) {
            $query->andWhere('r.allowedPeople >= :minAllowedPeople')
                ->setParameter('minAllowedPeople', $minAllowedPeople);
        }

        if (!is_null($maxAllowedPeople) && !empty($maxAllowedPeople)) {
            $query->andWhere('r.allowedPeople <= :maxAllowedPeople')
                ->setParameter('maxAllowedPeople', $maxAllowedPeople);
        }

        if (!is_null($startTime) && !is_null($endTime) && !is_null($startDateString) && !is_null($endDateString)) {
            if (is_null($unit)) {
                if ($type == RoomTypes::TYPE_NAME_MEETING) {
                    $query = $this->getMeetingProductsQuery(
                        $query,
                        $startDateString,
                        $endDateString
                    );
                } elseif ($type == RoomTypes::TYPE_NAME_OTHERS) {
                    $query = $this->getOthersProductsQuery(
                        $query,
                        $startDateString,
                        $endDateString
                    );
                }
            } else {
                $query->andWhere('p.startDate <= :startTime')
                    ->andWhere('p.endDate >= :startTime')
                    ->andWhere(
                        'p.id NOT IN (
                        SELECT po.productId FROM SandboxApiBundle:Order\ProductOrder po
                        WHERE po.status != :status
                        AND
                        (
                            (po.startDate >= :startTime AND po.startDate <= :endTime) OR
                            (po.endDate >= :startTime AND po.endDate <= :endTime) OR
                            (po.startDate <= :startTime AND po.endDate >= :endTime)
                        )
                    )'
                    )
                    ->setParameter('status', ProductOrder::STATUS_CANCELLED)
                    ->setParameter('startTime', $startTime)
                    ->setParameter('endTime', $endTime);
            }
        }

        if (!is_null($minBasePrice) || !is_null($maxBasePrice)) {
            $query->leftJoin('SandboxApiBundle:Product\ProductLeasingSet', 'ls', 'WITH', 'ls.product = p.id');

            if ($minBasePrice) {
                $query->andWhere('ls.basePrice >= :minBasePrice')
                    ->setParameter('minBasePrice', $minBasePrice);
            }

            if ($maxBasePrice) {
                $query->andWhere('ls.basePrice <= :maxBasePrice')
                    ->setParameter('maxBasePrice', $maxBasePrice);
            }
        }

        if (!is_null($roomTypeTags) && !empty($roomTypeTags)) {
            $query->leftJoin('SandboxApiBundle:Room\RoomTypeTags', 'rtt', 'WITH', 'rtt.tagKey = r.typeTag')
                ->andWhere('rtt.id IN (:typeTags)')
                ->setParameter('typeTags', $roomTypeTags);
        }

        if (!is_null($search)) {
            $query->leftJoin('SandboxApiBundle:SalesAdmin\SalesCompany',
                'sc',
                'WITH',
                'sc.id = b.companyId'
            )
                ->andWhere('(
                    sc.name LIKE :search
                    OR b.name LIKE :search
                    OR r.name LIKE :search
                )')
                ->setParameter('search', '%'.$search.'%');
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $buildingIds
     * @param $minAllowedPeople
     * @param $maxAllowedPeople
     * @param $startDate
     * @param $endDate
     * @param $type
     * @param $includeIds
     * @param $excludeIds
     * @param $unit
     * @param $isFavorite
     *
     * @return array
     */
    public function getWorkspaceProductsForClientCommunities(
        $userId,
        $buildingIds,
        $minAllowedPeople,
        $maxAllowedPeople,
        $startDate,
        $endDate,
        $startTime,
        $type,
        $includeIds,
        $excludeIds,
        $unit,
        $isFavorite,
        $minBasePrice,
        $maxBasePrice,
        $roomTypeTags,
        $search
    ) {
        $now = new \DateTime();

        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id, r.allowedPeople')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = r.buildingId')
            ->where('r.type = :type')
            ->andWhere('p.visible = :visible')
            ->andWhere('p.startDate <= :now AND p.endDate >= :now')
            ->setParameter('type', $type)
            ->setParameter('visible', true)
            ->setParameter('now', $now);

        if (!is_null($includeIds) && !empty($includeIds)) {
            $query->andWhere('b.companyId IN (:includeIds)')
                ->setParameter('includeIds', $includeIds);
        }

        if (!is_null($excludeIds) && !empty($excludeIds)) {
            $query->andWhere('b.companyId NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }

        if (!is_null($userId)) {
            $query->andWhere('p.visibleUserId = :userId OR p.private = :private')
                ->setParameter('private', false);

            if ($isFavorite) {
                $query->leftJoin('SandboxApiBundle:User\UserFavorite', 'uf', 'WITH', 'uf.objectId = p.id')
                    ->andWhere('uf.userId = :userId')
                    ->andWhere('uf.object = :product')
                    ->setParameter('product', UserFavorite::OBJECT_PRODUCT);
            }

            $query->setParameter('userId', $userId);
        } else {
            $query->andWhere('p.private = :private')
                ->setParameter('private', false);
        }

        $query = $this->getProductsByTimeQuery(
            $query,
            $startDate,
            $endDate,
            $startTime,
            Room::TYPE_DESK
        );

        if (!is_null($buildingIds)) {
            $query = $query->andWhere('r.building IN (:buildingIds)')
                ->setParameter('buildingIds', $buildingIds);
        }

        if (!is_null($minAllowedPeople) && !empty($minAllowedPeople)) {
            $query = $query->andWhere('r.allowedPeople >= :minAllowedPeople')
                ->setParameter('minAllowedPeople', $minAllowedPeople);
        }

        if (!is_null($maxAllowedPeople) && !empty($maxAllowedPeople)) {
            $query->andWhere('r.allowedPeople <= :maxAllowedPeople')
                ->setParameter('maxAllowedPeople', $maxAllowedPeople);
        }

        if (!is_null($unit) || !is_null($minBasePrice) || !is_null($maxBasePrice)) {
            $query->leftJoin('SandboxApiBundle:Product\ProductLeasingSet', 'ls', 'WITH', 'ls.product = p.id');

            if (!is_null($unit)) {
                $query->andWhere('ls.unitPrice = :unit')
                    ->setParameter('unit', $unit);
            }

            if ($minBasePrice) {
                $query->andWhere('ls.basePrice >= :minBasePrice')
                    ->setParameter('minBasePrice', $minBasePrice);
            }

            if ($maxBasePrice) {
                $query->andWhere('ls.basePrice <= :maxBasePrice')
                    ->setParameter('maxBasePrice', $maxBasePrice);
            }
        }

        if (!is_null($roomTypeTags) && !empty($roomTypeTags)) {
            $query->leftJoin('SandboxApiBundle:Room\RoomTypeTags', 'rtt', 'WITH', 'rtt.tagKey = r.typeTag')
                ->andWhere('rtt.id IN (:typeTags)')
                ->setParameter('typeTags', $roomTypeTags);
        }

        if (!is_null($search)) {
            $query->leftJoin('SandboxApiBundle:SalesAdmin\SalesCompany',
                'sc',
                'WITH',
                'sc.id = b.companyId'
            )
                ->andWhere('(
                    sc.name LIKE :search
                    OR b.name LIKE :search
                    OR r.name LIKE :search
                )')
                ->setParameter('search', '%'.$search.'%');
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $buildingIds
     * @param $minAllowedPeople
     * @param $maxAllowedPeople
     * @param $startDate
     * @param $endDate
     * @param $includeIds
     * @param $excludeIds
     * @param $isFavorite
     * @param $minBasePrice
     * @param $maxBasePrice
     *
     * @return array
     */
    public function getOfficeProductsForClientCommunities(
        $userId,
        $buildingIds,
        $minAllowedPeople,
        $maxAllowedPeople,
        $startDate,
        $endDate,
        $startTime,
        $includeIds,
        $excludeIds,
        $isFavorite,
        $minBasePrice,
        $maxBasePrice,
        $roomTypeTags,
        $search
    ) {
        $now = new \DateTime();

        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = r.buildingId')
            ->where('r.type = :office')
            ->andWhere('p.visible = :visible')
            ->andWhere('p.startDate <= :now AND p.endDate >= :now')
            ->setParameter('office', Room::TYPE_OFFICE)
            ->setParameter('visible', true)
            ->setParameter('now', $now);

        if (!is_null($includeIds) && !empty($includeIds)) {
            $query->andWhere('b.companyId IN (:includeIds)')
                ->setParameter('includeIds', $includeIds);
        }

        if (!is_null($excludeIds) && !empty($excludeIds)) {
            $query->andWhere('b.companyId NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }

        if (!is_null($userId)) {
            $query->andWhere('p.visibleUserId = :userId OR p.private = :private')
                ->setParameter('private', false);

            if ($isFavorite) {
                $query->leftJoin('SandboxApiBundle:User\UserFavorite', 'uf', 'WITH', 'uf.objectId = p.id')
                    ->andWhere('uf.userId = :userId')
                    ->andWhere('uf.object = :product')
                    ->setParameter('product', UserFavorite::OBJECT_PRODUCT);
            }

            $query->setParameter('userId', $userId);
        } else {
            $query->andWhere('p.private = :private')
                ->setParameter('private', false);
        }

        if (!is_null($buildingIds)) {
            $query = $query->andWhere('r.building IN (:buildingIds)')
                ->setParameter('buildingIds', $buildingIds);
        }

        if (!is_null($minAllowedPeople) && !empty($minAllowedPeople)) {
            $query = $query->andWhere('r.allowedPeople >= :minAllowedPeople')
                ->setParameter('minAllowedPeople', $minAllowedPeople);
        }

        if (!is_null($maxAllowedPeople) && !empty($maxAllowedPeople)) {
            $query = $query->andWhere('r.allowedPeople <= :maxAllowedPeople')
                ->setParameter('maxAllowedPeople', $maxAllowedPeople);
        }

        $query = $this->getProductsByTimeQuery(
            $query,
            $startDate,
            $endDate,
            $startTime,
            Room::TYPE_OFFICE
        );

        if (!is_null($minBasePrice) || !is_null($maxBasePrice)) {
            $query->leftJoin('SandboxApiBundle:Product\ProductLeasingSet', 'ls', 'WITH', 'ls.product = p.id')
                ->andWhere('ls.unitPrice = :unit')
                ->setParameter('unit', 'month');

            if ($minBasePrice) {
                $query->andWhere('ls.basePrice >= :minBasePrice')
                    ->setParameter('minBasePrice', $minBasePrice);
            }

            if ($maxBasePrice) {
                $query->andWhere('ls.basePrice <= :maxBasePrice')
                    ->setParameter('maxBasePrice', $maxBasePrice);
            }
        }

        if (!is_null($roomTypeTags) && !empty($roomTypeTags)) {
            $query->leftJoin('SandboxApiBundle:Room\RoomTypeTags', 'rtt', 'WITH', 'rtt.tagKey = r.typeTag')
                ->andWhere('rtt.id IN (:typeTags)')
                ->setParameter('typeTags', $roomTypeTags);
        }

        if (!is_null($search)) {
            $query->leftJoin('SandboxApiBundle:SalesAdmin\SalesCompany',
                'sc',
                'WITH',
                'sc.id = b.companyId'
                )
                ->andWhere('(
                    sc.name LIKE :search
                    OR b.name LIKE :search
                    OR r.name LIKE :search
                )')
                ->setParameter('search', '%'.$search.'%');
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $buildingIds
     * @param $userId
     * @param $limit
     * @param $offset
     * @param $includeIds
     *
     * @return array
     */
    public function getAllProductsForCommunities(
        $buildingIds,
        $userId,
        $limit,
        $offset,
        $includeIds,
        $recommend
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->where('p.visible = TRUE')
            ->andWhere('p.isDeleted = FALSE')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
        ;

        if (!is_null($buildingIds)) {
            $query->andWhere('r.buildingId IN (:buildingIds)')
                ->setParameter('buildingIds', $buildingIds);
        }

        if ($recommend) {
            $query->orderBy('p.salesRecommend', 'DESC')
                ->addOrderBy('p.salesSortTime', 'DESC')
                ->addOrderBy('p.creationDate', 'DESC');
        } else {
            $query->orderBy('p.recommend', 'DESC')
                ->addOrderBy('p.creationDate', 'DESC');
        }

        if (!is_null($includeIds) && !empty($includeIds)) {
            $query->leftjoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = r.buildingId')
                ->andWhere('b.companyId IN (:ids)')
                ->setParameter('ids', $includeIds);
        }

        if (!is_null($userId)) {
            $query->andWhere('p.visibleUserId = :userId OR p.private = :private')
                ->setParameter('userId', $userId)
                ->setParameter('private', false);
        } else {
            $query->andWhere('p.private = :private')
                ->setParameter('private', false);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $productIds
     *
     * @return array
     */
    public function getMinPriceByProducts(
        $productIds
    ) {
        $idsString = '';
        foreach ($productIds as $id) {
            $idsString .= $id['id'].',';
        }
        $idsString = substr($idsString, 0, strlen($idsString) - 1);

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $stat = $connection->prepare("
            SELECT
              *
            FROM (
              SELECT
                pls.product_id,
                pls.base_price,
                pls.unit_price
              FROM product_leasing_set AS pls
              WHERE pls.product_id IN ($idsString)
                UNION
              SELECT
                prs.product_id,
                prs.base_price,
                prs.unit_price
              FROM product_rent_set AS prs
              WHERE prs.product_id IN ($idsString)
              AND prs.status = 1
            ) AS price
            ORDER BY
                CASE WHEN price.unit_price = 'hour' THEN 1 END DESC,
                CASE WHEN price.unit_price = 'day' THEN 2 END DESC,
                CASE WHEN price.unit_price = 'week' THEN 3 END DESC,
                CASE WHEN price.unit_price = 'month' THEN 4 END DESC,
                price.base_price ASC
            LIMIT 1
        ");
        $stat->execute();
        $re = $stat->fetchAll();

        return $re ? $re[0] : null;
    }

    /**
     * @param $query
     * @param $startDate
     * @param $endDate
     * @param $startTime
     *
     * @return QueryBuilder
     */
    public function getProductsByTimeQuery(
        $query,
        $startDate,
        $endDate,
        $startTime,
        $roomType
    ) {
        if (!is_null($startDate) && !is_null($endDate) && !empty($startDate) && !empty($endDate)) {
            $status = ProductOrder::STATUS_CANCELLED;

            $em = $this->getEntityManager();
            $connection = $em->getConnection();
            $stat = $connection->prepare("
                  SELECT
                      pid
                    FROM (SELECT
                        (CASE
                          WHEN (po.startDate >= $startDate AND po.endDate <= $endDate)
                            THEN DATEDIFF(po.endDate, po.startDate)
                          END) AS sum_day1,
                        (CASE
                          WHEN (po.startDate <= $startDate AND po.endDate >= $startDate)
                            THEN DATEDIFF(po.endDate, $startDate)
                          END) AS sum_day2,
                        (CASE
                          WHEN (po.startDate <= $endDate AND po.endDate >= $endDate)
                            THEN DATEDIFF($endDate, po.startDate)
                          END) AS sum_day3,
                        (CASE
                          WHEN (po.startDate >= $startDate AND po.endDate <= $endDate) OR
                               (po.startDate <= $startDate AND po.endDate >= $startDate) OR
                               (po.startDate <= $endDate AND po.endDate >= $endDate)
                            THEN
                              COUNT(id)
                          END) AS count,
                        po.productId AS pid
                      FROM product_order `po`
                      WHERE `po`.status != '$status'
                      GROUP BY po.id
                    ) AS p
                    GROUP BY pid
                    HAVING (DATEDIFF($endDate, $startDate) + 1) > (IFNULL(SUM(sum_day1), 0) + IFNULL(SUM(sum_day2), 0) + IFNULL(SUM(sum_day3), 0) + IFNULL(SUM(`count`), 0))
                    
                    UNION 

                    SELECT p.id
                    FROM product AS p
                      LEFT JOIN product_order AS po ON po.productId = p.id
                      LEFT JOIN room AS r ON r.id = p.roomId
                    WHERE po.id IS NULL OR `po`.status != '$status'
                    AND r.type = '$roomType'
                    GROUP BY p.id
                    HAVING COUNT(po.id) = 0
                    ;
              ");
            $stat->execute();
            $pids = array_map('current', $stat->fetchAll());

//            $em = $this->getEntityManager();
//            $connection = $em->getConnection();
//            $stat = $connection->prepare("
//                    SELECT p.id,r.allowedPeople
//                    FROM product AS p
//                      LEFT JOIN product_order AS po ON po.productId = p.id
//                      LEFT JOIN room AS r ON r.id = p.roomId
//                    WHERE r.type = '$roomType'
//                    AND ((po.startDate >= $startDate AND po.endDate <= $endDate) OR
//                       (po.startDate <= $startDate AND po.endDate >= $startDate) OR
//                       (po.startDate <= $endDate AND po.endDate >= $endDate))
//                    GROUP BY p.id
//                    HAVING COUNT(po.id) < r.allowedPeople
//                    ;
//              ");
//            $stat->execute();
//            $pidsTwo = array_map('current', $stat->fetchAll());

            $query->andWhere('p.startDate <= :startDate')
                ->andWhere('p.endDate >= :startDate')
                ->andWhere('p.id IN (:pids)')
//                ->andWhere('p.id IN (:pids) OR p.id IN (:pidsTwo)')
                ->setParameter('startDate', $startTime)
                ->setParameter('pids', $pids);
        }

        return $query;
    }

    /**
     * @param $query
     * @param $startDate
     * @param $endDate
     *
     * @return mixed
     */
    private function getMeetingProductsQuery(
        $query,
        $startDate,
        $endDate
    ) {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $stat = $connection->prepare("
            SELECT `pid`
            FROM (
                  SELECT
                    meeting_hour.order_start_hour as starthour,
                    meeting_hour.order_end_hour AS endHour,
                    CASE WHEN `po`.`startDate` >= meeting_hour.order_start_hour AND `po`.`endDate` <= meeting_hour.order_end_hour
                      THEN UNIX_TIMESTAMP(`po`.`endDate`) -  UNIX_TIMESTAMP(`po`.`startDate`) END AS `sum_day1`,
                    CASE WHEN `po`.`startDate` <= meeting_hour.order_start_hour AND `po`.`endDate` >= meeting_hour.order_start_hour
                      THEN UNIX_TIMESTAMP(`po`.`endDate`) - UNIX_TIMESTAMP($startDate) END AS `sum_day2`,
                    CASE WHEN `po`.`startDate` <= meeting_hour.order_end_hour AND `po`.`endDate` >= meeting_hour.order_end_hour
                      THEN UNIX_TIMESTAMP(meeting_hour.order_end_hour) - UNIX_TIMESTAMP(`po`.`startDate`) END AS `sum_day3`,
                    `po`.productId AS `pid`
                    FROM product_order AS `po`
                      LEFT JOIN (
                        SELECT
                        po.productId,
                        CASE
                          WHEN TIME($startDate) < m.endHour AND TIME($endDate) > m.endHour AND m.startHour < TIME($startDate)
                            THEN $startDate
                          WHEN TIME($startDate) < m.endHour AND TIME($endDate) > m.endHour AND m.startHour >= TIME($startDate)
                            THEN CONCAT(DATE($startDate),' ',m.startHour)
                          WHEN TIME($endDate) < m.endHour AND TIME($endDate) > m.startHour AND m.startHour >= TIME($startDate)
                            THEN CONCAT(DATE($startDate),' ',m.startHour)
                          WHEN TIME($startDate) > m.startHour AND TIME($endDate) < m.endHour
                            THEN $startDate
                        END AS order_start_hour,
                        CASE
                          WHEN TIME($startDate) < m.endHour AND TIME($endDate) > m.endHour
                            THEN CONCAT(DATE($startDate),' ',m.endHour)
                          WHEN TIME($startDate) < m.endHour AND TIME($endDate) > m.endHour AND m.startHour >= TIME($startDate)
                            THEN CONCAT(DATE($startDate),' ',m.startHour)
                          WHEN TIME($endDate) < m.endHour AND TIME($endDate) > m.startHour AND m.startHour >= TIME($startDate)
                            THEN $endDate
                          WHEN TIME($startDate) > m.startHour AND TIME($endDate) < m.endHour
                            THEN $endDate
                        END AS order_end_hour
                      FROM product_order AS po
                        LEFT JOIN product AS p ON p.id = po.productId
                        LEFT JOIN room AS r ON r.id = p.roomId
                        LEFT JOIN room_meeting AS m ON m.roomId = r.id
                      WHERE po.status != 'cancelled'
                      AND r.type = 'meeting'
                      GROUP BY po.productId
                      HAVING order_start_hour IS NOT NULL AND order_end_hour IS NOT NULL
                        ) AS meeting_hour ON meeting_hour.productId = po.productId
                      WHERE meeting_hour.order_start_hour IS NOT NULL AND meeting_hour.order_end_hour IS NOT NULL
                      AND po.status != 'cancelled'
                    GROUP BY `po`.id
            ) `po_view`
            GROUP BY `pid` DESC
            HAVING (UNIX_TIMESTAMP($endDate) - UNIX_TIMESTAMP($startDate)) > IFNULL(SUM(`sum_day1`), 0) + IFNULL(SUM(`sum_day2`), 0) + IFNULL(SUM(`sum_day3`), 0)
            
            UNION 

            SELECT p.id AS pid
            FROM product AS p
              LEFT JOIN product_order AS po ON po.productId = p.id
              LEFT JOIN room AS r ON r.id = p.roomId
            WHERE po.id IS NULL
            AND r.type = 'meeting'
              ");
        $stat->execute();
        $pids = array_map('current', $stat->fetchAll());

        $query->andWhere('p.id IN (:pids)')
            ->setParameter('pids', $pids);

        return $query;
    }

    /**
     * @param $query
     * @param $startDate
     * @param $endDate
     *
     * @return mixed
     */
    private function getOthersProductsQuery(
        $query,
        $startDate,
        $endDate
    ) {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $stat = $connection->prepare("
                SELECT `pid`
                FROM (
                SELECT
                        CASE WHEN `po`.`startDate` >= $startDate AND `po`.`endDate` <= $endDate
                          THEN UNIX_TIMESTAMP(`po`.`endDate`) -  UNIX_TIMESTAMP(`po`.`startDate`) END AS `sum_day1`,
                        CASE WHEN `po`.`startDate` <= $startDate AND `po`.`endDate` >= $startDate
                          THEN UNIX_TIMESTAMP(`po`.`endDate`) - UNIX_TIMESTAMP($startDate) END AS `sum_day2`,
                        CASE WHEN `po`.`startDate` <= $endDate AND `po`.`endDate` >= $endDate
                          THEN UNIX_TIMESTAMP($endDate) - UNIX_TIMESTAMP(`po`.`startDate`) END AS `sum_day3`,
                        `po`.productId AS `pid`
                        FROM product_order AS `po`
                        GROUP BY `po`.id
                ) `po_view`
                GROUP BY `pid`
                HAVING UNIX_TIMESTAMP($endDate) - UNIX_TIMESTAMP($startDate) > IFNULL(SUM(`sum_day1`), 0) + IFNULL(SUM(`sum_day2`), 0) + IFNULL(SUM(`sum_day3`), 0);
                
                UNION 

                SELECT p.id AS pid
                FROM product AS p
                  LEFT JOIN product_order AS po ON po.productId = p.id
                  LEFT JOIN room AS r ON r.id = p.roomId
                WHERE po.id IS NULL
                AND r.type = 'others'
              ");
        $stat->execute();
        $pids = array_map('current', $stat->fetchAll());

        $query->andWhere('p.id IN (:pids)')
            ->setParameter('pids', $pids);

        return $query;
    }

    /**
     * @param $company
     * @param $building
     *
     * @return array
     */
    public function findProductIdsByCompanyAndBuilding(
        $company,
        $building = null
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('
                    p.id,
                    p.visible,
                    r.id as room_id,
                    r.name as room_name,
                    r.type as room_type,
                    r.allowedPeople as allowed_people,
                    r.area,
                    r.typeTag as type_tag,
                    b.description as building_name,
                    rm.startHour as start_hour,
                    rm.endHour as end_hour
                ')
            ->leftJoin('p.room', 'r')
            ->leftJoin('r.building', 'b')
            ->leftJoin('SandboxApiBundle:Room\RoomMeeting', 'rm', 'WITH', 'r.id = rm.room')
            ->where('p.isDeleted = FALSE')
            ->andWhere('r.isDeleted = FALSE')
            ->andWhere('b.company = :company')
            ->andWhere('r.type = :type')
            ->setParameter('company', $company)
            ->setParameter('type', Room::TYPE_OFFICE);

        if ($building) {
            $query->andWhere('b.id = :building')
                ->setParameter('building', $building);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function findProductByProductId(
        $productId
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('
                    p.id,
                    p.visible,
                    r.id as room_id,
                    r.name as room_name,
                    r.type as room_type,
                    r.allowedPeople as allowed_people,
                    r.area,
                    r.typeTag as type_tag,
                    r.description as description,
                    b.id as building_id,
                    b.name as building_name,
                    rc.name as city_name,
                    rm.startHour as start_hour,
                    rm.endHour as end_hour
                ')
            ->leftJoin('p.room', 'r')
            ->leftJoin('r.building', 'b')
            ->leftJoin('SandboxApiBundle:Room\RoomMeeting', 'rm', 'WITH', 'r.id = rm.room')
            ->leftJoin('SandboxApiBundle:Room\RoomCity','rc','WITH','rc.id = b.cityId')
            ->where('p.id = :id')
            ->setParameter('id', $productId);

        $result = $query->getQuery()->getSingleResult();

        return $result;
    }

    /**
     * @param $buildingId
     *
     * @return array
     */
    public function searchLeasesProducts(
        $buildingId
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('
                p.id AS product_id,
                r.name AS room_name,
                r.allowedPeople as allowed_people,
                r.area,
                r.id as room_id
            ')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Product\ProductRentSet', 'prs', 'WITH', 'prs.product = p.id')
            ->where('p.visible = TRUE')
            ->andWhere('p.isDeleted = FALSE')
            ->andWhere('prs.id IS NOT NULL');

        if (!is_null($buildingId)) {
            $query->andWhere('r.buildingId = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        return $query->getQuery()->getResult();
    }
}
