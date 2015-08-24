<?php

namespace Sandbox\ApiBundle\Repository\Product;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;

class ProductRepository extends EntityRepository
{
    public function getMeetingProductsNotInOrder(

    ) {
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
        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomMeeting', 'm', 'WITH', 'p.roomId = m.room')
            ->where('p.visibleUserId = :userId OR p.private = :private')
            ->andWhere('r.type = \'meeting\'')
            ->andWhere('p.visible = :visible')
            ->setParameter('visible', true)
            ->setParameter('private', false)
            ->setParameter('userId', $userId);
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
                        WHERE po.status <> \'cancelled\'
                        AND po.productId <> \'null\'
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
                        WHERE po.status <> \'cancelled\'
                        AND po.productId <> \'null\'
                        AND
                        (
                            (po.startDate <= :startTime AND po.endDate > :startTime) OR
                            (po.startDate < :endTime AND po.endDate >= :endTime) OR
                            (po.startDate >= :startTime AND po.endDate <= :endTime)
                        )
                    )'
                )
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
        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->where('p.visibleUserId = :userId OR p.private = :private')
            ->andWhere('r.type = \'office\'')
            ->andWhere('p.visible = :visible')
            ->setParameter('visible', true)
            ->setParameter('private', false)
            ->setParameter('userId', $userId);
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
                        WHERE po.status <> \'cancelled\'
                        AND po.productId <> \'null\'
                        AND
                        (
                            (po.startDate <= :startDate AND po.endDate > :startDate) OR
                            (po.startDate < :endDate AND po.endDate >= :endDate) OR
                            (po.startDate >= :startDate AND po.endDate <= :endDate)
                        )
                    )'
                )
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
        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->where('p.visibleUserId = :userId OR p.private = :private')
            ->andWhere('r.type = \'fixed\' OR r.type = \'flexible\'')
            ->andWhere('p.visible = :visible')
            ->setParameter('visible', true)
            ->setParameter('private', false)
            ->setParameter('userId', $userId);
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
                        WHERE po.status <> \'cancelled\'
                        AND po.productId <> \'null\'
                        AND
                        (
                            (po.startDate <= :startDate AND po.endDate > :startDate) OR
                            (po.startDate < :endDate AND po.endDate >= :endDate) OR
                            (po.startDate >= :startDate AND po.endDate <= :endDate)
                        )
                    )'
                )
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
     * @param String       $type
     * @param RoomCity     $city
     * @param RoomBuilding $building
     * @param String       $sortBy
     * @param String       $direction
     * @param String       $search
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
        $search
    ) {
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('p')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId');

        //only needed when searching products
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
            $parameters['visible'] = $visible;
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

        //sort by by method
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

        //set all parameters
        if ($notFirst) {
            $query->setParameters($parameters);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param QueryBuilder $query
     * @param bool         $notFirst
     * @param String       $where
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
}
