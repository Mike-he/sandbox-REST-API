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
            ->setParameter('private', false)
            ->setParameter('userId', $userId);
        if (!is_null($cityId)) {
            $query = $query->andWhere('r.city = :cityId')
                ->setParameter('cityId', $cityId);
        }
        if (!is_null($buildingId)) {
            $query = $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }
        if (!is_null($allowedPeople)) {
            $query = $query->andWhere('r.allowedPeople >= :allowedPeople')
                ->setParameter('allowedPeople', $allowedPeople);
        }
        if (!is_null($startTime) && is_null($endTime)) {
            $query = $query
                //->select('sum(hour(DATE_DIFF(o.startDate, o.endDate))) as sum')
                ->select(
                    '
                        p.id,
                        SUM(hour(DATE_DIFF(o.startDate, o.endDate))) as diff,
                        hour(DATE_DIFF(m.startHour, m.endHour)) as total
                    '
                )
                ->leftJoin('SandboxApiBundle:Order\ProductOrder', 'o', 'WITH', 'o.productId = p.id')
                ->andWhere('m.startHour <= :startHour AND m.endHour > :startHour')
                ->andWhere('p.startDate <= :startTime AND p.endDate > :startTime')
//                ->andWhere(
//                    'p.id IN (
//                        SELECT po.productId AND sum(hour(DATE_DIFF(po.startDate, po.endDate))) as diffSum
//                        WHERE diffSum > 1
//                        FROM SandboxApiBundle:Order\ProductOrder po
//                        GROUP BY po.productId
//                    )'
//                )
//                        ->orWhere('o.endDate <= :startTime')
//                ->orWhere('o.startDate > :startTime')
                ->groupBy('o.productId')
                ->having('diff < total')
                ->setParameter('startTime', $startTime)
                ->setParameter('startHour', $startHour);
        }
        if (!is_null($startTime) && !is_null($endTime)) {
            $query = $query->andWhere('m.startHour <= :startHour AND m.endHour >= :endHour')
                ->andWhere('p.startDate <= :startTime')
                ->andWhere('p.endDate >= :endTime')
                ->andWhere(
                    'p.id NOT IN (
                        SELECT po.productId FROM SandboxApiBundle:Order\ProductOrder po
                        WHERE po.status <> \'cancelled\' AND
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
            ->setParameter('private', false)
            ->setParameter('userId', $userId);
        if (!is_null($cityId)) {
            $query = $query->andWhere('r.city = :cityId')
                ->setParameter('cityId', $cityId);
        }
        if (!is_null($buildingId)) {
            $query = $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }
        if (!is_null($allowedPeople)) {
            $query = $query->andWhere('r.allowedPeople >= :allowedPeople')
                ->setParameter('allowedPeople', $allowedPeople);
        }
        if (!is_null($startDate) && !is_null($endDate)) {
            $query = $query->andWhere('p.startDate <= :startDate')
                ->andWhere('p.endDate >= :endDate')
                ->andWhere(
                    'p.id NOT IN (
                        SELECT po.productId FROM SandboxApiBundle:Order\ProductOrder po
                        WHERE po.status <> \'cancelled\' AND
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
            ->setParameter('private', false)
            ->setParameter('userId', $userId);
        if (!is_null($cityId)) {
            $query = $query->andWhere('r.city = :cityId')
                ->setParameter('cityId', $cityId);
        }
        if (!is_null($buildingId)) {
            $query = $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }
        if (!is_null($allowedPeople)) {
            $query = $query->andWhere('r.allowedPeople >= :allowedPeople')
                ->setParameter('allowedPeople', $allowedPeople);
        }
        if (!is_null($startDate) && !is_null($endDate)) {
            $query = $query->andWhere('p.startDate <= :startDate')
                ->andWhere('p.endDate >= :endDate')
                ->andWhere(
                    'p.id NOT IN (
                        SELECT po.productId FROM SandboxApiBundle:Order\ProductOrder po
                        WHERE po.status <> \'cancelled\' AND
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
}
