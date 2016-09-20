<?php

namespace Sandbox\ApiBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;

class AdminPositionUserBindingRepository extends EntityRepository
{
    /**
     * @param $userId
     * @param $positionIds
     *
     * @return array
     */
    public function getPositionBindings(
        $userId,
        $positionIds
    ) {
        $query = $this->createQueryBuilder('pb')
            ->where('pb.userId = :userId')
            ->andWhere('pb.positionId IN (:positionIds)')
            ->setParameter('userId', $userId)
            ->setParameter('positionIds', $positionIds);

        return $query->getQuery()->getResult();
    }

    public function findPositionByAdmin($admin)
    {
        $qb = $this->getEntityManager()
            ->createQuery('
                SELECT p.salesCompanyId, p.id, p.platform, p.name
                FROM SandboxApiBundle:Admin\AdminPositionUserBinding b
                JOIN b.position p
                WHERE
                    b.userId = :admin
                ORDER BY p.platform ASC
            ')
            ->setParameter('admin', $admin);

        return $qb->getResult();
    }
}
