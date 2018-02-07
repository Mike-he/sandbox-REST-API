<?php

namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyView;

class SalesCompanyViewRepository extends EntityRepository
{
    /**
     * @param $status
     * @param $keyword
     * @param $keywordSearch
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getCompanyList(
        $status,
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
                sc.status,
                sc.contacter,
                sc.contacterPhone as contacter_phone,
                sc.contacterEmail as contacter_email,
                sc.type,
                sc.creationDate as creation_date
            ')
            ->where('1=1');

        if (!is_null($status)) {
            $query->andWhere('sc.status = :status')
                ->setParameter('status', $status);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'company':
                    $query->andWhere('sc.name LIKE :search');
                    break;
                case 'building':
                    $query->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'sc.id = b.companyId')
                        ->andWhere('sc.type = :type')
                        ->andWhere('b.name LIKE :search')
                        ->setParameter('type', SalesCompanyView::TYPE_COMPANY)
                    ;
                    break;
                case 'shop':
                    $query->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'sc.id = b.companyId')
                        ->leftJoin('SandboxApiBundle:Shop\Shop', 's', 'WITH', 'b.id = s.buildingId')
                        ->andWhere('s.name LIKE :search')
                        ->andWhere('sc.type = :type')
                        ->setParameter('type', SalesCompanyView::TYPE_COMPANY)
                    ;
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
        $status,
        $keyword,
        $keywordSearch
    ) {
        $query = $this->createQueryBuilder('sc')
            ->select('count(sc.id)')
            ->where('1=1');
        if (!is_null($status)) {
            $query->andWhere('sc.status = :status')
                ->setParameter('status', $status);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'company':
                    $query->andWhere('sc.name LIKE :search');
                    break;
                case 'building':
                    $query->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'sc.id = b.companyId')
                        ->andWhere('sc.type = :type')
                        ->andWhere('b.name LIKE :search')
                        ->setParameter('type', SalesCompanyView::TYPE_COMPANY)
                    ;
                    break;
                case 'shop':
                    $query->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'sc.id = b.companyId')
                        ->leftJoin('SandboxApiBundle:Shop\Shop', 's', 'WITH', 'b.id = s.buildingId')
                        ->andWhere('s.name LIKE :search')
                        ->andWhere('sc.type = :type')
                        ->setParameter('type', SalesCompanyView::TYPE_COMPANY)
                    ;
                    break;
                default:
                    $query->andWhere('sc.name LIKE :search');
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }


        return $query->getQuery()->getSingleScalarResult();
    }
}
