<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;

class RoomAttachmentRepository extends EntityRepository
{
    public function deleteRoomAttachmentByIds(
        $room,
        $ids
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:Room\RoomAttachmentBinding r
                    WHERE r.room = :room AND r.attachmentId IN (:ids)
                ')
            ->setParameter('room', $room)
            ->setParameter('ids', $ids);

        $query->execute();
    }
}
