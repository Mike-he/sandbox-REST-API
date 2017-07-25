<?php

namespace Sandbox\ApiBundle\Repository\Customer;

use Doctrine\ORM\EntityRepository;

class UserEnterpriseCustomerRepository extends EntityRepository
{
    /**
     * @param $search
     * @param $salesCompanyId
     *
     * @return array
     */
    public function searchSalesEnterpriseCustomers(
        $salesCompanyId,
        $search
    ) {
        $query = $this->createQueryBuilder('ec');

        $query->where('ec.companyId = :companyId')
            ->setParameter('companyId', $salesCompanyId);

        if (!is_null($search)) {
            $query->andWhere('ec.name LIKE :search')
                ->setParameter('search', $search.'%');
        }

        return $query->getQuery()->getResult();
    }
}
