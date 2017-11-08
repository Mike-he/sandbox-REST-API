<?php

namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;

class SalesAdminRepository extends EntityRepository
{
    /**
     * @param $phone
     *
     * @return array
     */
    public function searchAdmins(
        $phone
    ) {
        $query = $this->createQueryBuilder('a')
            ->select('
                a.userId as user_id,
                a.phone
            ')
            ->where('a.phone LIKE :search')
            ->setParameter('search', '%'.$phone.'%');

        $query->orderBy('a.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }

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

    /**
     * @param $companyId
     * @param $typeId
     * @param $search
     *
     * @return \Doctrine\ORM\Query
     */
    public function getSalesAdminList(
        $companyId,
        $typeId,
        $search
    ) {
        $query = $this->createQueryBuilder('a');

        $query->where('a.companyId = :company')
            ->setParameter('company', $companyId);

        if (!is_null($typeId)) {
            $query->andWhere('a.typeId = :type')
                ->setParameter('type', $typeId);
        }

        // filter by search
        if (!is_null($search)) {
            $query->andWhere('
                    (a.name LIKE :search OR
                    a.username LIKE :search)
                ')
                ->setParameter('search', '%'.$search.'%');
        }

        $query->orderBy('a.username', 'ASC');

        return $query->getQuery();
    }
}
