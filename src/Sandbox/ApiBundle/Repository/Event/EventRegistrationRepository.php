<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventRegistration;

class EventRegistrationRepository extends EntityRepository
{
    /**
     * @param int    $eventId
     * @param string $status
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
            ->andWhere('e.isDeleted = FALSE')
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
     * @return string
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
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = er.eventId')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'u.id = er.userId')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = er.userId')
            ->where('er.id = :registrationId')
            ->andWhere('e.id = :eventId')
            ->andWhere('e.isDeleted = FALSE')
            ->setParameter('eventId', $eventId)
            ->setParameter('registrationId', $registrationId);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $eventId
     *
     * @return string
     */
    public function getAcceptedPersonNumber(
        $eventId
    ) {
        $query = $this->createQueryBuilder('er')
            ->select('
                COUNT(er) as counts
            ')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = er.eventId')
            ->where('e.verify = TRUE')
            ->andWhere('e.id = :eventId')
            ->andWhere('er.status = :accepted')
            ->andWhere('e.isDeleted = FALSE')
            ->setParameter('eventId', $eventId)
            ->setParameter('accepted', EventRegistration::STATUS_ACCEPTED);

        return $query->getQuery()->getSingleScalarResult();
    }

    public function deleteEventRegistrations()
    {
        $now = new \DateTime();
        $start = clone $now;
        $start->modify('-15 minutes');

        $query = $this->createQueryBuilder('r')
            ->select('r.id')
            ->leftJoin('SandboxApiBundle:Event\EventOrder', 'o', 'WITH', 'r.eventId = o.eventId')
            ->where('r.userId = o.userId')
            ->andWhere('o.status = \'cancelled\'')
            ->andWhere('o.creationDate <= :start')
            ->setParameter('start', $start)
            ->getQuery();

        $registrations = $query->getResult();
        $registrationIds = array_map('current', $registrations);

        // delete event registrations
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:Event\EventRegistration r
                    WHERE r.id IN (:ids)
                '
            )
            ->setParameter('ids', $registrationIds);

        $query->execute();
    }

    /**
     * @param $eventId
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getClientEventRegistrations(
        $eventId,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('er')
            ->select('
                er.id,
                er.status,
                up.jobTitle,
                up.name as user_name,
                up.gender,
                u.phone,
                u.email
            ')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = er.eventId')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'u.id = er.userId')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = er.userId')
            ->where('e.id = :eventId')
            ->andWhere('e.isDeleted = FALSE')
            ->andWhere('er.notInList = FALSE')
            ->setParameter('eventId', $eventId);

        $query->setMaxResults($limit);
        $query->setFirstResult($offset);

        $query->orderBy('er.id', 'ASC');

        return $query->getQuery()->getResult();
    }
}
