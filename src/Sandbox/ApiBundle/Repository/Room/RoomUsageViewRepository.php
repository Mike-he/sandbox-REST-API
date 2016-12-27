<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;

/**
 * RoomUsageViewRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RoomUsageViewRepository extends EntityRepository
{
    /**
     * Seek all users that rented room.
     *
     * @param $productId
     * @param $start
     * @param $end
     *
     * @return array
     */
    public function getRoomUsersUsage(
        $productId,
        $start,
        $end,
        $seat = null
    ) {
        $query = $this->createQueryBuilder('r')
            ->leftJoin('SandboxApiBundle:Order\ProductOrder', 'o', 'WITH', 'r.id = o.id')
            ->where('r.productId = :productId')
            ->andWhere('o.rejected = FALSE')
            ->andWhere('r.status = \'paid\' OR r.status = \'completed\'')
            ->andWhere('
                (r.startDate <= :start AND r.endDate > :start) OR
                (r.startDate < :end AND r.endDate >= :end) OR
                (r.startDate >= :start AND r.endDate <= :end)
            ')
            ->setParameter('productId', $productId)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if (!is_null($seat)) {
            $query->andWhere('o.seatId = :seat')
                ->setParameter('seat', $seat);
        }

        return $query->getQuery()->getResult();
    }

    //-------------------- sales room repository --------------------//

    /**
     * Seek all users that rented room.
     *
     * @param $productId
     * @param $start
     * @param $end
     *
     * @return array
     */
    public function getSalesRoomUsersUsage(
        $productId,
        $start,
        $end,
        $seat = null
    ) {
        $query = $this->createQueryBuilder('r')
            ->leftJoin('SandboxApiBundle:Order\ProductOrder', 'o', 'WITH', 'r.id = o.id')
            ->where('r.productId = :productId')
            ->andWhere('o.rejected = FALSE')
            ->andWhere('r.status = \'paid\' OR r.status = \'completed\'')
            ->andWhere('
                (r.startDate <= :start AND r.endDate > :start) OR
                (r.startDate < :end AND r.endDate >= :end) OR
                (r.startDate >= :start AND r.endDate <= :end)
            ')
            ->setParameter('productId', $productId)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if (!is_null($seat)) {
            $query->andWhere('o.seatId = :seat')
                ->setParameter('seat', $seat);
        }

        return $query->getQuery()->getResult();
    }
}
