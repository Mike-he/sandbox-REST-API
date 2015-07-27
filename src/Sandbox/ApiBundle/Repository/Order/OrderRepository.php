<?php

namespace Sandbox\ApiBundle\Repository\Order;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Symfony\Component\Validator\Constraints\DateTime;

class OrderRepository extends EntityRepository
{
    public function getOrdersByUser(
        $userId
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('o')
            ->where('o.userId = :userId')
            ->andWhere('o.status <> \'cancelled\'')
            ->andWhere('o.endDate > :now')
            ->orderBy('o.startDate', 'ASC')
            ->setParameter('userId', $userId)
            ->setParameter('now', $now)
            ->getQuery();

        return $query->getResult();
    }

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
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.productId = :productId')
            ->andWhere('o.status <> \'cancelled\'')
            ->andWhere(
                '(
                    (o.startDate <= :startDate AND o.endDate > :startDate) OR
                    (o.startDate < :endDate AND o.endDate >= :endDate)
                )'
            )
            ->setParameter('productId', $productId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Get Booked Times for Meeting Room.
     *
     * @param $id
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    public function getTimesByDate(
        $id,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.productId = :productId')
            ->andWhere('o.status <> \'cancelled\'')
            ->andWhere('o.startDate >= :startDate')
            ->andWhere('o.endDate <= :endDate')
            ->orderBy('o.startDate', 'ASC')
            ->setParameter('productId', $id)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Get Booked Times for Room.
     *
     * @param $id
     *
     * @return array
     */
    public function getBookedDates(
        $id
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.productId = :productId')
            ->andWhere('o.status <> \'cancelled\'')
            ->orderBy('o.startDate', 'ASC')
            ->setParameter('productId', $id)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Get list of orders for admin.
     *
     * @param String       $type
     * @param RoomCity     $city
     * @param RoomBuilding $building
     * @param int          $userId
     * @param DateTime     $startDate
     * @param DateTime     $endDate
     *
     * @return array
     */
    public function getOrdersForAdmin(
        $type,
        $city,
        $building,
        $userId,
        $startDate,
        $endDate
    ) {
        $parameters = [];

        $query = $this->createQueryBuilder('o')
            ->select('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId');

        // filter by user id
        if (!is_null($userId)) {
            $query->where('o.userId = :userId');
            $parameters['userId'] = $userId;
        } else {
            $query->where('o.status != :unpaid');
            $parameters['unpaid'] = 'unpaid';
        }

        // filter by type
        if (!is_null($type)) {
            $query->andWhere('r.type = :type');
            $parameters['type'] = $type;
        }

        // filter by city
        if (!is_null($city)) {
            $query->andWhere('r.city = :city');
            $parameters['city'] = $city;
        }

        // filter by building
        if (!is_null($building)) {
            $query->andWhere('r.building = :building');
            $parameters['building'] = $building;
        }

        //filter by start date
        if (!is_null($startDate)) {
            $query->andWhere('o.startDate >= :startDate');
            $parameters['startDate'] = $startDate;
        }

        //filter by end date
        if (!is_null($endDate)) {
            $query->andWhere('o.endDate <= :endDate');
            $parameters['endDate'] = $endDate;
        }

        //set all parameters
        $query->setParameters($parameters);

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
