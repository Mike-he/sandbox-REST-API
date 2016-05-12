<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventRegistration;

class EventRepository extends EntityRepository
{
    /**
     * @param string $status
     *
     * @return array
     */
    public function getEvents(
        $status,
        $visible
    ) {
        $query = $this->createQueryBuilder('e')
            ->select('
                e as event,
                r.name as room_name,
                r.number as room_number
            ')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = e.roomId')
            ->where('e.isDeleted = FALSE');

        // filter by status
        $now = new \DateTime('now');
        if ($status == Event::STATUS_ONGOING) {
            $query->andwhere('e.eventEndDate >= :now')
                ->setParameter('now', $now);
        } elseif ($status == Event::STATUS_END) {
            $query->andwhere('e.eventEndDate < :now')
                ->setParameter('now', $now);
        } elseif ($status == Event::STATUS_SAVED) {
            $query->andWhere('e.isSaved = TRUE');
        }

        // filter by visible
        if (!is_null($visible)) {
            $query->andWhere('e.visible = :visible')
                ->setParameter('visible', $visible);
        }

        $query->orderBy('e.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getAllClientEvents(
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('e')
            ->where('e.isDeleted = FALSE')
            ->andWhere('e.visible = TRUE');

        $query->orderBy('e.creationDate', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    /**
     * @param int $userId
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getMyClientEvents(
        $userId,
        $limit,
        $offset
    ) {
        $queryStr = '
                SELECT e
                FROM SandboxApiBundle:Event\Event e
                LEFT JOIN SandboxApiBundle:Event\EventRegistration er WITH er.eventId = e.id
                WHERE e.isDeleted = FALSE
                AND e.visible = TRUE
                AND er.userId = :userId
                AND
                (
                    (
                        e.verify = FALSE
                    ) OR (
                        e.verify = TRUE
                        AND er.status = :accepted
                    )
                )
                ORDER BY er.creationDate DESC
        ';

        $query = $this->getEntityManager()
            ->createQuery($queryStr)
            ->setParameter('userId', $userId)
            ->setParameter('accepted', EventRegistration::STATUS_ACCEPTED)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getResult();
    }
}
