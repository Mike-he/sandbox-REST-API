<?php

namespace Sandbox\ApiBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;

/**
 * AdminPermissionGroupRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AdminPermissionGroupRepository extends EntityRepository
{
    /**
     * @param $adminId
     * @param $platform
     * @param $salesCompanyId
     *
     * @return array
     */
    public function getMyPermissionGroups(
        $adminId,
        $platform,
        $salesCompanyId
    ) {
        $positionBindingsQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT pb.positionId')
            ->from('SandboxApiBundle:Admin\AdminPositionUserBinding', 'pb')
            ->leftJoin('pb.position', 'p')
            ->where('pb.userId = :adminId')
            ->andWhere('p.platform = :platform')
            ->setParameter('adminId', $adminId)
            ->setParameter('platform', $platform);

        if (!is_null($salesCompanyId)) {
            $positionBindingsQuery->andWhere('p.salesCompanyId = :company')
                ->setParameter('company', $salesCompanyId);
        }

        $positions = $positionBindingsQuery->getQuery()->getResult();
        $positionIds = array_map('current', $positions);

        $groupBindingsQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('
                gr.id,
                gr.groupKey as key,
                gr.groupName as name
            ')
            ->from('SandboxApiBundle:Admin\AdminPositionGroupBinding', 'pgb')
            ->leftJoin('pgb.position', 'p')
            ->leftJoin('pgb.group', 'gr')
            ->where('p.id IN (:positionIds)')
            ->groupBy('gr.id')
            ->setParameter('positionIds', $positionIds);

        return $groupBindingsQuery->getQuery()->getResult();
    }

    public function getPermissionGroupByPlatform(
        $platform
    ) {
        $query = $this->createQueryBuilder('g')
            ->select('
                g.id,
                g.groupKey as key,
                g.groupName as name
            ')
            ->where('g.platform = :platform')
            ->setParameter('platform', $platform);

        return $query->getQuery()->getResult();
    }
}
