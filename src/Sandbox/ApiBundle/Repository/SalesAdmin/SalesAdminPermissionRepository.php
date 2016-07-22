<?php

namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;

class SalesAdminPermissionRepository extends EntityRepository
{
    /**
     * @param int $salesCompanyId
     * @param int $typeId
     *
     * @return array
     */
    public function getSalesAdminPermissions(
        $salesCompanyId,
        $typeId = null
    ) {
        $excludePermissionIds = $this->getEntityManager()->createQueryBuilder()
            ->select('ep.permissionId')
            ->from('SandboxApiBundle:SalesAdmin\SalesAdminExcludePermission', 'ep')
            ->where('ep.salesCompanyId = :salesCompanyId')
            ->setParameter('salesCompanyId', $salesCompanyId)
            ->getQuery()
            ->getResult();
        $excludePermissionIds = array_map('current', $excludePermissionIds);

        $query = $this->createQueryBuilder('p')
            ->where('p.id IS NOT NULL')
            ->orderBy('p.id', 'ASC');

        // filter by exclude permission ids
        if (!empty($excludePermissionIds)) {
            $query->andWhere('p.id NOT IN (:ids)')
                ->setParameter('ids', $excludePermissionIds);
        }

        // filter by type id
        if (!is_null($typeId)) {
            $query->andWhere('p.typeId = :typeId')
                ->setParameter('typeId', $typeId);
        }

        return $query->getQuery()->getResult();
    }
}
