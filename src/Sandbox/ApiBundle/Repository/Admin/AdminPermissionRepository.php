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
        if (!is_null($salesCompanyId)) {
            $excludePermissionIds = $this->getEntityManager()->createQueryBuilder()
                ->select('ep.permissionId')
                ->from('SandboxApiBundle:Admin\AdminExcludePermission', 'ep')
                ->where('ep.salesCompanyId = :salesCompanyId')
                ->setParameter('salesCompanyId', $salesCompanyId)
                ->getQuery()
                ->getResult();
            $excludePermissionIds = array_map('current', $excludePermissionIds);

            if (!empty($excludePermissionIds)) {
                $query->andWhere('p.id NOT IN (:ids)')
                    ->setParameter('ids', $excludePermissionIds);
            }
        }

        // filter by type id
        if (!is_null($platform)) {
            $query->andWhere('p.platform = :platform')
                ->setParameter('platform', $platform);
        }

        return $query->getQuery()->getResult();
    }
}
