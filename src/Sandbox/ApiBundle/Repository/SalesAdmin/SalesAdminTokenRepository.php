<?php

namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;

class SalesAdminTokenRepository extends EntityRepository
{
    /**
     * @param int $adminId
     * @param int $clientId
     */
    public function deleteAdminToken(
        $adminId,
        $clientId
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:Admin\AdminToken at
                    WHERE at.adminId = :adminId
                    AND at.clientId = :clientId
                '
            )
            ->setParameter('adminId', $adminId)
            ->setParameter('clientId', $clientId);

        $query->execute();
    }
}
