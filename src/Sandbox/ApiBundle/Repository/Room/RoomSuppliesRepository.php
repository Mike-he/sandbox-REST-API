<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;

class RoomSuppliesRepository extends EntityRepository
{
    public function deleteRoomSuppliesByIds(
        $room,
        $ids
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:Room\RoomSupplies r
                    WHERE r.room = :room AND r.suppliesId IN (:ids)
                ')
            ->setParameter('room', $room)
            ->setParameter('ids', $ids);

        $query->execute();
    }
}
