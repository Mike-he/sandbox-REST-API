<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Event\Event;

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

        return $query->getQuery()->getResult();
    }

    /**
     * @param string $belong
     * @param int    $userId
     *
     * @return array
     */
    public function getClientEvents(
        $belong,
        $userId
    ) {
        $query = $this->createQueryBuilder('e');

        if ($belong == Event::EVENT_BELONG_MY) {
            $query->leftJoin('SandboxApiBundle:Event\EventRegistration', 'er', 'WITH', 'er.eventId = e.id');
        }

        $query->where('e.visible = true');

        if ($belong == Event::EVENT_BELONG_MY) {
            $query->andWhere('er.userId = :userId')
                ->setParameter('userId', $userId);
        }

        return $query->getQuery()->getResult();
    }
}
