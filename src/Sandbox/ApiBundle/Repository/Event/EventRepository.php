<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventRegistration;

class EventRepository extends EntityRepository
{
    /**
     * @param String $status
     *
     * @return array
     */
    public function getEvents(
        $status
    ) {
        $query = $this->createQueryBuilder('e')
            ->select('
                e as event,
                r.name as room_name,
                r.number as room_number
            ')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = e.roomId')
            ->where('e.visible = TRUE');

        // filter by status
        $now = new \DateTime('now');
        if ($status == Event::STATUS_ONGOING) {
            $query = $query->andwhere('e.eventEndDate >= :now')
                ->setParameter('now', $now);
        } elseif ($status == Event::STATUS_END) {
            $query = $query->andwhere('e.eventEndDate < :now')
                ->setParameter('now', $now);
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
            ->where('e.visible = true');

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
        $accepted = EventRegistration::STATUS_ACCEPTED;

        $queryStr = '
                SELECT e
                FROM SandboxApiBundle:Event\Event e
                LEFT JOIN SandboxApiBundle:Event\EventRegistration er WITH er.eventId = e.id
                WHERE (
                      e.verify = FALSE
                      AND e.visible = TRUE
                      AND er.userId = :userId
                ) OR (
                      e.verify = TRUE
                      AND e.visible = TRUE
                      AND er.userId = :userId
                      AND er.status = :accepted
                )
        ';

        $query = $this->getEntityManager()
            ->createQuery($queryStr)
            ->setParameter('userId', $userId)
            ->setParameter('accepted', $accepted)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getResult();
    }
}
