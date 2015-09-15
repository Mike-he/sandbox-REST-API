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
     * @param $roomId
     *
     * @return array
     */
    public function getRoomUsersUsage(
        $productId,
        $start,
        $end
    ) {
        $query = $this->createQueryBuilder('r')
            ->where('r.productId = :productId')
            ->andWhere('r.status = \'paid\' OR r.status = \'completed\'')
            ->andWhere('
                (r.startDate <= :start AND r.endDate > :start) OR
                (r.startDate < :end AND r.endDate >= :end) OR
                (r.startDate >= :start AND r.endDate <= :end)
            ')
            ->setParameter('productId', $productId)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return $query->getQuery()->getResult();
    }
}
