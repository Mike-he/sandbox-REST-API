<?php

namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;

class SalesAdminRepository extends EntityRepository
{
    /**
     * @param int    $typeId
     * @param bool   $banned
     * @param string $search
     *
     * @return array
     */
    public function getSalesAdmins(
        $typeId,
        $banned,
        $search
    ) {
        $query = $this->createQueryBuilder('a');

        if (!is_null($search)) {
            $query->leftJoin('SandboxApiBundle:SalesAdmin\SalesCompany', 'c', 'WITH', 'c.id = a.companyId');
        }

        $query->where('a.typeId = :type')
            ->setParameter('type', $typeId);

        // filter by banned status
        if (!is_null($banned)) {
            $query->andWhere('a.banned = :banned')
                ->setParameter('banned', $banned);
        }

        // filter by search
        if (!is_null($search)) {
            $query->andWhere('
                    (c.name LIKE :search OR
                    c.phone LIKE :search OR
                    c.email LIKE :search)
                ')
                ->setParameter('search', '%'.$search.'%');
        }

        $query->orderBy('a.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }
}
