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
        $clientId
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:Shop\ShopAdminToken at
                    WHERE at.adminId = :adminId
                    AND at.clientId = :clientId
                '
            )
            ->setParameter('adminId', $adminId)
            ->setParameter('clientId', $clientId);

        $query->execute();
    }
}
