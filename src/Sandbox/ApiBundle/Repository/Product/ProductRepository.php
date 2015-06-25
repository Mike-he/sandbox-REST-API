<?php

namespace Sandbox\ApiBundle\Repository\Product;

use Doctrine\ORM\EntityRepository;

class ProductRepository extends EntityRepository
{
    public function getProductsForClient($roomType, $buildingId, $startTime, $timeUnit, $endTime, $allowedPeople)
    {
        $query = $this->createQueryBuilder('p')
            ->select('p.id')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomRentedDate', 'rd', 'WITH', 'rd.roomId = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\Roomcity', 'c', 'WITH', 'c.id = r.city')
            ->leftJoin('SandboxApiBundle:Room\Roombuilding', 'b', 'WITH', 'b.id = r.building')
            ->leftJoin('SandboxApiBundle:Room\Roomfloor', 'f', 'WITH', 'f.id = r.floor')
            ->where('r.type = :roomType')
            ->andWhere('p.unitPrice = :timeUnit')
            ->andWhere('r.building = :buildingId')
            ->andWhere('r.allowedPeople >= :allowedPeople')
            ->andWhere('((rd.startDate > :startTime AND rd.startDate > :endTime) OR (rd.endDate < :startTime AND rd.endDate < :endTime))')
            ->setParameter('roomType', $roomType)
            ->setParameter('timeUnit', $timeUnit)
            ->setParameter('buildingId', $buildingId)
            ->setParameter('allowedPeople', $allowedPeople)
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
            ->getQuery();

        return $query->getResult();
    }
}
