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
        $clientId = null
    ) {
        $query = $this->createQueryBuilder('at')
            ->delete('SandboxApiBundle:Admin\AdminToken', 'at')
            ->where('at.adminId = :adminId')
            ->setParameter('adminId', $adminId);

        if (!is_null($clientId)) {
            $query->andWhere('at.clientId = :clientId')
                ->setParameter('clientId', $clientId);
        }

        $query->getQuery()->execute();
    }
}
