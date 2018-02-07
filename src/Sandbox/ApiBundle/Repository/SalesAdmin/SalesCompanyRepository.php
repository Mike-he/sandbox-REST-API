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
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getCompanyList(
        $banned,
        $keyword,
        $keywordSearch,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('sc')
            ->select('
                sc.id,
                sc.phone,
                sc.address,
                sc.name,
                sc.banned,
                sc.contacter,
                sc.contacterPhone as contacter_phone,
                sc.contacterEmail as contacter_email
            ')
            ->where('1=1');

        if (!is_null($banned)) {
            $query->andWhere('sc.banned = :banned')
                ->setParameter('banned', $banned);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'company':
                    $query->andWhere('sc.name LIKE :search');
                    break;
                case 'building':
                    $query->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'sc.id = b.companyId')
                        ->andWhere('b.name LIKE :search');
                    break;
                case 'shop':
                    $query->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'sc.id = b.companyId')
                        ->leftJoin('SandboxApiBundle:Shop\Shop', 's', 'WITH', 'b.id = s.buildingId')
                        ->andWhere('s.name LIKE :search');
                    break;
                default:
                    $query->andWhere('sc.name LIKE :search');
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        $query->orderBy('sc.creationDate', 'DESC');

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }

    public function countCompanyList(
        $banned,
        $keyword,
        $keywordSearch
    ) {
        $query = $this->createQueryBuilder('sc')
            ->select('count(sc.id)')
            ->where('1=1');

        if (!is_null($banned)) {
            $query->andWhere('sc.banned = :banned')
                ->setParameter('banned', $banned);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'company':
                    $query->andWhere('sc.name LIKE :search');
                    break;
                case 'building':
                    $query->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'sc.id = b.companyId')
                        ->andWhere('b.name LIKE :search');
                    break;
                case 'shop':
                    $query->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'sc.id = b.companyId')
                        ->leftJoin('SandboxApiBundle:Shop\Shop', 's', 'WITH', 'b.id = s.buildingId')
                        ->andWhere('s.name LIKE :search');
                    break;
                default:
                    $query->andWhere('sc.name LIKE :search');
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }


        return $query->getQuery()->getSingleScalarResult();
    }

    public function getCompanyInfo(
        $id
    ) {
        $query = $this->createQueryBuilder('sc')
            ->select('
                sc.id,
                sc.name
            ')
            ->where('sc.id = :id')
            ->setParameter('id', $id);

        return $query->getQuery()->getResult();
    }
}
