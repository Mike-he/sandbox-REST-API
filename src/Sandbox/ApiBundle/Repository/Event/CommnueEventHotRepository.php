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

    public function getCommnueHotEvents()
    {
        $query = $this->createQueryBuilder('h')
            ->leftJoin('SandboxApiBundle:Event\Event','e','WITH','h.eventId = e.id')
            ->leftJoin('SandboxApiBundle:Event\EventAttachment','ea','WITH','ea.eventId = e.id')
            ->select(
                'e.id,
                e.name,
                e.address,
                e.status,
                e.buildingId,
                ea.content,
                ea.preview
              '
            )
            ->where('1=1');

        return $query->getQuery()->getResult();
    }
}
