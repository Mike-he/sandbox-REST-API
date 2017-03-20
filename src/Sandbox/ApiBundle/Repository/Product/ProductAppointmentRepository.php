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
     * @param $myBuildingIds
     * @param $status
     * @param $keyword
     * @param $search
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
     * @return int
     */
    public function countSalesProductAppointments(
        $myBuildingIds,
        $status,
        $keyword,
        $search,
        $createRange,
        $createStart,
        $createEnd,
        $rentFilter,
        $startDate,
        $endDate,
        $companyId = null,
        $roomId = null,
        $userId = null
    ) {
        $query = $this->createQueryBuilder('a')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = a.productId')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->select('COUNT(a)')
            ->where('a.id is not null');

        $query = $this->getQueryForSalesProductAppointments(
            $query,
            $myBuildingIds,
            $status,
            $keyword,
            $search,
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

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $myBuildingIds
     * @param $status
     * @param $keyword
     * @param $search
     * @param $createRange
     * @param $createStart
     * @param $createEnd
     * @param $rentFilter
     * @param $startDate
     * @param $endDate
     * @param $limit
     * @param $offset
     * @param null $companyId
     * @param null $roomId
     * @param $userId
     *
     * @return mixed
     */
    public function getSalesProductAppointments(
        $myBuildingIds,
        $status,
        $keyword,
        $search,
        $createRange,
        $createStart,
        $createEnd,
        $rentFilter,
        $startDate,
        $endDate,
        $limit,
        $offset,
        $companyId = null,
        $roomId = null,
        $userId = null
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
            $myBuildingIds,
            $status,
            $keyword,
            $search,
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

        return $query->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $query
     * @param $myBuildingIds
     * @param $status
     * @param $keyword
     * @param $search
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
     * @return mixed
     */
    private function getQueryForSalesProductAppointments(
        $query,
        $myBuildingIds,
        $status,
        $keyword,
        $search,
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
        if (!is_null($myBuildingIds) && !empty($myBuildingIds)) {
            $query->andWhere('r.buildingId IN (:buildingIds)')
                ->setParameter('buildingIds', $myBuildingIds);
        }

        if (!is_null($companyId) && !empty($companyId)) {
            $query->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'rb', 'WITH', 'r.buildingId = rb.id')
                ->andWhere('rb.companyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        if (!is_null($roomId) && !empty($roomId)) {
            $query->andWhere('r.id = :roomId')
                ->setParameter('roomId', $roomId);
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

            $query->setParameter('search', "%$search%");
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
                    $query->andWhere('a.startRentDate >= :startDate')
                        ->andWhere('a.startRentDate <= :endDate');
                    break;
                case 'rent_range':
                    $query->andWhere(
                        '(
                            (a.startRentDate <= :startDate AND a.endRentDate > :startDate) OR
                            (a.startRentDate < :endDate AND a.endRentDate >= :endDate) OR
                            (a.startRentDate >= :startDate AND a.endRentDate <= :endDate)
                        )'
                    );
                    break;
                case 'rent_end':
                    $query->andWhere('a.endRentDate >= :startDate')
                        ->andWhere('a.endRentDate <= :endDate');
                    break;
                default:
                    return $query;
            }

            $query->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        if (!is_null($userId)) {
            $query->andWhere('a.userId = :userId')
                ->setParameter('userId', $userId);
        }

        return $query;
    }
}
