<?php

namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;

class SalesAdminPermissionMapRepository extends EntityRepository
{
    /**
     * @param $adminId
     * @param $permissions
     * @param $opLevel
     *
     * @return array
     */
    public function getMySalesBuildings(
        $adminId,
        $permissions,
        $opLevel
    ) {
        $query = $this->createQueryBuilder('spm')
            ->select('DISTINCT spm.buildingId')
            ->where('spm.adminId = :adminId')
            ->andWhere('spm.opLevel >= :opLevel')
            ->andWhere('spm.permissionId IN (:permissions)')
            ->setParameter('adminId', $adminId)
            ->setParameter('opLevel', $opLevel)
            ->setParameter('permissions', $permissions);

        return $query->getQuery()->getResult();
    }
}
