<?php

namespace Sandbox\ApiBundle\Repository\Door;

use Doctrine\ORM\EntityRepository;

class DoorAccessRepository extends EntityRepository
{
    public function getBuildingIds(
        $userId
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('d')
            ->select('DISTINCT d.buildingId')
            ->where('d.userId = :userId')
            ->andWhere('d.endDate > :now')
            ->andWhere('d.access = :access')
            ->setParameter('userId', $userId)
            ->setParameter('now', $now)
            ->setParameter('access', false)
            ->getQuery();

        return $query->getResult();
    }

    public function getOrdersByBuilding(
        $userId,
        $buildingId
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('d')
            ->select('DISTINCT d.orderId')
            ->where('d.userId = :userId')
            ->andWhere('d.buildingId = :buildingId')
            ->andWhere('d.endDate > :now')
            ->andWhere('d.access = :access')
            ->groupBy('d.orderId')
            ->setParameter('userId', $userId)
            ->setParameter('buildingId', $buildingId)
            ->setParameter('now', $now)
            ->setParameter('access', false)
            ->getQuery();

        return $query->getResult();
    }

    public function getDoorsByDoorId(
        $userId,
        $buildingId,
        $doorId
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('d')
            ->where('d.userId = :userId')
            ->andWhere('d.buildingId = :buildingId')
            ->andWhere('d.doorId = :doorId')
            ->andWhere('d.endDate > :now')
            ->setParameter('userId', $userId)
            ->setParameter('buildingId', $buildingId)
            ->setParameter('doorId', $doorId)
            ->setParameter('now', $now)
            ->getQuery();

        return $query->getResult();
    }

    public function getAccessByRoom(
        $userId,
        $buildingId,
        $roomId
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('d')
            ->where('d.userId = :userId')
            ->andWhere('d.buildingId = :buildingId')
            ->andWhere('d.roomId = :roomId')
            ->andWhere('d.endDate > :now')
            ->groupBy('d.orderId')
            ->setParameter('userId', $userId)
            ->setParameter('buildingId', $buildingId)
            ->setParameter('roomId', $roomId)
            ->setParameter('now', $now)
            ->getQuery();

        return $query->getResult();
    }
}
