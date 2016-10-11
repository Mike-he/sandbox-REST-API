<?php

namespace Sandbox\ApiBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;

class AdminPositionUserBindingRepository extends EntityRepository
{
    /**
     * @param $userId
     * @param $type
     * @param $platform
     * @param null $company
     *
     * @return array
     */
    public function getBindingsBySpecifyAdminId(
        $userId,
        $type,
        $platform,
        $company = null
    ) {
        $query = $this->createQueryBuilder('pb')
            ->select('
                p.salesCompanyId as sales_company_id, 
                p.id as position_id, 
                p.platform, 
                p.name as position_name
            ')
            ->leftJoin('pb.position', 'p', 'WITH', 'p.id = pb.positionId')
            ->where('pb.userId = :userId')
            ->andWhere('p.platform = :platform')
            ->setParameter('userId', $userId)
            ->setParameter('platform', $platform);

        if (!is_null($company)) {
            $query->andWhere('p.salesCompanyId = :company')
                ->setParameter('company', $company);
        }

        if (!is_null($type) && !empty($type)) {
            $query->leftJoin('SandboxApiBundle:Admin\AdminPositionPermissionMap', 'm', 'WITH', 'p.id = m.positionId')
                ->leftJoin('SandboxApiBundle:Admin\AdminPermission', 'ap', 'WITH', 'ap.id = m.permissionId')
                ->andWhere('ap.level = :type')
                ->setParameter('type', $type);
        }

        $query->groupBy('p.id');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $isSuperAdmin
     * @param $platform
     * @param $salesCompanyId
     *
     * @return array
     */
    public function getPositionBindingsByIsSuperAdmin(
        $userId,
        $isSuperAdmin,
        $platform,
        $salesCompanyId = null
    ) {
        $query = $this->createQueryBuilder('pb')
            ->leftJoin('SandboxApiBundle:Admin\AdminPosition', 'p', 'WITH', 'p.id = pb.positionId')
            ->where('pb.userId = :userId')
            ->andWhere('p.platform = :platform')
//            ->andWhere('p.isHidden = FALSE')
            ->andWhere('p.isSuperAdmin = :isSuperAdmin')
            ->setParameter('userId', $userId)
            ->setParameter('platform', $platform)
            ->setParameter('isSuperAdmin', $isSuperAdmin);

        if (!is_null($salesCompanyId) && $platform != AdminPosition::PLATFORM_OFFICIAL) {
            $query->andWhere('p.salesCompanyId = :salesCompanyId')
                ->setParameter('salesCompanyId', $salesCompanyId);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $positionIds
     *
     * @return array
     */
    public function getPositionBindings(
        $userId,
        $positionIds = null
    ) {
        $query = $this->createQueryBuilder('pb')
            ->where('pb.userId = :userId')
            ->setParameter('userId', $userId);

        if (!is_null($positionIds)) {
            $query->andWhere('pb.positionId IN (:positionIds)')
                ->setParameter('positionIds', $positionIds);
        }

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
            ->select('
                p.salesCompanyId as sales_company_id, 
                c.name as sales_company_name, 
                p.id as position_id, 
                p.platform, 
                p.name as position_name
            ')
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
     * @param null $users
     *
     * @return array
     */
    public function getBindUser(
        $positions,
        $building = null,
        $shop = null,
        $users = null
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('p.userId')
            ->where('1=1');

        if (!is_null($positions)) {
            $query->andWhere('p.position in (:positions)')
                ->setParameter('positions', $positions);
        }

        if (!is_null($building) && !empty($building)) {
            $query->andWhere('p.building = :building')
                ->setParameter('building', $building);
        }

        if (!is_null($shop) && !empty($shop)) {
            $query->andWhere('p.shop = :shop')
                ->setParameter('shop', $shop);
        }

        if (!is_null($users)) {
            $query->andWhere('p.userId in (:users)')
                ->setParameter('users', $users);
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
     * @param $platform
     * @param $building
     * @param null $shop
     *
     * @return array
     */
    public function getBuildingPosition(
        $platform,
        $building,
        $shop = null
    ) {
        $query = $this->createQueryBuilder('pb')
            ->leftJoin('pb.position', 'p')
            ->select('pb.positionId')
            ->where('p.platform = :platform')
            ->setParameter('platform', $platform);

        if (!is_null($building)) {
            $query->andWhere('pb.building = :building')
                ->setParameter('building', $building);
        }

        if (!is_null($shop)) {
            $query->andWhere('pb.shop = :shop')
                ->setParameter('shop', $shop);
        }

        $query->groupBy('pb.positionId');

        return $query->getQuery()->getResult();
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
            ->select('p.id')
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

        $query->groupBy('p.id');

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
            ->andWhere('p.platform = :platform')
            ->andWhere('p.salesCompanyId = :companyId')
            ->setParameter('platform', $platform)
            ->setParameter('companyId', $companyId)
            ->setParameter('user', $user);

        $query->groupBy('pb.buildingId');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $user
     * @param $platform
     * @param $companyId
     *
     * @return array
     */
    public function getBindShop(
        $user,
        $platform,
        $companyId
    ) {
        $query = $this->createQueryBuilder('pb')
            ->select('pb.shopId')
            ->leftJoin('pb.position', 'p')
            ->where('pb.shopId is not null')
            ->andWhere('pb.userId = :user')
            ->andWhere('p.platform = :platform')
            ->andWhere('p.salesCompanyId = :companyId')
            ->setParameter('platform', $platform)
            ->setParameter('companyId', $companyId)
            ->setParameter('user', $user);

        $query->groupBy('pb.shopId');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $platform
     * @param null $company
     *
     * @return array
     */
    public function getBindingsByUser(
        $userId,
        $platform,
        $company = null
    ) {
        $query = $this->createQueryBuilder('pb')
            ->leftJoin('pb.position', 'p')
            ->where('pb.userId = :userId')
            ->andWhere('p.platform = :platform')
            ->setParameter('userId', $userId)
            ->setParameter('platform', $platform);

        if (!is_null($company)) {
            $query->andWhere('p.salesCompanyId = :company')
                ->setParameter('company', $company);
        }

        return $query->getQuery()->getResult();
    }
}
