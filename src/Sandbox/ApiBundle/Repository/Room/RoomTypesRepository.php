<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;

class RoomTypesRepository extends EntityRepository
{
    /**
     * @param RoomBuilding $building
     *
     * @return array
     */
    public function getPresentRoomTypes(
        $building
    ) {
        $query = $this->createQueryBuilder('rt')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.type = rt.name')
            ->where('r.building = :building')
            ->setParameter('building', $building);

        return $query->getQuery()->getResult();
    }
}
