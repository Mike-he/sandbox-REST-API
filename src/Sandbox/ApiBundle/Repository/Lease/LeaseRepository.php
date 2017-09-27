<?php

namespace Sandbox\ApiBundle\Repository\Lease;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Product\ProductAppointment;

class LeaseRepository extends EntityRepository
{
    /**
     * @param $myBuildingIds
     * @param $status
     * @param $keyword
     * @param $keywordSearch
     * @param $createRange
     * @param $createStart
     * @param $createEnd
     * @param $rentFilter
     * @param $startDate
     * @param $endDate
     * @param $companyId
     * @param $roomId
     * @param $userId
     *
     * @return array
     */
    public function countLeasesAmount(
        $myBuildingIds,
        $status,
        $keyword,
        $keywordSearch,
        $createRange,
        $createStart,
        $createEnd,
        $rentFilter,
        $startDate,
        $endDate,
        $companyId,
        $roomId,
        $userId = null
    ) {
        $query = $this->createQueryBuilder('l')
            ->leftJoin('l.product', 'p')
            ->leftJoin('p.room', 'r')
            ->select('COUNT(l)')
            ->where('l.id is not null');

        $query = $this->generateQueryForLeases(
            $query,
            $myBuildingIds,
            $status,
            $keyword,
            $keywordSearch,
            $createRange,
            $createStart,
            $createEnd,
            $rentFilter,
            $startDate,
            $endDate,
            $companyId,
            $roomId,
            $userId
        );

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $myBuildingIds
     * @param $status
     * @param $keyword
     * @param $keywordSearch
     * @param $createRange
     * @param $createStart
     * @param $createEnd
     * @param $rentFilter
     * @param $startDate
     * @param $companyId
     * @param $endDate
     * @param $roomId
     * @param $limit
     * @param $offset
     * @param $userId
     * @param $sortColumn,
     * @param $direction,
     *
     * @return array
     */
    public function findLeases(
        $myBuildingIds,
        $status,
        $keyword,
        $keywordSearch,
        $createRange,
        $createStart,
        $createEnd,
        $rentFilter,
        $startDate,
        $endDate,
        $companyId,
        $roomId,
        $limit = null,
        $offset = null,
        $userId = null,
        $sortColumn = null,
        $direction = null
    ) {
        $query = $this->createQueryBuilder('l')
            ->leftJoin('l.product', 'p')
            ->leftJoin('p.room', 'r');

        $query = $this->generateQueryForLeases(
            $query,
            $myBuildingIds,
            $status,
            $keyword,
            $keywordSearch,
            $createRange,
            $createStart,
            $createEnd,
            $rentFilter,
            $startDate,
            $endDate,
            $companyId,
            $roomId,
            $userId,
            $sortColumn,
            $direction
        );

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $query
     * @param $myBuildingIds
     * @param $status
     * @param $keyword
     * @param $keywordSearch
     * @param $createRange
     * @param $createStart
     * @param $createEnd
     * @param $rentFilter
     * @param $startDate
     * @param $endDate
     * @param $companyId
     * @param $roomId
     * @param $userId
     * @param $sortColumn,
     * @param $direction
     *
     * @return $query
     */
    private function generateQueryForLeases(
        $query,
        $myBuildingIds,
        $status,
        $keyword,
        $keywordSearch,
        $createRange,
        $createStart,
        $createEnd,
        $rentFilter,
        $startDate,
        $endDate,
        $companyId,
        $roomId,
        $userId = null,
        $sortColumn = null,
        $direction = null
    ) {
        if (!is_null($myBuildingIds) && !empty($myBuildingIds)) {
            $query->andWhere('r.buildingId IN (:buildingIds)')
                ->setParameter('buildingIds', $myBuildingIds);
        }

        if (!is_null($companyId) && !empty($companyId)) {
            $query->andWhere('l.companyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        if (!is_null($roomId)) {
            $query->andWhere('r.id = :roomId')
                ->setParameter('roomId', $roomId);
        }

        if ($status) {
            $query->andWhere('l.status = :status')
                ->setParameter('status', $status);
        }

        if (!is_null($keyword) &&
            !empty($keyword) &&
            !is_null($keywordSearch) &&
            !empty($keywordSearch)
        ) {
            switch ($keyword) {
                case ProductAppointment::KEYWORD_APPLICANT:
                    $query->andWhere('l.lesseeName LIKE :keywordSearch');
                    break;
                case ProductAppointment::KEYWORD_ROOM:
                    $query->andWhere('r.name LIKE :keywordSearch');
                    break;
                case ProductAppointment::KEYWORD_NUMBER:
                    $query->andWhere('l.serialNumber LIKE :keywordSearch');
                    break;
                default:
                    return array();
            }

            $query->setParameter('keywordSearch', "%$keywordSearch%");
        }

        if (!is_null($createRange) && !empty($createRange)) {
            $now = new \DateTime();

            if ($createRange == ProductAppointment::RANGE_LAST_WEEK) {
                $last = $now->modify('-1 week');
            } elseif ($createRange == ProductAppointment::RANGE_LAST_MONTH) {
                $last = $now->modify('-1 month');
            } else {
                $last = $now;
            }

            $query->andWhere('l.creationDate >= :last')
                ->setParameter('last', $last);
        } else {
            if (!is_null($createStart) && !empty($createStart)) {
                $createStart = new \DateTime($createStart);
                $createStart->setTime(0, 0, 0);

                $query->andWhere('l.creationDate >= :createStart')
                    ->setParameter('createStart', $createStart);
            }

            if (!is_null($createEnd) && !empty($createEnd)) {
                $createEnd = new \DateTime($createEnd);
                $createEnd->setTime(23, 59, 59);

                $query->andWhere('l.creationDate <= :createEnd')
                    ->setParameter('createEnd', $createEnd);
            }
        }

        if (!is_null($rentFilter) &&
            !empty($rentFilter) &&
            !is_null($startDate) &&
            !empty($startDate) &&
            !is_null($endDate) &&
            !empty($endDate)
        ) {
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

        if(!is_null($sortColumn) && !is_null($direction)) {
            $direction = strtoupper($direction);

            switch ($sortColumn) {
                case 'start_date':
                    $query->orderBy('l.startDate', $direction);
                    break;
                case 'end_date':
                    $query->orderBy('l.endDate', $direction);
                    break;
                case 'monthly_rent':
                    $query->orderBy('l.monthlyRent', $direction);
                    break;
                case 'deposit':
                    $query->orderBy('l.deposit', $direction);
                    break;
                case 'creation_date':
                    $query->orderBy('l.creationDate', $direction);
                    break;
                case 'total_rent':
                    $query->orderBy('l.totalRent', $direction);
                    break;
                default:
                    $query->orderBy('lo.id', 'DESC');
                    break;
            }
        }

//        if (!is_null($userId)) {
//            $query->andWhere('(l.supervisor = :userId OR l.drawee = :userId)')
//                ->setParameter('userId', $userId);
//        }

        return $query;
    }

    /**
     * @param $productId
     * @param $start
     * @param $end
     * @param $status
     *
     * @return array
     */
    public function getRoomUsersUsage(
        $productId,
        $start,
        $end,
        $status
    ) {
        $query = $this->createQueryBuilder('l')
            ->where('l.product = :productId')
            ->andWhere('l.status in (:status)')
            ->andWhere('
                (l.startDate <= :start AND l.endDate > :start) OR
                (l.startDate < :end AND l.endDate >= :end) OR
                (l.startDate >= :start AND l.endDate <= :end)
            ')
            ->setParameter('productId', $productId)
            ->setParameter('status', $status)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $search
     *
     * @return array
     */
    public function getCurrentLeases(
        $userId,
        $search
    ) {
        $query = $this->createQueryBuilder('l')
            ->select('
                    l.id, 
                    u.id as supervisor, 
                    l.startDate, 
                    l.endDate, 
                    up.name as username, 
                    b.address, 
                    r.name, 
                    r.type, 
                    p.roomId, 
                    p.id as productId, 
                    l.creationDate
                ')
            ->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'l.lesseeCustomer = uc.id')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'uc.userId = u.id')
            ->leftJoin('u.userProfile', 'up')
            ->leftJoin('l.product', 'p')
            ->leftJoin('p.room', 'r')
            ->leftJoin('r.building', 'b')
            ->leftJoin('r.city', 'c')
            ->leftJoin('l.invitedPeople', 'i')
            ->where(
                '(
                    uc.userId = :userId OR
                    i.id = :userId
                )'
            )
            ->andWhere('l.status = :status')
            ->setParameter('status', Lease::LEASE_STATUS_PERFORMING)
            ->setParameter('userId', $userId);

        if (!is_null($search)) {
            $query->andWhere(
                '(
                    up.name LIKE :search OR
                    c.name LIKE :search OR
                    b.name LIKE :search OR
                    r.name LIKE :search
                )'
            )
                ->setParameter('search', "%$search%");
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $buildings
     * @param $date
     * @param $status
     *
     * @return array
     */
    public function getUsingLease(
        $buildings,
        $date,
        $status
    ) {
        $query = $this->createQueryBuilder('l')
            ->leftJoin('l.product', 'p')
            ->leftJoin('p.room', 'r')
            ->where('r.building in (:building)')
            ->andWhere('l.endDate >= :date')
            ->andWhere('l.status in (:status)')
            ->setParameter('building', $buildings)
            ->setParameter('date', $date)
            ->setParameter('status', $status);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $customerIds
     * @param $statusArray
     *
     * @return array
     */
    public function getLeaseNumbersForClientLease(
        $customerIds,
        $statusArray
    ) {
        $query = $this->createQueryBuilder('l')
            ->select('l.serialNumber')
            ->where('l.status IN (:status)')
            ->andWhere('l.lesseeCustomer IN (:customerIds)')
            ->setParameter('status', $statusArray)
            ->setParameter('customerIds', $customerIds);

        $query->orderBy('l.modificationDate', 'DESC');

        $result = $query->getQuery()->getScalarResult();
        $result = array_map('current', $result);

        return $result;
    }

    public function countValidOrder(
        $userId,
        $now,
        $status
    ) {
        $query = $this->createQueryBuilder('l')
            ->select('count(l.id)')
            ->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'l.lesseeCustomer = uc.id')
            ->leftJoin('l.invitedPeople', 'p')
            ->where('l.status IN (:status)')
            ->andWhere('(uc.userId = :userId OR p.id = :userId)')
            ->andWhere('l.startDate <= :now')
            ->andWhere('l.endDate >= :now')
            ->setParameter('status', $status)
            ->setParameter('userId', $userId)
            ->setParameter('now', $now);

        $result = $query->getQuery()->getSingleScalarResult();

        return (int) $result;
    }
}
