<?php

namespace Sandbox\ApiBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;

class AdminTokenRepository extends EntityRepository
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
