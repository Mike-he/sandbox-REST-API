<?php

namespace Sandbox\ApiBundle\Repository\Customer;

use Doctrine\ORM\EntityRepository;

class UserCustomerRepository extends EntityRepository
{
    /**
     * @param $salesCompanyId
     * @param $search
     * @param $groupId
     * @param $pageLimit
     * @param $pageIndex
     *
     * @return mixed
     */
    public function getSalesAdminCustomers(
        $salesCompanyId,
        $search,
        $groupId,
        $pageLimit,
        $pageIndex,
        $getCount = null
    ) {
        $query = $this->createQueryBuilder('c');

        if ($getCount) {
            $query->select('COUNT(c)');
        }

        $query->where('c.companyId = :companyId')
            ->setParameter('companyId', $salesCompanyId);

        if ($search) {
            $query->andWhere('
                c.name LIKE :search OR
                c.phone LIKE :search OR
                c.email LIKE :search
            ')
                ->setParameter('search', $search.'%');
        }

        if ($groupId) {
            $query->leftJoin('SandboxApiBundle:User\UserGroupHasUser', 'gu', 'WITH', 'gu.customerId = c.id')
                ->andWhere('gu.groupId = :groupId')
                ->setParameter('groupId', $groupId);
        }

        if ($pageIndex && $pageLimit) {
            $offset = ($pageIndex - 1) * $pageLimit;

            $query->setMaxResults($pageLimit)
                ->setFirstResult($offset);
        }

        $query->orderBy('c.id', 'DESC');

        if ($getCount) {
            return (int) $query->getQuery()->getSingleScalarResult();
        }

        return $query->getQuery()->getResult();
    }
}