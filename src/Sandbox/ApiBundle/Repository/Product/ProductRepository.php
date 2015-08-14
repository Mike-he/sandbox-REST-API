<?php

namespace Sandbox\ApiBundle\Repository\Product;

use Doctrine\ORM\EntityRepository;
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
            $query = $query
                ->andWhere('p.startDate <= :startTime AND p.endDate > :startTime')
                ->andWhere(
                    'p.id NOT IN (
                        SELECT po.productId
                        FROM SandboxApiBundle:Order\ProductOrder po
                        WHERE po.status <> \'cancelled\'
                        AND po.productId <> \'null\'
                        AND p.visible = \'true\'
                        AND po.startDate >= :currentDateStart
                        AND po.endDate <= :currentDateEnd
                        AND po.endDate > :startTime
                        GROUP BY po.productId
                        HAVING (
                            (
                                CASE WHEN MIN(po.startDate) >= :startTime
                                THEN hour((m.endHour - time(:startHour)))
                                ELSE hour((m.endHour - time(MIN(po.startDate))))
                                END
                            ) <= hour((sum(po.endDate) - sum(po.startDate)))
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
                ->andWhere('p.endDate >= :endTime')
                ->andWhere(
                    'p.id NOT IN (
                        SELECT po.productId FROM SandboxApiBundle:Order\ProductOrder po
                        WHERE po.status <> \'cancelled\'
                        AND po.productId <> \'null\'
                        AND p.visible = \'true\'
                        AND
                        (
                            (po.startDate <= :startTime AND po.endDate > :startTime) OR
                            (po.startDate < :endTime AND po.endDate >= :endTime)
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
                ->andWhere('p.endDate >= :endDate')
                ->andWhere(
                    'p.id NOT IN (
                        SELECT po.productId FROM SandboxApiBundle:Order\ProductOrder po
                        WHERE po.status <> \'cancelled\'
                        AND po.productId <> \'null\'
                        AND p.visible = \'true\'
                        AND
                        (
                            (po.startDate <= :startDate AND po.endDate > :startDate) OR
                            (po.startDate < :endDate AND po.endDate >= :endDate)
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
                ->andWhere('p.endDate >= :endDate')
                ->andWhere(
                    'p.id NOT IN (
                        SELECT po.productId FROM SandboxApiBundle:Order\ProductOrder po
                        WHERE po.status <> \'cancelled\'
                        AND po.productId <> \'null\'
                        AND p.visible = \'true\'
                        AND
                        (
                            (po.startDate <= :startDate AND po.endDate > :startDate) OR
                            (po.startDate < :endDate AND po.endDate >= :endDate)
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
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAdminProducts(
        $type,
        $city,
        $building,
        $visible
    ) {
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId');

        // filter by type
        if (!is_null($type)) {
            $query->where('r.type = :type');
            $parameters['type'] = $type;
            $notFirst = true;
        }

        // filter by visible
        if (!is_null($visible)) {
            $visibleWhere = 'p.visible = :visible';
            if ($notFirst) {
                $query->andWhere($visibleWhere);
            } else {
                $query->where($visibleWhere);
            }
            $parameters['visible'] = $visible;
            $notFirst = true;
        }

        // filter by city
        if (!is_null($city)) {
            $cityWhere = 'r.city = :city';
            if ($notFirst) {
                $query->andWhere($cityWhere);
            } else {
                $query->where($cityWhere);
            }
            $parameters['city'] = $city;
            $notFirst = true;
        }

        // filter by building
        if (!is_null($building)) {
            $buildingWhere = 'r.building = :building';
            if ($notFirst) {
                $query->andWhere($buildingWhere);
            } else {
                $query->where($buildingWhere);
            }
            $parameters['building'] = $building;
            $notFirst = true;
        }

        $query->orderBy('p.creationDate', 'DESC');

        //set all parameters
        if ($notFirst) {
            $query->setParameters($parameters);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * Search product by city, building, room name and room number.
     *
     * @param String $search
     *
     * @return \Doctrine\ORM\Query
     */
    public function searchProducts(
        $search
    ) {
        $query = $this->createQueryBuilder('p')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomCity', 'rc', 'WITH', 'r.city = rc.id')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'rb', 'WITH', 'r.building = rb.id')
            ->where('r.name LIKE :search')
            ->orWhere('r.number LIKE :search')
            ->orWhere('rc.name LIKE :search')
            ->orWhere('rb.name LIKE :search')
            ->setParameter('search', "%$search%");

        return $result = $query->getQuery()->getResult();
    }
}
