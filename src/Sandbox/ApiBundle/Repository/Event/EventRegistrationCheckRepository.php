<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;

class EventRegistrationCheckRepository extends EntityRepository
{
    /**
     * @param $eventId
     *
     * @return array
     */
    public function getEventRegistrationCheckCount(
        $eventId
    ) {
        $query = $this->createQueryBuilder('erc')
            ->select('COUNT(erc)')
            ->where('erc.eventId = :eventId')
            ->setParameter('eventId', $eventId);

        return $query->getQuery()->getSingleScalarResult();
    }
}
