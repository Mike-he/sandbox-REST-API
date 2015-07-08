<?php

namespace Sandbox\ApiBundle\Repository\Product;

use Doctrine\ORM\EntityRepository;

class ProductRepository extends EntityRepository
{
    public function getProductsForClient(
        $roomType,
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
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = r.building');

        if ($roomType === 'meeting') {
            $query = $query->leftJoin('SandboxApiBundle:Room\RoomMeeting', 'm', 'WITH', 'r.id = m.room');
        }

        // condition
        $query = $query->Where('p.visibleUserId = :userId OR p.private = :private')
            ->andWhere($typeCondition)
            ->andWhere('r.building = :buildingId')
            ->andWhere('r.allowedPeople >= :allowedPeople')
            ->andWhere('p.startDate <= :startTime')
            ->andWhere('p.endDate >= :endTime');

        if ($roomType === 'meeting') {
            $query = $query->andWhere('m.startHour <= :startHour AND m.endHour >= :endHour');
        }

        $query = $query->andWhere(
                'p.id NOT IN (
                    SELECT po.productId FROM SandboxApiBundle:Order\ProductOrder po
                    WHERE po.status <> \'cancelled\' AND
                    (
                        (po.startDate <= :startTime AND po.endDate > :startTime) OR
                        (po.startDate < :endTime AND po.endDate >= :endTime)
                    )
                )'
            )
            ->setParameter('buildingId', $buildingId)
            ->setParameter('allowedPeople', $allowedPeople)
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
            ->setParameter('private', false)
            ->setParameter('userId', $userId);

        if ($roomType === 'meeting') {
            $query = $query->setParameter('startHour', $startHour)
                ->setParameter('endHour', $endHour);
        }

        // paging
        $query = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery();

        return $query->getResult();
    }
}
