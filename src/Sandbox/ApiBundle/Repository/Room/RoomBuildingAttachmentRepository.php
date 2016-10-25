<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;

class RoomBuildingAttachmentRepository extends EntityRepository
{
    public function findRoomBuildingAttachmentByBuildingId($buildingId)
    {
        return $this->createQueryBuilder('rba')
            ->select('rba.content')
            ->where('rba.buildingId = :buildingId')
            ->setParameter('buildingId', $buildingId)
            ->orderBy('rba.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
