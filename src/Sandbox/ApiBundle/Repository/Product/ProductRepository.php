<?php

namespace Sandbox\ApiBundle\Repository\Product;

use Doctrine\ORM\EntityRepository;

class ProductRepository extends EntityRepository
{
    public function getProductsForClient(
        $roomType,
        $buildingId,
        $startTime,
        $timeUnit,
        $endTime,
        $allowedPeople,
        $userId,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('p.id')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Order\ProductOrder', 'o', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Roomcity', 'c', 'WITH', 'c.id = r.city')
            ->leftJoin('SandboxApiBundle:Room\Roombuilding', 'b', 'WITH', 'b.id = r.building')
            ->leftJoin('SandboxApiBundle:Room\Roomfloor', 'f', 'WITH', 'f.id = r.floor')
            ->where('r.type = :roomType')
            ->andWhere('p.unitPrice = :timeUnit')
            ->andWhere('p.private =: private OR p.visibleUserId =: userId')
            ->andWhere('r.building = :buildingId')
            ->andWhere('r.allowedPeople >= :allowedPeople')
            ->andWhere('p.startDate <= :startTime')
            ->andWhere('p.endDate > :endTime')
            ->andWhere('((o.startDate > :startTime AND o.startDate > :endTime) OR (o.endDate < :startTime AND o.endDate < :endTime))')
            ->setParameter('roomType', $roomType)
            ->setParameter('timeUnit', $timeUnit)
            ->setParameter('buildingId', $buildingId)
            ->setParameter('allowedPeople', $allowedPeople)
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
            ->setParameter('private', false)
            ->setParameter('userId', $userId)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery();

        return $query->getResult();
    }
}
