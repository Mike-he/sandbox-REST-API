<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Event\Event;

class EventRegistrationRepository extends EntityRepository
{
    /**
     * @param int    $eventId
     * @param String $status
     *
     * @return array
     */
    public function getEventRegistrations(
        $eventId,
        $status,
        $query
    ) {
        $query = $this->createQueryBuilder('er')
            ->select('
                er.id,
                er.status,
                up.name as user_name,
                up.gender,
                u.phone,
                u.email
            ')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = er.eventId')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'u.id = er.userId')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = er.userId')
            ->where('e.id = :eventId')
            ->setParameter('eventId', $eventId);

        // filter by status
        if (!is_null($status)) {
            $query = $query->andWhere('er.status = :status')
                ->setParameter('status', $status);
        }

        $query->orderBy('er.id', 'ASC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param int $eventId
     *
     * @return array
     */
    public function getRegistrationCounts(
        $eventId
    ) {
        $query = $this->createQueryBuilder('er')
            ->select('
                count(er) as counts
            ')
            ->where('er.eventId = :eventId')
            ->setParameter('eventId', $eventId);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $eventId
     * @param int $registrationId
     *
     * @return array
     */
    public function getEventRegistration(
        $eventId,
        $registrationId
    ) {
        $query = $this->createQueryBuilder('er')
            ->select('
                er.id,
                up.name as user_name,
                up.gender,
                u.phone,
                u.email
            ')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = :eventId')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'u.id = er.userId')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = er.userId')
            ->where('er.id = :registrationId')
            ->setParameter('eventId', $eventId)
            ->setParameter('registrationId', $registrationId);

        return $query->getQuery()->getSingleResult();
    }
}
