<?php

namespace Sandbox\ApiBundle\Repository\Product;

use Doctrine\ORM\EntityRepository;

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
}
