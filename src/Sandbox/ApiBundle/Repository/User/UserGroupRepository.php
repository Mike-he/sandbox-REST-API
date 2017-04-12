<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserGroupRepository extends EntityRepository
{
    /**
     * @param $company
     * @param $type
     *
     * @return array
     */
    public function countUserGroup(
        $company,
        $type
    ) {
        $query = $this->createQueryBuilder('ug')
            ->select('count(ug.id)')
            ->where('ug.companyId = :company')
            ->andWhere('ug.type = :type')
            ->setParameter('company', $company)
            ->setParameter('type', $type);

        $result = $query->getQuery()->getSingleScalarResult();

        return (int) $result;
    }
}
