<?php

namespace Sandbox\ApiBundle\Repository\Product;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;

class ProductRepository extends EntityRepository
{
    public function getProductsForClient(
        $roomType,
        $cityId,
        $buildingId,
        $startTime,
        $endTime,
        $allowedPeople,
        $userId,
        $startHour,
        $endHour,
        $limit,
        $offset
    ) {
        $typeCondition = 'r.type = \''.$roomType.'\'';
        if ($roomType === 'workspace') {
            $typeCondition = 'r.type = \'fixed\' OR r.type = \'flexible\'';
        }

        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId');

        if ($roomType === 'meeting') {
            $query = $query->leftJoin('SandboxApiBundle:Room\RoomMeeting', 'm', 'WITH', 'r.id = m.room');
        }

        // condition
        $query = $query->where('p.visibleUserId = :userId OR p.private = :private')
            ->andWhere('p.visible = true');
        if (!is_null($roomType)) {
            $query = $query->andWhere($typeCondition);
        }
        if (!is_null($cityId)) {
            $query = $query->andWhere('r.city = :cityId');
        }
        if (!is_null($buildingId)) {
            $query = $query->andWhere('r.building = :buildingId');
        }
        if (!is_null($allowedPeople)) {
            $query = $query->andWhere('r.allowedPeople >= :allowedPeople');
        }
        if ($roomType === 'meeting' && !is_null($startTime)) {
            $query = $query->andWhere('m.startHour <= :startHour AND m.endHour >= :endHour');
        }
        if (!is_null($startTime)) {
            $query = $query->andWhere('p.startDate <= :startTime')
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
                );
        }
        if (!is_null($cityId)) {
            $query = $query->setParameter('cityId', $cityId);
        }
        if (!is_null($buildingId)) {
            $query = $query->setParameter('buildingId', $buildingId);
        }
        if (!is_null($allowedPeople)) {
            $query = $query->setParameter('allowedPeople', $allowedPeople);
        }
        if (!is_null($startTime)) {
            $query = $query->setParameter('startTime', $startTime)
                ->setParameter('endTime', $endTime);
        }

        $query = $query->setParameter('private', false)
            ->setParameter('userId', $userId);

        if ($roomType === 'meeting' && !is_null($startTime)) {
            $query = $query->setParameter('startHour', $startHour)
                ->setParameter('endHour', $endHour);
        }

        // paging
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
