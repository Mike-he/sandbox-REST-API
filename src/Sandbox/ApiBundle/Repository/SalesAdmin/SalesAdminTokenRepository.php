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
        $clientId = null
    ) {
        $query = $this->createQueryBuilder('at')
            ->delete('SandboxApiBundle:SalesAdmin\SalesAdminToken', 'at')
            ->where('at.adminId = :adminId')
            ->setParameter('adminId', $adminId);

        if (!is_null($clientId)) {
            $query->andWhere('at.clientId = :clientId')
                ->setParameter('clientId', $clientId);
        }

        $query->getQuery()->execute();
    }
}
