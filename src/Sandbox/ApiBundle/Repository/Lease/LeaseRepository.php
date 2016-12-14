<?php

namespace Sandbox\ApiBundle\Repository\Lease;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Product\ProductAppointment;

class LeaseRepository extends EntityRepository
{
    /**
     * @param $startDate
     * @param $endDate
     * @param $keyword
     * @param $keywordSearch
     * @param $myBuildingIds
     * @param $createRange
     * @param $createStart
     * @param $createEnd
     * @param $status
     * @param $limit
     * @param $offset
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
        $startDate,
        $endDate,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('l')
            ->leftJoin('l.product', 'p')
            ->leftJoin('p.room', 'r')
            ->select('COUNT(l)')
            ->where('l.id is not null');

        $query->andWhere('r.buildingId IN (:buildingIds)')
            ->setParameter('buildingIds', $myBuildingIds);

        if ($status == 'all') {
            $query->andWhere('l.status != :status')
                ->setParameter('status', Lease::LEASE_STATUS_DRAFTING);
        } else {
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
                    $query->andWhere('l.lessee LIKE :keywordSearch');
                    break;
                case ProductAppointment::KEYWORD_ROOM:
                    $query->andWhere('r.name LIKE :keywordSearch');
                    break;
                case ProductAppointment::KEYWORD_NUMBER:
                    $query->andWhere('a.appointmentNumber LIKE :keywordSearch');
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

        if (!is_null($startDate) && !empty($startDate)) {
            $startDate = new \DateTime($startDate);
            $startDate->setTime(0, 0, 0);

            $query->andWhere('l.endRentDate > :startDate')
                ->setParameter('startDate', $startDate);
        }

        if (!is_null($endDate) && !empty($endDate)) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            $query->andWhere('l.startRentDate <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return $result;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $keyword
     * @param $keywordSearch
     * @param $myBuildingIds
     * @param $createRange
     * @param $createStart
     * @param $createEnd
     * @param $status
     * @param $limit
     * @param $offset
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
        $startDate,
        $endDate,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('l')
            ->leftJoin('l.product', 'p')
            ->leftJoin('p.room', 'r')
            ->orderBy('l.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $query->andWhere('r.buildingId IN (:buildingIds)')
            ->setParameter('buildingIds', $myBuildingIds);

        if ($status == 'all') {
            $query->andWhere('l.status != :status')
                ->setParameter('status', Lease::LEASE_STATUS_DRAFTING);
        } else {
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
                    $query->andWhere('l.lessee LIKE :keywordSearch');
                    break;
                case ProductAppointment::KEYWORD_ROOM:
                    $query->andWhere('r.name LIKE :keywordSearch');
                    break;
                case ProductAppointment::KEYWORD_NUMBER:
                    $query->andWhere('a.appointmentNumber LIKE :keywordSearch');
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

        if (!is_null($startDate) && !empty($startDate)) {
            $startDate = new \DateTime($startDate);
            $startDate->setTime(0, 0, 0);

            $query->andWhere('l.endRentDate > :startDate')
                ->setParameter('startDate', $startDate);
        }

        if (!is_null($endDate) && !empty($endDate)) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            $query->andWhere('l.startRentDate <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $userId
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getClientLeases(
        $userId,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('l')
            ->leftJoin('l.supervisor', 'u')
            ->where('u.id = :userId')
            ->andWhere('l.status != :status')
            ->orderBy('l.creationDate')
            ->setParameter('userId', $userId)
            ->setParameter('status', Lease::LEASE_STATUS_DRAFTING);

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }
}
