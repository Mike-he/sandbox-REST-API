<?php

namespace Sandbox\ApiBundle\Repository\Shop;

use Doctrine\ORM\EntityRepository;

class ShopAdminPermissionMapRepository extends EntityRepository
{
    /**
     * @param $adminId
     * @param $permissions
     * @param $opLevel
     *
     * @return array
     */
    public function getMyShops(
        $adminId,
        $permissions,
        $opLevel
    ) {
        $query = $this->createQueryBuilder('spm')
            ->select('DISTINCT spm.shopId')
            ->where('spm.adminId = :adminId')
            ->andWhere('spm.opLevel >= :opLevel')
            ->andWhere('spm.permissionId IN (:permissions)')
            ->setParameter('adminId', $adminId)
            ->setParameter('opLevel', $opLevel)
            ->setParameter('permissions', $permissions);

        return $query->getQuery()->getResult();
    }
}
