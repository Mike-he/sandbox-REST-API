<?php

namespace Sandbox\ApiBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;

class AdminPositionUserBindingRepository extends EntityRepository
{
    /**
     * @param $userId
     * @param $positionIds
     *
     * @return array
     */
    public function getPositionBindings(
        $userId,
        $positionIds
    ) {
        $query = $this->createQueryBuilder('pb')
            ->where('pb.userId = :userId')
            ->andWhere('pb.positionId IN (:positionIds)')
            ->setParameter('userId', $userId)
            ->setParameter('positionIds', $positionIds);

        return $query->getQuery()->getResult();
    }

    public function findPositionByAdmin($admin)
    {
        $qb = $this->getEntityManager()
            ->createQuery('
                SELECT p.salesCompanyId, p.id, p.platform, p.name
                FROM SandboxApiBundle:Admin\AdminPositionUserBinding b
                JOIN b.position p
                WHERE
                    b.userId = :admin
                ORDER BY p.platform ASC
            ')
            ->setParameter('admin', $admin);

        return $qb->getResult();
    }

    /**
     * @param $positions
     * @param null $building
     * @param null $shop
     * @param $search
     *
     * @return array
     */
    public function getBindUser(
        $positions,
        $building,
        $shop,
        $search
    ) {
        $query = $this->createQueryBuilder('p')
            ->where('p.position in (:positions)')
            ->setParameter('positions', $positions);

        if (!is_null($building) && !empty($building)) {
            $query->andWhere('p.building = :building')
                ->setParameter('building', $building);
        }

        if (!is_null($shop) && !empty($shop)) {
            $query->andWhere('p.shop = :shop')
                ->setParameter('shop', $shop);
        }

        if (!is_null($search)) {
        }

        $query->groupBy('p.userId');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $user
     * @param $positions
     *
     * @return array
     */
    public function getAdminList(
        $user,
        $positions
    ) {
        $query = $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.positionId in (:positions)')
            ->setParameter('user', $user)
            ->setParameter('positions', $positions);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $building
     *
     * @return array
     */
    public function getBuildingPosition(
        $building
    ) {
        $query = $this->createQueryBuilder('p')
            ->where('p.building = :building')
            ->setParameter('building', $building)
            ->groupBy('p.position');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $building
     * @param null $position
     * 
     * @return mixed
     */
    public function countBuildingUser(
        $building,
        $position = null
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->where('p.building = :building')
            ->setParameter('building', $building);

        if (!is_null($position)) {
            $query->andWhere('p.position = :position')
                ->setParameter('position', $position);
        }

        return $query->getQuery()->getSingleScalarResult();
    }
}
