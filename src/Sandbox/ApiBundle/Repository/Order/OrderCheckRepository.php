<?php

namespace Sandbox\ApiBundle\Repository\Order;

use Doctrine\ORM\EntityRepository;

class OrderCheckRepository extends EntityRepository
{
    /**
     * Check for Order Conflict.
     *
     * @param $productId
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    public function checkProductForClient(
        $productId,
        $startDate,
        $endDate,
        $seatId = null
    ) {
        $query = $this->createQueryBuilder('oc')
            ->select('COUNT(oc.id)')
            ->where('oc.productId = :productId')
            ->andWhere(
                '(
                    (oc.startDate <= :startDate AND oc.endDate > :startDate) OR
                    (oc.startDate < :endDate AND oc.endDate >= :endDate) OR
                    (oc.startDate >= :startDate AND oc.endDate <= :endDate)
                )'
            )
            ->setParameter('productId', $productId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if (!is_null($seatId)) {
            $query->andWhere('oc.seatId = :seatId')
                ->setParameter('seatId', $seatId);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * Check for Order Conflict for flexible room.
     *
     * @param $productId
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    public function checkFlexibleForClient(
        $productId,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('oc')
            ->select('COUNT(oc.id)')
            ->where('oc.productId = :productId')
            ->andWhere(
                '(
                    (oc.startDate <= :startDate AND oc.endDate > :startDate) OR
                    (oc.startDate < :endDate AND oc.endDate >= :endDate) OR
                    (oc.startDate >= :startDate AND oc.endDate <= :endDate)
                )'
            )
            ->setParameter('productId', $productId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery();

        return $query->getSingleScalarResult();
    }
}
