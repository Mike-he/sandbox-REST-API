<?php

namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;

class SalesCompanyRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getSalesCompanies()
    {
        $query = $this->createQueryBuilder('sc')
           ->orderBy('sc.id', 'ASC');

        return $query->getQuery()->getResult();
    }
}
