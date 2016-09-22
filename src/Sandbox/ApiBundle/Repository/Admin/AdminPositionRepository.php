<?php

namespace Sandbox\ApiBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;

class AdminPositionRepository extends EntityRepository
{
    /**
     * @return mixed
     */
    public function getAdminPositions(
        $platform,
        $type,
        $companyId
    ) {
        $query = $this->createQueryBuilder('p')
            ->where('p.isHidden = FALSE');

        if ($platform == AdminPosition::PLATFORM_OFFICIAL) {
            $query->andWhere('p.platform = :platform')
                ->setParameter('platform', $platform);
        } else {
            if (is_null($companyId) || empty($companyId)) {
                return array();
            }

            if ($platform == AdminPosition::PLATFORM_SALES) {
                $query->andWhere('p.platform = :sales')
                    ->setParameter('sales', AdminPosition::PLATFORM_SALES);
            } elseif ($platform == AdminPosition::PLATFORM_SHOP) {
                $query->andWhere('p.platform = :platform')
                    ->setParameter('platform', $platform);
            }

            $query->andWhere('p.salesCompanyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        if (!is_null($type) && !empty($type)) {
            $query->leftJoin('SandboxApiBundle:Admin\AdminPositionPermissionMap', 'm', 'WITH', 'p.id = m.positionId')
                ->leftJoin('SandboxApiBundle:Admin\AdminPermission', 'ap', 'WITH', 'ap.id = m.permissionId')
                ->andWhere('ap.level = :type')
                ->setParameter('type', $type);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $platform
     * @param $companyId
     * @param null $isSuperAdmin
     * @param null $positionIds
     * 
     * @return array
     */
    public function getPositions(
        $platform,
        $companyId,
        $isSuperAdmin = null,
        $positionIds = null
    ) {
        $query = $this->createQueryBuilder('p')
            ->where('p.isHidden = FALSE');

        if (!is_null($isSuperAdmin)) {
            $query->andWhere('p.isSuperAdmin = :isSuperAdmin')
                ->setParameter('isSuperAdmin', $isSuperAdmin);
        }

        if ($platform == AdminPosition::PLATFORM_OFFICIAL) {
            $query->andWhere('p.platform = :platform')
                ->setParameter('platform', $platform);
        } else {
            if (is_null($companyId) || empty($companyId)) {
                return array();
            }

            $query->andWhere('p.platform = :platform')
                ->setParameter('platform', $platform);

            $query->andWhere('p.salesCompanyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        if (!is_null($positionIds)) {
            $query->andWhere('p.id in (:ids)')
                ->setParameter('ids', $positionIds);
        }

        return $query->getQuery()->getResult();
    }
}
