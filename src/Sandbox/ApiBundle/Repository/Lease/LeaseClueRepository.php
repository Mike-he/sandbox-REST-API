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
     * @param $sortColumn
     * @param $direction
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
        $rentFilter = null,
        $startDate = null,
        $endDate = null,
        $limit = null,
        $offset = null,
        $sortColumn = null,
        $direction = null
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
                    return $query;
            }

            $query->setParameter('keywordSearch', "%$keywordSearch%");
        }

        if ($createStart) {
            $query->andWhere('lc.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if ($createEnd) {
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

        if (!is_null($sortColumn) && !is_null($direction)) {
            $direction = strtoupper($direction);
            $sortArray = array(
                'start_date' => 'lc.startDate',
                'cycle' => 'lc.cycle',
                'monthly_rent' => 'lc.monthlyRent',
                'number' => 'lc.number',
                'creation_date' => 'lc.creationDate',
                'total_rent' => '((lc.cycle) * (lc.monthlyRent))',
            );

            $query->orderBy($sortArray[$sortColumn], $direction);
        } else {
            $query->orderBy('lc.creationDate', 'DESC');
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
     * @param $createEnd
     * @param $rentFilter
     * @param $startDate
     * @param $endDate
     *
     * @return int
     */
    public function countClues(
        $myBuildingIds,
        $buildingId,
        $status,
        $keyword = null,
        $keywordSearch = null,
        $createStart = null,
        $createEnd = null,
        $rentFilter = null,
        $startDate = null,
        $endDate = null
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
                    return $query;
            }

            $query->setParameter('keywordSearch', "%$keywordSearch%");
        }

        if ($createStart) {
            $query->andWhere('lc.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if ($createEnd) {
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

        return (int) $result;
    }

    /**
     * @param $myBuildingIds
     * @param $buildingId
     * @param $status
     * @param $keyword
     * @param $keywordSearch
     * @param $startDate
     * @param $endDate
     * @param $source
     * @param $cycleStart
     * @param $cycleEnd
     *
     * @return array
     */
    public function findCluesForPropertyClient(
        $myBuildingIds,
        $buildingId,
        $status,
        $keyword,
        $keywordSearch,
        $startDate,
        $endDate,
        $source,
        $cycleStart,
        $cycleEnd,
        $createStart,
        $createEnd
    ) {
        $query = $this->createQueryBuilder('lc')
            ->select('lc.id')
            ->where('lc.buildingId in (:buildingIds)')
            ->setParameter('buildingIds', $myBuildingIds);

        if (!is_null($buildingId) && !empty($buildingId)) {
            $query->andWhere('lc.buildingId in (:building)')
                ->setParameter('building', $buildingId);
        }

        if ($status) {
            $query->andWhere('lc.status = :status')
                ->setParameter('status', $status);
        }

        if ($source) {
            if ('appointment' == $source) {
                $query->andWhere('lc.productAppointmentId is not null');
            } elseif ('created' == $source) {
                $query->andWhere('lc.productAppointmentId is null');
            }
        }

        if ($keyword && $keywordSearch) {
            switch ($keyword) {
                case 'all':
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'lc.lesseeCustomer = uc.id')
                        ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'lc.productId = p.id')
                        ->leftJoin('p.room', 'r')
                        ->andWhere('
                            lc.serialNumber LIKE :keywordSearch OR
                            uc.phone LIKE :keywordSearch OR 
                            uc.name LIKE :keywordSearch OR
                            r.name LIKE :keywordSearch
                        ');
                    break;
                default:
                    $query->andWhere('lc.serialNumber LIKE :keywordSearch');
            }

            $query->setParameter('keywordSearch', "%$keywordSearch%");
        }

        if ($startDate) {
            $startDate = new \DateTime($startDate);

            $query->andWhere('lc.startDate >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            $query->andWhere('lc.startDate <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        if ($cycleStart) {
            $query->andWhere('lc.cycle >= :cycleStart')
                ->setParameter('cycleStart', $cycleStart);
        }

        if ($cycleEnd) {
            $query->andWhere('lc.cycle <= :cycleEnd')
                ->setParameter('cycleEnd', $cycleEnd);
        }

        if ($createStart) {
            $createStart = new \DateTime($createStart);

            $query->andWhere('lc.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if ($createEnd) {
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);

            $query->andWhere('lc.creationDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        $query->orderBy('lc.creationDate', 'DESC');

        $result = $query->getQuery()->getResult();
        $result = array_map('current', $result);

        return $result;
    }
}
