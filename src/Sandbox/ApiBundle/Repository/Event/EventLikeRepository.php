<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventRegistration;

class EventLikeRepository extends EntityRepository
{
    /**
     * @param $eventId
     * @return mixed
     */
    public function getLikesCount(
        $eventId
    ) {
        $query = $this->createQueryBuilder('el')
            ->select('COUNT(el.id)')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'u.id = el.authorId')
            ->where('el.eventId = :eventId')
            ->andWhere('u.banned = FALSE')
            ->setParameter('eventId', $eventId);

        return $query->getQuery()->getSingleScalarResult();
    }
}
