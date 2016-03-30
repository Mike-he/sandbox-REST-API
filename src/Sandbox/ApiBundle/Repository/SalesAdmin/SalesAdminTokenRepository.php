<?php

namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;

class SalesAdminTokenRepository extends EntityRepository
{
    /**
     * @param int $adminId
     * @param int $clientId
     */
    public function deleteSalesAdminToken(
        $adminId,
        $clientId
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:SalesAdmin\SalesAdminToken at
                    WHERE at.adminId = :adminId
                    AND at.clientId = :clientId
                '
            )
            ->setParameter('adminId', $adminId)
            ->setParameter('clientId', $clientId);

        $query->execute();
    }
}
