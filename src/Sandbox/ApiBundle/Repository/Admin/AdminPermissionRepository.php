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
        $platform,
        $salesCompanyId = null
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
                ->andWhere('ep.salesCompanyId = :salesCompanyId')
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
    public function findAdminPermissionsByAdminAndPlatform(
        $admin,
        $platform,
        $salesCompanyId = null
    ) {
        $excludePermissionIds = $this->findAdminExcludePermissionIds($platform, $salesCompanyId);

        $positions = $this->getEntityManager()
        ->createQueryBuilder()
            ->select('p.id')
            ->from('SandboxApiBundle:Admin\AdminPositionUserBinding', 'b')
            ->join('b.position', 'p')
            ->where('b.userId = :admin')
            ->andWhere('p.platform = :platform')
            ->setParameter('platform', $platform)
            ->setParameter('admin', $admin);

        $clonePositions = clone $positions;

        $superAdminPosition = $positions
            ->andWhere('p.isSuperAdmin = 1')
            ->getQuery()
            ->getResult();

        // if it's a super admin, return all permissions
        if (!empty($superAdminPosition)) {
            $permission = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('ap.id, ap.name, ap.key, ap.maxOpLevel as op_level')
                ->from('SandboxApiBundle:Admin\AdminPermission', 'ap')
                ->where('ap.platform = :platform')
                ->setParameter('platform', $platform);

            if (!empty($excludePermissionIds)) {
                $permission->andWhere('ap.id NOT IN (:excludePermissionIds)')
                    ->setParameter('excludePermissionIds', $excludePermissionIds);
            }

            return $permission->getQuery()->getResult();
        }

        // if it's not a super admin
        $clonePositions->andWhere('p.isSuperAdmin = 0');

        if ($platform !== 'official') {
            $positionIds = $clonePositions
                ->andWhere('p.salesCompanyId = :salesCompanyId')
                ->setParameter('salesCompanyId', $salesCompanyId)
                ->getQuery()
                ->getResult();
        } else {
            $positionIds = $clonePositions->getQuery()->getResult();
        }

        $positionIds = array_map('current', $positionIds);

        // get all permissions of the given positions
        $permissions = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('
                m.opLevel as op_level, 
                p.id, 
                max(p.name), 
                max(p.key), 
                max(m.positionId) as position_id'
            )
            ->from('SandboxApiBundle:Admin\AdminPositionPermissionMap', 'm')
            ->join('m.permission', 'p')
            ->where('m.positionId IN (:positionIds)')
            ->setParameter('positionIds', $positionIds);

        if (!empty($excludePermissionIds)) {
            $permissions->andWhere('p.id NOT IN (:excludePermissionIds)')
                ->setParameter('excludePermissionIds', $excludePermissionIds);
        }

        return $permissions
            ->distinct()
            ->groupBy('p.id', 'm.opLevel')
            ->getQuery()
            ->getResult();
    }

    private function findAdminExcludePermissionIds($platform, $salesCompanyId)
    {
        // filter by exclude permission ids
        $excludePermissionIdsQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('ep.permissionId')
            ->from('SandboxApiBundle:Admin\AdminExcludePermission', 'ep')
            ->where('ep.platform = :platform')
            ->setParameter('platform', $platform);

        if (!is_null($salesCompanyId)) {
            $excludePermissionIdsQuery
                ->andWhere('ep.salesCompanyId = :salesCompanyId')
                ->setParameter('salesCompanyId', $salesCompanyId);
        }

        return array_map('current', $excludePermissionIdsQuery->getQuery()->getResult());
    }
}
