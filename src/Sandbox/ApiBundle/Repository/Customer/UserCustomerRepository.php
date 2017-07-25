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

    /**
     * @param $id
     */
    public function getUserIdByCustomerId(
        $id
    ) {
        $query = $this->createQueryBuilder('c')
            ->select('c.userId')
            ->where('c.id = :Id')
            ->setParameter('Id', $id);

        $result = $query->getQuery()->getOneOrNullResult();

        $userId = null;
        if ($result && $result['userId']) {
            $userId = $result['userId'];
        }

        return $userId;
    }

    /**
     * @param $userId
     *
     * @return array
     */
    public function getCustomerIdsByUserId(
        $userId
    ) {
        $query = $this->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.userId = :userId')
            ->setParameter('userId', $userId);

        $customers = $query->getQuery()->getResult();

        $result = array();
        foreach ($customers as $customer) {
            $result[] = $customer['id'];
        }

        return $result;
    }

    /**
     * @param $ids
     *
     * @return array
     */
    public function searchCustomers(
        $ids
    ) {
        $query = $this->createQueryBuilder('c')
            ->select('
                    c.userId as user_id,
                    c.phone,
                    c.avatar,
                    c.name,
                    c.email
                ')
            ->where('c.id in (:ids)')
            ->setParameter('ids', $ids);

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
