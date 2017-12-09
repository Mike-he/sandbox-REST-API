<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventAttachment;

class CommnueEventHotRepository extends EntityRepository
{
    /**
     *
     * @return mixed
     */
    public function countHots()
    {
        $query = $this->createQueryBuilder('h')
            ->select('COUNT(h.id)');

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function getCommnueHotEventsId()
    {
        $query = $this->createQueryBuilder('h')
            ->select(
                'h.eventId'
            )
            ->where('1=1');

        $ids = $query->getQuery()->getScalarResult();
        return array_unique(array_map('current',$ids));
    }
}
