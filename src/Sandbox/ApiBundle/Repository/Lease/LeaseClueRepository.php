<?php

namespace Sandbox\ApiBundle\Repository\Lease;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Lease\LeaseClue;

class LeaseClueRepository extends EntityRepository
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
     *
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function findClues(
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
        $offset = null
    ) {
        $query = $this->createQueryBuilder('lc')
            ->where('lc.buildingId in (:buildingIds)')
            ->setParameter('buildingIds', $myBuildingIds);

        if ($buildingId) {
            $query->andWhere('lc.buildingId = :building')
                ->setParameter('building', $buildingId);
        }

        if ($status) {
            $query->andWhere('lc.status = :status')
                ->setParameter('status', $status);
        }

        if ($keyword && $keywordSearch) {
            switch ($keyword) {
                case LeaseClue::KEYWORD_NUMBER:
                    $query->andWhere('lc.serialNumber LIKE :keywordSearch');
                    break;
                case LeaseClue::KEYWORD_CUSTOMER_PHONE:
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'lc.lesseeCustomer = uc.id')
                        ->andWhere('uc.phone LIKE :keywordSearch');
                    break;
                case LeaseClue::KEYWORD_CUSTOMER_NAME:
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'lc.lesseeCustomer = uc.id')
                        ->andWhere('uc.name LIKE :keywordSearch');
                    break;
                case LeaseClue::KEYWORD_ROOM_NAME:
                    $query->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'lc.productId = p.id')
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

            $query->andWhere('lc.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if ($createEnd) {
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);

            $query->andWhere('lc.creationDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        if ($rentFilter && $startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $startDate->setTime(0, 0, 0);

            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            switch ($rentFilter) {
                case 'rent_start':
                    $query->andWhere('lc.startDate >= :startDate')
                        ->andWhere('lc.startDate <= :endDate');
                    break;
                case 'rent_range':
                    $query->andWhere(
                        '(
                            (lc.startDate <= :startDate AND lc.endDate > :startDate) OR
                            (lc.startDate < :endDate AND lc.endDate >= :endDate) OR
                            (lc.startDate >= :startDate AND lc.endDate <= :endDate)
                        )'
                    );
                    break;
                case 'rent_end':
                    $query->andWhere('lc.endDate >= :startDate')
                        ->andWhere('lc.endDate <= :endDate');
                    break;
                default:
                    return $query;
            }

            $query->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        $query->orderBy('lc.id', 'DESC');

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
    public function countClues(
        $myBuildingIds,
        $buildingId,
        $status,
        $keyword,
        $keywordSearch,
        $createStart,
        $rentFilter,
        $startDate,
        $endDate,
        $createEnd
    ) {
        $query = $this->createQueryBuilder('lc')
            ->select('count(lc.id)')
            ->where('lc.buildingId in (:buildingIds)')
            ->setParameter('buildingIds', $myBuildingIds);

        if ($buildingId) {
            $query->andWhere('lc.buildingId = :building')
                ->setParameter('building', $buildingId);
        }

        if ($status) {
            $query->andWhere('lc.status = :status')
                ->setParameter('status', $status);
        }

        if ($keyword && $keywordSearch) {
            switch ($keyword) {
                case LeaseClue::KEYWORD_NUMBER:
                    $query->andWhere('lc.serialNumber LIKE :keywordSearch');
                    break;
                case LeaseClue::KEYWORD_CUSTOMER_PHONE:
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'lc.lesseeCustomer = uc.id')
                        ->andWhere('uc.phone LIKE :keywordSearch');
                    break;
                case LeaseClue::KEYWORD_CUSTOMER_NAME:
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'lc.lesseeCustomer = uc.id')
                        ->andWhere('uc.name LIKE :keywordSearch');
                    break;
                case LeaseClue::KEYWORD_ROOM_NAME:
                    $query->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'lc.productId = p.id')
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

            $query->andWhere('lc.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if ($createEnd) {
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);

            $query->andWhere('lc.creationDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        if ($rentFilter && $startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $startDate->setTime(0, 0, 0);

            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            switch ($rentFilter) {
                case 'rent_start':
                    $query->andWhere('lc.startDate >= :startDate')
                        ->andWhere('lc.startDate <= :endDate');
                    break;
                case 'rent_range':
                    $query->andWhere(
                        '(
                            (lc.startDate <= :startDate AND lc.endDate > :startDate) OR
                            (lc.startDate < :endDate AND lc.endDate >= :endDate) OR
                            (lc.startDate >= :startDate AND lc.endDate <= :endDate)
                        )'
                    );
                    break;
                case 'rent_end':
                    $query->andWhere('lc.endDate >= :startDate')
                        ->andWhere('lc.endDate <= :endDate');
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
