<?php

namespace Sandbox\ApiBundle\Repository\Shop;

use Doctrine\ORM\EntityRepository;

class ShopAdminRepository extends EntityRepository
{
    /**
     * @param int $companyId
     *
     * @return array
     */
    public function countShopAdmins(
        $companyId
    ) {
        $query = $this->createQueryBuilder('a')
            ->select('COUNT (a.id)')
            ->where('a.companyId = :companyId')
            ->setParameter('companyId', $companyId);

        return $query->getQuery()->getSingleScalarResult();
    }
}
