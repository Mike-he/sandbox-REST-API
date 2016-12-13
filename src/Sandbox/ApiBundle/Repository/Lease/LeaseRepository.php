<?php

namespace Sandbox\ApiBundle\Repository\Lease;

use Doctrine\ORM\EntityRepository;

class LeaseRepository extends EntityRepository
{
    /**
     * @param $buildingId
     *
     * @return array
     */
    public function findLeases(
        $buildingId
    ) {
        $query = $this->createQueryBuilder('l');

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
