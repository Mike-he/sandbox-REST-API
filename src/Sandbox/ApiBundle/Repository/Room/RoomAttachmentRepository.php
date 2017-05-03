<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;

class RoomAttachmentRepository extends EntityRepository
{
    /**
     * @param $types
     * @param $buildingId
     *
     * @return array
     */
    public function getAttachmentsByTypes(
        $types,
        $buildingId
    ) {
        $query = $this->createQueryBuilder('a')
            ->where('a.buildingId = :buildingId')
            ->setParameter('buildingId', $buildingId);

        if (!is_null($types) && !empty($types)) {
            $query->andWhere('a.roomType IN (:types)')
                ->setParameter('types', $types);
        }

        $query->andWhere('a.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }

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

    public function findAttachmentsByRoom(
        $room,
        $limit = null
    ) {
        $query = $this->createQueryBuilder('rb')
            ->select('ra.content, ra.preview')
            ->leftJoin('SandboxApiBundle:Room\RoomAttachment', 'ra', 'WITH', 'ra.id = rb.attachmentId')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = rb.room')
            ->where('r.id = :roomId')
            ->orderBy('rb.id', 'ASC')
            ->setParameter('roomId', $room);

        if (!is_null($limit)) {
            $query->setMaxResults($limit);
        }

        return $query->getQuery()->getResult();
    }
}
