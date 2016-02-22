<?php

namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;

class SalesAdminPermissionMapRepository extends EntityRepository
{
    /**
     * @param $adminId
     * @param $permissions
     *
     * @return array
     */
    public function getMySalesBuildings(
        $adminId,
        $permissions
    ) {
        $query = $this->createQueryBuilder('spm')
            ->select('DISTINCT spm.buildingId')
            ->where('spm.adminId = :adminId')
            ->andWhere('spm.opLevel >= :opLevel')
            ->andWhere('spm.permissionId IN (:permissions)')
            ->setParameter('adminId', $adminId)
            ->setParameter('opLevel', AdminPermissionMap::OP_LEVEL_VIEW)
            ->setParameter('permissions', $permissions);

        return $query->getQuery()->getResult();
    }
}
