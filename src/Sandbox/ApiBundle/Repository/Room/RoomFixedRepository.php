<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;

class RoomFixedRepository extends EntityRepository
{
    /**
     * @param RoomBuilding $building
     *
     * @return array
     */
    public function getFixedSeats(
        $room
    ) {
        $query = $this->createQueryBuilder('f')
            ->select('DISTINCT MIN(f.basePrice)')
            ->where('f.room = :room')
            ->andWhere('f.basePrice IS NOT NULL')
            ->setParameter('room', $room);

        return $query->getQuery()->getSingleScalarResult();
    }
}
