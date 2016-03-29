<?php
namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;

class SalesUserRepository extends EntityRepository
{
    /**
     * @param $buildingIds
     *
     * @return array
     */
    public function getSalesUsers(
        $buildingIds
    ) {
        $query = $this->createQueryBuilder('su')
            ->select('su.userId')
            ->where('su.buildingId IN (:buildingIds)')
            ->setParameter('buildingIds', $buildingIds);

        return $query->getQuery()->getResult();
    }
}