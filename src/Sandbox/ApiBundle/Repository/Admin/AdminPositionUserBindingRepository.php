<?php

namespace Sandbox\ApiBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;

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

    /**
     * @param $admin
     * 
     * @return array
     */
    public function findPositionByAdmin(
        $admin
    ) {
        return $this->createQueryBuilder('pb')
            ->select('p.salesCompanyId, p.id, p.platform, p.name')
            ->leftJoin('pb.position', 'p')
            ->leftJoin('p.salesCompany', 'c')
            ->where('c.banned = 0')
            ->orWhere('p.platform = \'official\'')
            ->andWhere('pb.userId = :admin')
            ->setParameter('admin', $admin)
            ->getQuery()
            ->getResult();
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
            ->select('p.userId')
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
            //todo...
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
            ->where('p.userId = :user')
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

    /**
     * @param $user
     * @param $platform
     * @param $companyId
     *
     * @return array
     */
    public function getBindUserInfo(
        $user,
        $platform,
        $companyId
    ) {
        $query = $this->createQueryBuilder('pb')
            ->leftJoin('pb.position', 'p')
            ->where('pb.userId = :user')
            ->setParameter('user', $user);

        if ($platform == AdminPosition::PLATFORM_OFFICIAL) {
            $query->andWhere('p.platform = :platform')
                ->setParameter('platform', $platform);
        } else {
            if (is_null($companyId) || empty($companyId)) {
                return array();
            }

            $query->andWhere('p.platform = :platform')
                ->setParameter('platform', $platform);

            $query->andWhere('p.salesCompanyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $user
     * @param $platform
     * @param $companyId
     *
     * @return array
     */
    public function getBindBuilding(
        $user,
        $platform,
        $companyId
    ) {
        $query = $this->createQueryBuilder('pb')
            ->select('pb.buildingId')
            ->leftJoin('pb.position', 'p')
            ->where('pb.buildingId is not null')
            ->andWhere('pb.userId = :user')
            ->setParameter('user', $user);

        if ($platform == AdminPosition::PLATFORM_OFFICIAL) {
            return array();
        } else {
            if (is_null($companyId) || empty($companyId)) {
                return array();
            }

            $query->andWhere('p.platform = :platform')
                ->setParameter('platform', $platform);

            $query->andWhere('p.salesCompanyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        $query->groupBy('pb.buildingId');

        return $query->getQuery()->getResult();
    }
}
