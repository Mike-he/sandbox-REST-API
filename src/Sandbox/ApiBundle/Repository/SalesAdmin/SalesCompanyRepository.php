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

    public function getCompanyList(
        $banned,
        $search
    ) {
        $query = $this->createQueryBuilder('sc');

        if (!is_null($banned)) {
            $query->andWhere('sc.banned = :banned')
                ->setParameter('banned', $banned);
        }

        if (!is_null($search)) {
            $query->andWhere('
                    (sc.name LIKE :search OR
                    sc.phone LIKE :search OR
                    sc.email LIKE :search)
                ')
                ->setParameter('search', '%'.$search.'%');
        }

        $query->orderBy('sc.id', 'ASC');

        return $query->getQuery()->getResult();
    }
}
