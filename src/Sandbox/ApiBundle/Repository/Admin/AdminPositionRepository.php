<?php

namespace Sandbox\ApiBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;
use Sandbox\AdminApiBundle\Data\Position\Position;

class AdminPositionRepository extends EntityRepository
{
    /**
     * @param $platform
     * @param $type
     * @param $companyId
     *
     * @return mixed
     */
    public function getAdminPositions(
        $platform,
        $type,
        $companyId
    ) {
        $query = $this->createQueryBuilder('p')
            ->where('p.isHidden = FALSE')
            ->andWhere('p.platform = :platform')
            ->setParameter('platform', $platform);

        if (!is_null($companyId)) {
            $query->andWhere('p.salesCompanyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        if (!is_null($type) && !empty($type)) {
            $query->leftJoin('SandboxApiBundle:Admin\AdminPositionPermissionMap', 'm', 'WITH', 'p.id = m.positionId')
                ->leftJoin('SandboxApiBundle:Admin\AdminPermission', 'ap', 'WITH', 'ap.id = m.permissionId')
                ->andWhere('ap.level = :type')
                ->setParameter('type', $type);
        }

        $query->orderBy('p.sortTime', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $platform
     * @param $companyId
     * @param null $isSuperAdmin
     * @param null $position
     * @param null $type
     *
     * @return array
     */
    public function getPositions(
        $platform,
        $companyId,
        $isSuperAdmin = null,
        $position = null,
        $type = null
    ) {
        $query = $this->createQueryBuilder('p')
            ->where('p.isHidden = FALSE')
            ->andWhere('p.platform = :platform')
            ->setParameter('platform', $platform);

        if (!is_null($isSuperAdmin)) {
            $query->andWhere('p.isSuperAdmin = :isSuperAdmin')
                ->setParameter('isSuperAdmin', $isSuperAdmin);
        }

        if (!is_null($companyId)) {
            $query->andWhere('p.salesCompanyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        if (!is_null($position)) {
            $query->andWhere('p.id = :id')
                ->setParameter('id', $position);
        }

        if (!is_null($type) && !empty($type)) {
            $query->leftJoin('SandboxApiBundle:Admin\AdminPositionPermissionMap', 'm', 'WITH', 'p.id = m.positionId')
                ->leftJoin('SandboxApiBundle:Admin\AdminPermission', 'ap', 'WITH', 'ap.id = m.permissionId')
                ->andWhere('ap.level = :type')
                ->setParameter('type', $type);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $platform
     * @param $companyId
     * @param null $isSuperAdmin
     * @param null $position
     * @param null $type
     *
     * @return array
     */
    public function getPositionIds(
        $platform,
        $companyId,
        $isSuperAdmin = null,
        $position = null,
        $type = null
    ) {
        $query = $this->createQueryBuilder('p')
            ->select('p.id')
            ->where('p.isHidden = FALSE')
            ->andWhere('p.platform = :platform')
            ->setParameter('platform', $platform);

        if (!is_null($isSuperAdmin)) {
            $query->andWhere('p.isSuperAdmin = :isSuperAdmin')
                ->setParameter('isSuperAdmin', $isSuperAdmin);
        }

        if (!is_null($companyId)) {
            $query->andWhere('p.salesCompanyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        if (!is_null($position)) {
            $query->andWhere('p.id = :id')
                ->setParameter('id', $position);
        }

        if (!is_null($type) && !empty($type)) {
            $query->leftJoin('SandboxApiBundle:Admin\AdminPositionPermissionMap', 'm', 'WITH', 'p.id = m.positionId')
                ->leftJoin('SandboxApiBundle:Admin\AdminPermission', 'ap', 'WITH', 'ap.id = m.permissionId')
                ->andWhere('ap.level = :type')
                ->setParameter('type', $type);
        }

        return $query->getQuery()->getResult();
    }

    public function findSwapPosition(
        $platform,
        $salesCompanyId,
        $sortTime,
        $action,
        $type
    ) {
        // operator and order direction
        $operator = '>';
        $direction = 'ASC';
        if (Position::ACTION_DOWN == $action) {
            $operator = '<';
            $direction = 'DESC';
        }

        $query = $this->createQueryBuilder('p')
            ->where('p.sortTime '.$operator.' :sortTime')
            ->andWhere('p.platform = :platform')
            ->andWhere('p.isHidden = FALSE')
            ->setParameter('platform', $platform)
            ->setParameter('sortTime', $sortTime)
            ->orderBy('p.sortTime', $direction)
            ->setMaxResults(1);

        if (!is_null($salesCompanyId)) {
            $query->andWhere('p.salesCompanyId = :salesCompanyId')
                ->setParameter('salesCompanyId', $salesCompanyId);
        }

        if (!is_null($type) && !empty($type)) {
            $query->leftJoin('SandboxApiBundle:Admin\AdminPositionPermissionMap', 'm', 'WITH', 'p.id = m.positionId')
                ->leftJoin('SandboxApiBundle:Admin\AdminPermission', 'ap', 'WITH', 'ap.id = m.permissionId')
                ->andWhere('ap.level = :type')
                ->setParameter('type', $type);
        }

        return $query->getQuery()->getOneOrNullResult();
    }
}
