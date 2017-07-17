<?php

namespace Sandbox\ApiBundle\Repository\Lease;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Lease\LeaseClue;

class LeaseOfferRepository extends EntityRepository
{
    public function findOffers(
        $salesCompanyId,
        $buildingId,
        $keyword,
        $keywordSearch,
        $createStart,
        $createEnd,
        $rentFilter,
        $startDate,
        $endDate,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('lo')
            ->where('lo.companyId = :companyId')
            ->setParameter('companyId', $salesCompanyId);

        if ($buildingId) {
            $query->where('lo.buildingId = :building')
                ->setParameter('building', $buildingId);
        }

        if ($keyword && $keywordSearch) {
            switch ($keyword) {
                case LeaseClue::KEYWORD_NUMBER:
                    $query->andWhere('lo.serialNumber LIKE :keywordSearch');
                    break;
                case LeaseClue::KEYWORD_CUSTOMER_PHONE:
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'lo.lesseeCustomer = uc.id')
                        ->andWhere('uc.phone LIKE :keywordSearch');
                    break;
                case LeaseClue::KEYWORD_CUSTOMER_NAME:
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'lo.lesseeCustomer = uc.id')
                        ->andWhere('uc.name LIKE :keywordSearch');
                    break;
                case LeaseClue::KEYWORD_ROOM_NAME:
                    $query->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'lo.productId = p.id')
                        ->leftJoin('p.room', 'r')
                        ->andWhere('r.name LIKE :keywordSearch');
                    break;
                default:
                    return array();
            }

            $query->setParameter('keywordSearch', "%$keywordSearch%");
        }

        if ($createStart) {
            $createStart = new \DateTime($createStart);
            $createStart->setTime(0, 0, 0);

            $query->andWhere('l.confirmingDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if ($createEnd) {
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);

            $query->andWhere('l.confirmingDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        if ($rentFilter && $startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $startDate->setTime(0, 0, 0);

            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            switch ($rentFilter) {
                case 'rent_start':
                    $query->andWhere('l.startDate >= :startDate')
                        ->andWhere('l.startDate <= :endDate');
                    break;
                case 'rent_range':
                    $query->andWhere(
                        '(
                            (l.startDate <= :startDate AND l.endDate > :startDate) OR
                            (l.startDate < :endDate AND l.endDate >= :endDate) OR
                            (l.startDate >= :startDate AND l.endDate <= :endDate)
                        )'
                    );
                    break;
                case 'rent_end':
                    $query->andWhere('l.endDate >= :startDate')
                        ->andWhere('l.endDate <= :endDate');
                    break;
                default:
                    return $query;
            }

            $query->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        $query->orderBy('lo.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    public function countOffers(
        $salesCompanyId,
        $buildingId,
        $keyword,
        $keywordSearch,
        $createStart,
        $rentFilter,
        $startDate,
        $endDate,
        $createEnd
    ) {
        $query = $this->createQueryBuilder('lo')
            ->select('count(lo.id)')
            ->where('lo.companyId = :companyId')
            ->setParameter('companyId', $salesCompanyId);

        if ($buildingId) {
            $query->where('lo.buildingId = :building')
                ->setParameter('building', $buildingId);
        }

        if ($keyword && $keywordSearch) {
            switch ($keyword) {
                case LeaseClue::KEYWORD_NUMBER:
                    $query->andWhere('lo.serialNumber LIKE :keywordSearch');
                    break;
                case LeaseClue::KEYWORD_CUSTOMER_PHONE:
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'lo.lesseeCustomer = uc.id')
                        ->andWhere('uc.phone LIKE :keywordSearch');
                    break;
                case LeaseClue::KEYWORD_CUSTOMER_NAME:
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'lo.lesseeCustomer = uc.id')
                        ->andWhere('uc.name LIKE :keywordSearch');
                    break;
                case LeaseClue::KEYWORD_ROOM_NAME:
                    $query->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'lo.productId = p.id')
                        ->leftJoin('p.room', 'r')
                        ->andWhere('r.name LIKE :keywordSearch');
                    break;
                default:
                    return array();
            }

            $query->setParameter('keywordSearch', "%$keywordSearch%");
        }

        if ($createStart) {
            $createStart = new \DateTime($createStart);
            $createStart->setTime(0, 0, 0);

            $query->andWhere('l.confirmingDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if ($createEnd) {
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);

            $query->andWhere('l.confirmingDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        if ($rentFilter && $startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $startDate->setTime(0, 0, 0);

            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            switch ($rentFilter) {
                case 'rent_start':
                    $query->andWhere('l.startDate >= :startDate')
                        ->andWhere('l.startDate <= :endDate');
                    break;
                case 'rent_range':
                    $query->andWhere(
                        '(
                            (l.startDate <= :startDate AND l.endDate > :startDate) OR
                            (l.startDate < :endDate AND l.endDate >= :endDate) OR
                            (l.startDate >= :startDate AND l.endDate <= :endDate)
                        )'
                    );
                    break;
                case 'rent_end':
                    $query->andWhere('l.endDate >= :startDate')
                        ->andWhere('l.endDate <= :endDate');
                    break;
                default:
                    return $query;
            }

            $query->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return $result;
    }
}
