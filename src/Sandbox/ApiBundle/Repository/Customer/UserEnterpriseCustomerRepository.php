<?php

namespace Sandbox\ApiBundle\Repository\Customer;

use Doctrine\ORM\EntityRepository;

class UserEnterpriseCustomerRepository extends EntityRepository
{
    /**
     * @param $search
     * @param $salesCompanyId
     * @param $address,
     * @param $phone,
     * @param $contectName,
     * @param $contectPhone
     *
     * @return array
     */
    public function searchSalesEnterpriseCustomers(
        $salesCompanyId,
        $search,
        $address,
        $phone,
        $contectName,
        $contectPhone
    ) {
        $query = $this->createQueryBuilder('ec')
                 ->leftJoin('SandboxApiBundle:User\EnterpriseCustomerContacts', 'ecc', 'WITH', 'ec.id = ecc.enterpriseCustomerId')
                 ->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'ecc.customerId = uc.id');

        $query->where('ec.companyId = :companyId')
            ->setParameter('companyId', $salesCompanyId);

        if (!is_null($search)) {
            $query->andWhere('ec.name LIKE :search')
                ->setParameter('search', $search.'%');
        }

        if (!is_null($address)) {
            $query->andWhere('ec.registerAddress LIKE :address')
                ->setParameter('address', $address.'%');
        }

        if (!is_null($phone)) {
            $query->andWhere('ec.phone LIKE :phone')
                ->setParameter('phone', $phone.'%');
        }

        if (!is_null($contectName)) {
            $query->andWhere('uc.name LIKE :name')
                ->setParameter('name', $contectName.'%');
        }

        if (!is_null($contectPhone)) {
            $query->andWhere('uc.phone LIKE :phone')
                ->setParameter('phone', $contectPhone.'%');
        }

        return $query->getQuery()->getResult();
    }
}
