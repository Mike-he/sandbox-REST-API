<?php

namespace Sandbox\ApiBundle\Repository\Product;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Sandbox\ApiBundle\Entity\Product\ProductAppointment;

class ProductAppointmentRepository extends EntityRepository
{
    /**
     * @param $productId
     * @param $status
     *
     * @return mixed
     */
    public function countProductAppointment(
        $productId,
        $status = null
    ) {
        $query = $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->where('a.productId = :productId')
            ->setParameter('productId', $productId);

        if (!is_null($status)) {
            $query->andWhere('a.status = :status')
                ->setParameter('status', $status);
        }

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $buildingId
     * @param $myBuildingIds
     * @param $status
     * @param $keyword
     * @param $search
     * @param $createRange
     * @param $createStart
     * @param $createEnd
     * @param $startDate
     * @param $endDate
     *
     * @return int
     */
    public function countSalesProductAppointments(
        $buildingId,
        $myBuildingIds,
        $status,
        $keyword,
        $search,
        $createRange,
        $createStart,
        $createEnd,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('a')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = a.productId')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->select('COUNT(a)')
            ->where('a.id is not null');

        $query = $this->getQueryForSalesProductAppointments(
            $query,
            $buildingId,
            $myBuildingIds,
            $status,
            $keyword,
            $search,
            $createRange,
            $createStart,
            $createEnd,
            $startDate,
            $endDate
        );

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $productId
     * @param $status
     *
     * @return mixed
     */
    public function getSalesProductAppointments(
        $buildingId,
        $myBuildingIds,
        $status,
        $keyword,
        $search,
        $createRange,
        $createStart,
        $createEnd,
        $startDate,
        $endDate,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('a')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = a.productId')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->where('a.id is not null')
            ->orderBy('a.creationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $query = $this->getQueryForSalesProductAppointments(
            $query,
            $buildingId,
            $myBuildingIds,
            $status,
            $keyword,
            $search,
            $createRange,
            $createStart,
            $createEnd,
            $startDate,
            $endDate
        );

        return $query->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $query
     * @param $buildingId
     * @param $myBuildingIds
     * @param $status
     * @param $keyword
     * @param $search
     * @param $createRange
     * @param $createStart
     * @param $createEnd
     * @param $startDate
     * @param $endDate
     *
     * @return mixed
     */
    private function getQueryForSalesProductAppointments(
        $query,
        $buildingId,
        $myBuildingIds,
        $status,
        $keyword,
        $search,
        $createRange,
        $createStart,
        $createEnd,
        $startDate,
        $endDate
    ) {
        if (!is_null($buildingId)) {
            $query = $query->andWhere('r.buildingId = :buildingId')
                ->setParameter('buildingId', $buildingId);
        } else {
            $query->andWhere('r.buildingId IN (:buildingIds)')
                ->setParameter('buildingIds', $myBuildingIds);
        }

        if (!is_null($status)) {
            $query->andWhere('a.status = :status')
                ->setParameter('status', $status);
        }

        if (!is_null($keyword) &&
            !empty($keyword) &&
            !is_null($search) &&
            !empty($search)
        ) {
            switch ($keyword) {
                case ProductAppointment::KEYWORD_APPLICANT:
                    $query->andWhere('a.applicantCompany LIKE :search');
                    break;
                case ProductAppointment::KEYWORD_ROOM:
                    $query->andWhere('r.name LIKE :search');
                    break;
                case ProductAppointment::KEYWORD_NUMBER:
                    $query->andWhere('a.appointmentNumber LIKE :search');
                    break;
                default:
                    return array();
            }

            $query->setParameter('search', $search);
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

            $query->andWhere('a.creationDate >= :last')
                ->setParameter('last', $last);
        } else {
            if (!is_null($createStart) && !empty($createStart)) {
                $createStart = new \DateTime($createStart);
                $createStart->setTime(0, 0, 0);

                $query->andWhere('a.creationDate >= :createStart')
                    ->setParameter('createStart', $createStart);
            }

            if (!is_null($createEnd) && !empty($createEnd)) {
                $createEnd = new \DateTime($createEnd);
                $createEnd->setTime(23, 59, 59);

                $query->andWhere('a.creationDate <= :createEnd')
                    ->setParameter('createEnd', $createEnd);
            }
        }

        if (!is_null($startDate) && !empty($startDate)) {
            $startDate = new \DateTime($startDate);
            $startDate->setTime(0, 0, 0);

            $query->andWhere('a.endRentDate > :startDate')
                ->setParameter('startDate', $startDate);
        }

        if (!is_null($endDate) && !empty($endDate)) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            $query->andWhere('a.startRentDate <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        return $query;
    }
}
