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
            ->setParameter('userId', $userId)
            ->setParameter('now', $now)
            ->getQuery();

        return $query->getResult();
    }

    public function getDoorsByBuilding(
        $userId,
        $buildingId
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('d')
            ->where('d.userId = :userId')
            ->andWhere('d.buildingId = :buildingId')
            ->andWhere('d.endDate > :now')
            ->setParameter('userId', $userId)
            ->setParameter('buildingId', $buildingId)
            ->setParameter('now', $now)
            ->getQuery();

        return $query->getResult();
    }
}
