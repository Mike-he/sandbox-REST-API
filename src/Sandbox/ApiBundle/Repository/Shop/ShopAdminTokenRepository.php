<?php

namespace Sandbox\ApiBundle\Repository\Shop;

use Doctrine\ORM\EntityRepository;

class ShopAdminTokenRepository extends EntityRepository
{
    /**
     * @param int $adminId
     * @param int $clientId
     */
    public function deleteShopAdminToken(
        $adminId,
        $clientId = null
    ) {
        $query = $this->createQueryBuilder('at')
            ->delete('SandboxApiBundle:Shop\ShopAdminToken', 'at')
            ->where('at.adminId = :adminId')
            ->setParameter('adminId', $adminId);

        if (!is_null($clientId)) {
            $query->andWhere('at.clientId = :clientId')
                ->setParameter('clientId', $clientId);
        }

        $query->getQuery()->execute();
    }
}
