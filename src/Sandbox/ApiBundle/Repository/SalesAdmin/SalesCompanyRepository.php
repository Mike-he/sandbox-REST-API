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

    /**
     * @param $banned
     * @param $keyword
     * @param $keywordSearch
     *
     * @return array
     */
    public function getCompanyList(
        $banned,
        $keyword,
        $keywordSearch
    ) {
        $query = $this->createQueryBuilder('sc')
            ->where('1=1');

        if (!is_null($banned)) {
            $query->andWhere('sc.banned = :banned')
                ->setParameter('banned', $banned);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'name':
                    $query->andWhere('sc.name LIKE :search');
                    break;
                default:
                    $query->andWhere('sc.name LIKE :search');
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        $query->orderBy('sc.id', 'DESC');

        return $query->getQuery()->getResult();
    }
}
