<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;

class RoomBuildingTagBindingRepository extends EntityRepository
{
    public function findRoomBuildingTagsByBuildingId($buildingId)
    {
        return $this->createQueryBuilder('rbt')
            ->select('
                t.id,
                t.key,
                t.icon,
                t.iconWithBg
            ')
            ->leftJoin('rbt.tag', 't')
            ->where('rbt.building = :buildingId')
            ->setParameter('buildingId', $buildingId)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
