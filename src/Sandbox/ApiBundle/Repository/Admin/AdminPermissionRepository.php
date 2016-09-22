<?php

namespace Sandbox\ApiBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;

class AdminPermissionRepository extends EntityRepository
{
    /**
     * @param int    $salesCompanyId
     * @param string $platform
     *
     * @return array
     */
    public function getAdminPermissions(
        $salesCompanyId,
        $platform
    ) {
        $query = $this->createQueryBuilder('p')
            ->where('p.id IS NOT NULL')
            ->orderBy('p.id', 'ASC');

        // filter by exclude permission ids
        $excludePermissionIdsQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('ep.permissionId')
            ->from('SandboxApiBundle:Admin\AdminExcludePermission', 'ep')
            ->where('ep.platform = :platform')
            ->setParameter('platform', $platform);

        if (!is_null($salesCompanyId)) {
            $excludePermissionIdsQuery
                ->where('ep.salesCompanyId = :salesCompanyId')
                ->setParameter('salesCompanyId', $salesCompanyId);
        }

        $excludePermissionIds = $excludePermissionIdsQuery->getQuery()->getResult();
        $excludePermissionIds = array_map('current', $excludePermissionIds);

        if (!empty($excludePermissionIds)) {
            $query->andWhere('p.id NOT IN (:ids)')
                ->setParameter('ids', $excludePermissionIds);
        }

        // filter by type id
        if (!is_null($platform)) {
            $query->andWhere('p.platform = :platform')
                ->setParameter('platform', $platform);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param int    $admin
     * @param string $platform
     * @param int    $salesCompanyId
     *
     * @return array
     */
    public function findAdminPermissionsByAdminAndPositions(
        $admin,
        $platform,
        $salesCompanyId = null
    ) {
        $positions = $this->getEntityManager()
        ->createQueryBuilder()
            ->select('p.id')
            ->from('SandboxApiBundle:Admin\AdminPositionUserBinding', 'b')
            ->join('b.position', 'p')
            ->where('b.userId = :admin')
            ->orderBy('p.platform', 'ASC')
            ->setParameter('admin', $admin);

        if (!is_null($salesCompanyId)) {
            $positionIds = $positions
                ->andWhere('p.salesCompanyId = :salesCompanyId')
                ->setParameter('salesCompanyId', $salesCompanyId)
                ->getQuery()->getResult();
        } else {
            $positionIds = $positions->getQuery()->getResult();
        }

        $positionIds = array_map('current', $positionIds);

        // filter by exclude permission ids
        $excludePermissionIds = array();
        if (!is_null($salesCompanyId)) {
            $excludePermissionIds = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('e.permissionId')
                ->from('SandboxApiBundle:Admin\AdminExcludePermission', 'e')
                ->where('e.salesCompanyId = :salesCompanyId')
                ->setParameter('salesCompanyId', $salesCompanyId)
                ->getQuery()
                ->getResult();
            $excludePermissionIds = array_map('current', $excludePermissionIds);
        }

        $query = $this->getEntityManager()
            ->createQueryBuilder()
                ->select('m.opLevel as op_level, p.id, p.name, p.key, m.positionId as position_id')
                ->from('SandboxApiBundle:Admin\AdminPositionPermissionMap', 'm')
                ->join('m.permission', 'p')
                ->where('m.positionId IN (:positionIds)')
                ->setParameter('positionIds', $positionIds);

        if (!empty($excludePermissionIds)) {
            $query->andWhere('p.id NOT IN (:ids)')
                ->setParameter('ids', $excludePermissionIds);
        }

        return $query
                ->distinct()
                ->groupBy('p.id', 'm.opLevel')
                ->getQuery()
                ->getResult();
    }
}
