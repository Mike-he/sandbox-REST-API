<?php

namespace Sandbox\ApiBundle\Repository\Customer;

use Doctrine\ORM\EntityRepository;

class UserEnterpriseCustomerRepository extends EntityRepository
{
    /**
     * @param $salesCompanyId
     * @param $keyword
     * @param $keywordSearch
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function searchSalesEnterpriseCustomers(
        $salesCompanyId,
        $keyword,
        $keywordSearch,
        $limit = null,
        $offset = null
    ) {
        $query = $this->createQueryBuilder('ec')
                 ->leftJoin('SandboxApiBundle:User\EnterpriseCustomerContacts', 'ecc', 'WITH', 'ec.id = ecc.enterpriseCustomerId')
                 ->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'ecc.customerId = uc.id');

        $query->where('ec.companyId = :companyId')
            ->setParameter('companyId', $salesCompanyId);

        if(!is_null($keyword) && !is_null($keywordSearch)){
            switch ($keyword) {
                case 'name':
                    $query->andWhere('ec.name LIKE :search');
                    break;
                case 'registerAddress':
                    $query->andWhere('ec.registerAddress LIKE :search');
                    break;
                case 'contactName':
                    $query->andWhere('uc.name LIKE :search');
                    break;
                case 'contactPhone':
                    $query->andWhere('uc.phone LIKE :search');
                    break;
                default:
                    break;
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }
}
