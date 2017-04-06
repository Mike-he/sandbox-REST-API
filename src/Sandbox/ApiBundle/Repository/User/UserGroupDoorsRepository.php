<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserGroupDoorsRepository extends EntityRepository
{
    /**
     * @param $buildingId
     *
     * @return array
     */
    public function getMembershipCard(
        $buildingId
    ) {
        $query = $this->createQueryBuilder('d')
            ->select('DISTINCT(d.card)')
            ->where('d.card IS NOT NULL');

        if (!is_null($buildingId)) {
            $query->andWhere('d.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        $result = $query->getQuery()->getResult();
        $result = array_map('current', $result);

        return $result;
    }
}