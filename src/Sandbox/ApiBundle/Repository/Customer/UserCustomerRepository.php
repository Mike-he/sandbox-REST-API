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
        $groupId
    ) {
        $query = $this->createQueryBuilder('c');

        $query->where('c.companyId = :companyId')
            ->andWhere('c.isDeleted = FALSE')
            ->setParameter('companyId', $salesCompanyId);

        if ($search) {
            $query->andWhere('(
                c.name LIKE :search OR
                c.phone LIKE :search OR
                c.email LIKE :search
            )')
                ->setParameter('search', $search.'%');
        }

        if ($groupId) {
            $query->leftJoin('SandboxApiBundle:User\UserGroupHasUser', 'gu', 'WITH', 'gu.customerId = c.id')
                ->andWhere('gu.groupId = :groupId')
                ->setParameter('groupId', $groupId);
        }

        $query->orderBy('c.id', 'DESC');

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
     * @param $salesCompanyId
     * @param $ids
     * @param $userIds
     * @param $search
     *
     * @return array
     */
    public function searchCustomers(
        $salesCompanyId,
        $ids,
        $userIds = null,
        $search = null
    ) {
        $query = $this->createQueryBuilder('c')
            ->select('
                    c.id,
                    c.userId as user_id,
                    c.phone,
                    c.avatar,
                    c.name,
                    c.email
                ')
            ->where('1=1');

        if ($salesCompanyId) {
            $query->andWhere('c.companyId = :company')
                ->setParameter('company', $salesCompanyId);
        }

        if ($ids && !empty($ids)) {
            $query->andWhere('c.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($userIds && !empty($userIds)) {
            $query->andWhere('c.userId in (:userIds)')
                ->setParameter('userIds', $userIds);
        }

        if ($search) {
            $query->andWhere('c.phone LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

}
