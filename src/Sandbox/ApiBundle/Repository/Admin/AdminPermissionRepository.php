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
        $excludePermissionIds = $this->findAdminExcludePermissionIds($platform, $salesCompanyId);

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
     * @param string $platform
     * @param int    $salesCompanyId
     *
     * @return array
     */
    public function findSuperAdminPermissionsByPlatform(
        $platform,
        $salesCompanyId = null
    ) {
        $excludePermissionIds = $this->findAdminExcludePermissionIds($platform, $salesCompanyId);

        $permission = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('
                ap.id,
                ap.name,
                ap.key,
                ap.maxOpLevel as op_level
            ')
            ->from('SandboxApiBundle:Admin\AdminPermission', 'ap')
            ->where('ap.platform = :platform')
            ->setParameter('platform', $platform);

        if (!empty($excludePermissionIds)) {
            $permission->andWhere('ap.id NOT IN (:excludePermissionIds)')
                ->setParameter('excludePermissionIds', $excludePermissionIds);
        }

        return $permission->getQuery()->getResult();
    }

    private function findAdminExcludePermissionIds($platform, $salesCompanyId)
    {
        // filter by exclude permission ids
        $excludePermissionIdsQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('ep.permissionId')
            ->from('SandboxApiBundle:Admin\AdminExcludePermission', 'ep')
            ->where('ep.platform = :platform')
            ->andWhere('ep.permissionId IS NOT NULL')
            ->setParameter('platform', $platform);

        if (!is_null($salesCompanyId)) {
            $excludePermissionIdsQuery
                ->andWhere('(ep.salesCompanyId = :salesCompanyId OR ep.salesCompanyId IS NULL)')
                ->setParameter('salesCompanyId', $salesCompanyId);
        }

        return array_map('current', $excludePermissionIdsQuery->getQuery()->getResult());
    }
}
