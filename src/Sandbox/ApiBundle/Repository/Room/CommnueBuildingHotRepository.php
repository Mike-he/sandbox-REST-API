<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;

class CommnueBuildingHotRepository extends EntityRepository
{
    /**
     * @return mixed
     */
    public function countHots()
    {
        $query = $this->createQueryBuilder('cbh')
            ->select('COUNT(cbh.id)');

        return $query->getQuery()->getSingleScalarResult();
    }
}