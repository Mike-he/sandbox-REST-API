<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;

class EventOrderCheckRepository extends EntityRepository
{
    /**
     * @param $eventId
     * @param $userId
     *
     * @return int
     */
    public function countEventOrderCheck(
        $eventId,
        $userId
    ) {
        $query = $this->createQueryBuilder('eoc')
            ->select('COUNT(*)')
            ->where('eoc.eventId = :eventId')
            ->andWhere('eoc.userId = :userId')
            ->setParameter('eventId', $eventId)
            ->setParameter('userId', $userId);

        return (int) $query->getQuery()->getSingleScalarResult();
    }
}
