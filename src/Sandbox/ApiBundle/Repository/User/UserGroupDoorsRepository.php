<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserGroupDoorsRepository extends EntityRepository
{
    /**
     * @param $group
     * @param $card
     *
     * @return array
     */
    public function getBuildingIdsByGroup(
        $group,
        $card = null
    ) {
        $query = $this->createQueryBuilder('d')
            ->select('d.building')
            ->where('1=1');

        if ($group) {
            $query->andWhere('d.group = :group')
                ->setParameter('group', $group);
        }

        if ($card) {
            $query->andWhere('d.card = :card')
                ->setParameter('card', $card);
        }

        $query->groupBy('d.building');

        $result = $query->getQuery()->getScalarResult();
        $result = array_map('current', $result);

        return $result;
    }

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

    /**
     * @param $buildingId
     * @param $card
     *
     * @return array
     */
    public function getGroupsByBuilding(
        $buildingId,
        $card = null
    ) {
        $query = $this->createQueryBuilder('d')
            ->where('1=1');

        if (!is_null($buildingId)) {
            $query->andWhere('d.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if ($card) {
            $query->andWhere('d.card IS NOT NULL');
        }

        $query->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }
}
