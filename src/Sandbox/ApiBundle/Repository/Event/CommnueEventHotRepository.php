<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Event\Event;

class CommnueEventHotRepository extends EntityRepository
{
    /**
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
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'h.eventId = e.id')
            ->select(
                'e.id,
                e.name,
                e.address,
                e.status,
                e.buildingId
              '
            )
            ->where('e.commnueVisible = TRUE');

        return $query->getQuery()->getResult();
    }
}
