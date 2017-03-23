<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;

class RoomBuildingServiceMemberRepository extends EntityRepository
{
    /**
     * @param RoomBuilding $building
     *
     * @return array
     */
    public function getServicesByBuilding(
        $buildingId
    ) {
        $query = $this->createQueryBuilder('s')
            ->select('DISTINCT s.tag')
            ->where('s.buildingId = :buildingId')
            ->setParameter('buildingId', $buildingId);

        return $query->getQuery()->getScalarResult();
    }
}
