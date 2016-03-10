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

class ProductRepository extends EntityRepository
{
    /**
     * @param $userId
     * @param $cityId
     * @param $buildingId
     * @param $allowedPeople
     * @param $startTime
     * @param $endTime
     * @param $startHour
     * @param $endHour
     * @param $limit
     * @param $offset
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
        $limit,
        $offset
    ) {
        $now = new \DateTime();

        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomMeeting', 'm', 'WITH', 'p.roomId = m.room')
            ->where('r.type = :type')
            ->andWhere('p.visible = :visible')
            ->andWhere('p.startDate <= :now AND p.endDate >= :now');

        if (!is_null($userId)) {
            $query->andWhere('p.visibleUserId = :userId OR p.private = :private');
        }

        $query->setParameter('type', Room::TYPE_MEETING)
            ->setParameter('visible', true)
            ->setParameter('now', $now);

        if (!is_null($userId)) {
            $query->setParameter('userId', $userId)
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

        $query = $query->orderBy('p.creationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $userId
     * @param $cityId
     * @param $buildingId
     * @param $allowedPeople
     * @param $startDate
     * @param $endDate
     * @param $limit
     * @param $offset
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
        $limit,
        $offset
    ) {
        $now = new \DateTime();

        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->where('r.type = :type')
            ->andWhere('p.visible = :visible')
            ->andWhere('p.startDate <= :now AND p.endDate >= :now');

        if (!is_null($userId)) {
            $query->andWhere('p.visibleUserId = :userId OR p.private = :private');
        }

        $query->setParameter('type', Room::TYPE_OFFICE)
            ->setParameter('visible', true)
            ->setParameter('now', $now);

        if (!is_null($userId)) {
            $query->setParameter('userId', $userId)
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

        $query = $query->orderBy('p.creationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $userId
     * @param $cityId
     * @param $buildingId
     * @param $allowedPeople
     * @param $startDate
     * @param $endDate
     * @param $limit
     * @param $offset
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
        $limit,
        $offset
    ) {
        $now = new \DateTime();

        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->where('r.type = :typeFixed OR r.type = :typeFlexible')
            ->andWhere('p.visible = :visible')
            ->andWhere('p.startDate <= :now AND p.endDate >= :now');

        if (!is_null($userId)) {
            $query->andWhere('p.visibleUserId = :userId OR p.private = :private');
        }

        $query->setParameter('typeFixed', Room::TYPE_FIXED)
            ->setParameter('typeFlexible', Room::TYPE_FLEXIBLE)
            ->setParameter('visible', true)
            ->setParameter('now', $now);

        if (!is_null($userId)) {
            $query->setParameter('userId', $userId)
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
                        p.id NOT IN (
                            SELECT po.productId FROM SandboxApiBundle:Order\ProductOrder po
                            WHERE po.status != :status
                            AND
                            (
                                (po.startDate <= :startDate AND po.endDate > :startDate) OR
                                (po.startDate < :endDate AND po.endDate >= :endDate) OR
                                (po.startDate >= :startDate AND po.endDate <= :endDate)
                            )
                        )
                        OR
                        p.id IN (
                            SELECT po.productId FROM SandboxApiBundle:Order\ProductOrder po
                            WHERE po.status  != :status
                            AND r.type = :roomType
                            AND
                            (
                                (po.startDate <= :startDate AND po.endDate > :startDate) OR
                                (po.startDate < :endDate AND po.endDate >= :endDate) OR
                                (po.startDate >= :startDate AND po.endDate <= :endDate)
                            )
                            GROUP BY po.productId
                            HAVING COUNT(po.productId) < r.allowedPeople
                        )
                    )'
                )
                ->setParameter('status', ProductOrder::STATUS_CANCELLED)
                ->setParameter('roomType', Room::TYPE_FLEXIBLE)
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        $query = $query->orderBy('p.creationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery();

        return $query->getResult();
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
        $recommend = false
    ) {
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('p')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId');

        // only needed when searching products
        if (!is_null($search)) {
            $query->leftJoin('SandboxApiBundle:Room\RoomCity', 'rc', 'WITH', 'r.city = rc.id');
            $query->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'rb', 'WITH', 'r.building = rb.id');
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
                $query->andWhere('p.isDeleted = FALSE');
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

        //Search product by city, building, room name and room number.
        if (!is_null($search)) {
            $where = '
                r.name LIKE :search OR
                r.number LIKE :search OR
                rc.name LIKE :search OR
                rb.name LIKE :search
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

    /**
     *
     */
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
        $city,
        $building
    ) {
        $queryStr = 'SELECT p FROM SandboxApiBundle:Product\Product p';

        if (!is_null($city) || !is_null($building)) {
            $queryStr = $queryStr.' LEFT JOIN Room r WITH p.roomId = r.id';
        }

        $queryStr = $queryStr.' WHERE p.recommend = :recommend';

        if (!is_null($city)) {
            $queryStr = $queryStr.' AND r.city = :city';
        }

        if (!is_null($building)) {
            $queryStr = $queryStr.' AND r.building = :building';
        }

        // operator
        $operator = '>';
        if ($action == ProductRecommendPosition::ACTION_DOWN) {
            $operator = '<';
        }
        $queryStr = $queryStr.' AND p.sortTime '.$operator.' :sortTime';

        // order by
        $orderBy = 'ASC';
        if ($action == ProductRecommendPosition::ACTION_DOWN) {
            $orderBy = 'DESC';
        }
        $queryStr = $queryStr.' ORDER BY p.sortTime '.$orderBy;

        // set parameters
        $query = $this->getEntityManager()->createQuery($queryStr);
        $query->setParameter('recommend', true);
        $query->setParameter('sortTime', $product->getSortTime());

        if (!is_null($city)) {
            $query->setParameter('city', $city);
        }

        if (!is_null($building)) {
            $query->setParameter('building', $building);
        }

        $query->setMaxResults(1);

        return $query->getSingleResult();
    }

    /**
     * @param int      $userId
     * @param RoomCity $city
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
        $limit,
        $offset,
        $recommend
    ) {
        $queryStr = 'SELECT p FROM SandboxApiBundle:Product\Product p';

        if (!is_null($city)) {
            $queryStr = $queryStr.' LEFT JOIN SandboxApiBundle:Room\Room r WITH p.roomId = r.id';
        }

        $queryStr = $queryStr.' WHERE p.visible = :visible';
        $queryStr = $queryStr.' AND p.recommend = :recommend';
        $queryStr = $queryStr.' AND (p.startDate <= :now AND p.endDate >= :now)';

        if (!is_null($userId)) {
            $queryStr = $queryStr.' AND (p.visibleUserId = :userId OR p.private = :private)';
        }

        if (!is_null($city)) {
            $queryStr = $queryStr.' AND r.city = :city';
        }

        if ($recommend) {
            $queryStr = $queryStr.' ORDER BY p.sortTime DESC';
        } else {
            $queryStr = $queryStr.' ORDER BY p.creationDate DESC';
        }

        $query = $this->getEntityManager()->createQuery($queryStr);
        $query->setParameter('visible', true);
        $query->setParameter('recommend', $recommend);
        $query->setParameter('now', new \DateTime('now'));

        if (!is_null($userId)) {
            $query->setParameter('userId', $userId);
            $query->setParameter('private', false);
        }

        if (!is_null($city)) {
            $query->setParameter('city', $city);
        }

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }

    /**
     * @param RoomCity $city
     * @param bool     $recommend
     *
     * @return int
     */
    public function getProductsRecommendCount(
        $city,
        $recommend
    ) {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->where('p.recommend = :recommend')
            ->setParameter('recommend', $recommend);

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
     * @param bool         $recommend
     *
     * @return \Doctrine\ORM\QueryBuilder
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
        $recommend = false
    ) {
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('p')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId');

        // only needed when searching products
        if (!is_null($search)) {
            $query->leftJoin('SandboxApiBundle:Room\RoomCity', 'rc', 'WITH', 'r.city = rc.id');
            $query->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'rb', 'WITH', 'r.building = rb.id');
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
        } else {
            $where = 'r.buildingId IN (:buildingIds)';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['buildingIds'] = $myBuildingIds;
            $notFirst = true;
        }

        //Search product by city, building, room name and room number.
        if (!is_null($search)) {
            $where = '
                r.name LIKE :search OR
                r.number LIKE :search OR
                rc.name LIKE :search OR
                rb.name LIKE :search
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
     *
     * @return mixed
     */
    public function countsProductByBuilding(
        $building
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->where('r.building = :building')
            ->andWhere('p.isDeleted = FALSE')
            ->setParameter('building', $building);

        return $query->getQuery()->getSingleScalarResult();
    }
}
