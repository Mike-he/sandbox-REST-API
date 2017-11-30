<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;

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
}
