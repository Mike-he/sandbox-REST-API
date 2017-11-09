<?php

namespace Sandbox\ApiBundle\Repository\Lease;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Lease\LeaseClue;

class LeaseOfferRepository extends EntityRepository
{
    /**
     * @param $myBuildingIds
     * @param $buildingId
     * @param $status
     * @param $keyword
     * @param $keywordSearch
     * @param $createStart
     * @param $createEnd
     * @param $rentFilter
     * @param $startDate
     * @param $endDate
     * @param $limit
     * @param $offset
     * @param $sortColumn
     * @param $direction
     *
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function findOffers(
        $myBuildingIds,
        $buildingId,
        $status,
        $keyword,
        $keywordSearch,
        $createStart,
        $createEnd,
        $rentFilter,
        $startDate,
        $endDate,
        $limit = null,
        $offset = null,
        $sortColumn = null,
        $direction = null
    ) {
        $query = $this->createQueryBuilder('lo')
            ->where('lo.buildingId in (:buildingIds)')
            ->setParameter('buildingIds', $myBuildingIds);

        if ($buildingId) {
            $query->andWhere('lo.buildingId = :building')
                ->setParameter('building', $buildingId);
        }

        if ($status) {
            $query->andWhere('lo.status = :status')
                ->setParameter('status', $status);
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
                    return $query;
            }

            $query->setParameter('keywordSearch', "%$keywordSearch%");
        }

        if ($createStart) {
            $createStart = new \DateTime($createStart);
            $createStart->setTime(0, 0, 0);

            $query->andWhere('lo.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if ($createEnd) {
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);

            $query->andWhere('lo.creationDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        if ($rentFilter && $startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $startDate->setTime(0, 0, 0);

            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            switch ($rentFilter) {
                case 'rent_start':
                    $query->andWhere('lo.startDate >= :startDate')
                        ->andWhere('lo.startDate <= :endDate');
                    break;
                case 'rent_range':
                    $query->andWhere(
                        '(
                            (lo.startDate <= :startDate AND lo.endDate > :startDate) OR
                            (lo.startDate < :endDate AND lo.endDate >= :endDate) OR
                            (lo.startDate >= :startDate AND lo.endDate <= :endDate)
                        )'
                    );
                    break;
                case 'rent_end':
                    $query->andWhere('lo.endDate >= :startDate')
                        ->andWhere('lo.endDate <= :endDate');
                    break;
                default:
                    return $query;
            }

            $query->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        if (!is_null($sortColumn) && !is_null($direction)) {
            $direction = strtoupper($direction);
            $sortArray = array(
                'start_date' => 'lo.startDate',
                'end_date' => 'lo.endDate',
                'monthly_rent' => 'lo.monthlyRent',
                'deposit' => 'lo.deposit',
                'creation_date' => 'lo.creationDate',
                'total_rent' => 'lo.creationDate'
            );

            $query->orderBy($sortArray[$sortColumn],$direction);
        }else{
            $query->orderBy('lo.creationDate','DESC');
        }

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $myBuildingIds
     * @param $buildingId
     * @param $status
     * @param $keyword
     * @param $keywordSearch
     * @param $createStart
     * @param $rentFilter
     * @param $startDate
     * @param $endDate
     * @param $createEnd
     *
     * @return \Doctrine\ORM\QueryBuilder|mixed
     */
    public function countOffers(
        $myBuildingIds,
        $buildingId,
        $status,
        $keyword,
        $keywordSearch,
        $createStart,
        $createEnd,
        $rentFilter,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('lo')
            ->select('count(lo.id)')
            ->where('lo.buildingId in (:buildingIds)')
            ->setParameter('buildingIds', $myBuildingIds);

        if ($buildingId) {
            $query->andWhere('lo.buildingId = :building')
                ->setParameter('building', $buildingId);
        }

        if ($status) {
            $query->andWhere('lo.status = :status')
                ->setParameter('status', $status);
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
                    return $query;
            }

            $query->setParameter('keywordSearch', "%$keywordSearch%");
        }

        if ($createStart) {
            $createStart = new \DateTime($createStart);
            $createStart->setTime(0, 0, 0);

            $query->andWhere('lo.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if ($createEnd) {
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);

            $query->andWhere('lo.creationDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        if ($rentFilter && $startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $startDate->setTime(0, 0, 0);

            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            switch ($rentFilter) {
                case 'rent_start':
                    $query->andWhere('lo.startDate >= :startDate')
                        ->andWhere('lo.startDate <= :endDate');
                    break;
                case 'rent_range':
                    $query->andWhere(
                        '(
                            (lo.startDate <= :startDate AND lo.endDate > :startDate) OR
                            (lo.startDate < :endDate AND lo.endDate >= :endDate) OR
                            (lo.startDate >= :startDate AND lo.endDate <= :endDate)
                        )'
                    );
                    break;
                case 'rent_end':
                    $query->andWhere('lo.endDate >= :startDate')
                        ->andWhere('lo.endDate <= :endDate');
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

    public function findOffersForPropertyClient(
        $myBuildingIds,
        $buildingId,
        $status,
        $source,
        $keyword,
        $keywordSearch,
        $createStart,
        $createEnd,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('lo')
            ->select('lo.id')
            ->where('lo.buildingId in (:buildingIds)')
            ->setParameter('buildingIds', $myBuildingIds);

        if (!is_null($buildingId) && !empty($buildingId)) {
            $query->andWhere('lo.buildingId in (:building)')
                ->setParameter('building', $buildingId);
        }

        if ($status) {
            $query->andWhere('lo.status = :status')
                ->setParameter('status', $status);
        }

        if ($source) {
            if ('clue' == $source) {
                $query->andWhere('lo.LeaseClueId is not null');
            } elseif ('created' == $source) {
                $query->andWhere('lo.LeaseClueId is null');
            }
        }

        if ($keyword && $keywordSearch) {
            switch ($keyword) {
                case 'all':
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'lo.lesseeCustomer = uc.id')
                        ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'lo.productId = p.id')
                        ->leftJoin('p.room', 'r')
                        ->andWhere('
                            lo.serialNumber LIKE :keywordSearch OR
                            uc.phone LIKE :keywordSearch OR
                            uc.name LIKE :keywordSearch OR
                            r.name LIKE :keywordSearch
                        ');
                    break;
                default:
                    $query->andWhere(' lo.serialNumber LIKE :keywordSearch');
            }

            $query->setParameter('keywordSearch', "%$keywordSearch%");
        }

        if ($createStart) {
            $createStart = new \DateTime($createStart);

            $query->andWhere('lo.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if ($createEnd) {
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);

            $query->andWhere('lo.creationDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        if ($startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            $query->andWhere(
                '(
                    (lo.startDate <= :startDate AND lo.endDate > :startDate) OR
                    (lo.startDate < :endDate AND lo.endDate >= :endDate) OR
                    (lo.startDate >= :startDate AND lo.endDate <= :endDate)
                )'
            )
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        $query->orderBy('lo.creationDate', 'DESC');

        $result = $query->getQuery()->getResult();
        $result = array_map('current', $result);

        return $result;
    }
}
