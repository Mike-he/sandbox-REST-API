<?php

namespace Sandbox\ApiBundle\Repository\Lease;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Lease\Lease;

class LeaseRentTypesRepository extends EntityRepository
{
    /**
     * @param Lease $lease
     */
    public function getExcludeLeaseRentTypes(
        $lease
    ) {
        $selectTypes = array();

        foreach ($lease->getLeaseRentTypes() as $type) {
            array_push($selectTypes, $type->getId());
        }

        $query = $this->createQueryBuilder('t')
            ->where('t.status = TRUE');

        if (!empty($selectTypes)) {
            $query->andWhere('t.id NOT IN (:ids)')
                ->setParameter('ids', $selectTypes);
        }

        return $query->getQuery()->getResult();
    }
}
