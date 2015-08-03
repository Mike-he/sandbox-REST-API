<?php

namespace Sandbox\ApiBundle\Repository\Order;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Symfony\Component\Validator\Constraints\DateTime;

class OrderRepository extends EntityRepository
{
    const COMPLETED = "'completed'";
    const CANCELLED = "'cancelled'";

    /**
     * set status to completed when current time passes start time.
     */
    public function setStatusCompleted()
    {
        $now = new \DateTime();

        $query = $this->createQueryBuilder('o')
            ->update()
            ->set('o.status', self::COMPLETED)
            ->where('o.status = \'paid\'')
            ->andWhere('o.startDate <= :now')
            ->setParameter('now', $now)
            ->getQuery();

        $query->execute();
    }

    /**
     * set status to cancelled after 15 minutes.
     */
    public function setStatusCancelled()
    {
        $now = new \DateTime();
        $start = clone $now;
        $start->modify('-15 minutes');
        $nowString = (string) $now->format('Y-m-d H:i:s');
        $nowString = "'$nowString'";

        $query = $this->createQueryBuilder('o')
            ->update()
            ->set('o.status', self::CANCELLED)
            ->set('o.cancelledDate', $nowString)
            ->where('o.status = \'unpaid\'')
            ->andWhere('o.creationDate <= :start')
            ->setParameter('start', $start)
            ->getQuery();

        $query->execute();
    }

    public function getRenewOrder(
        $userId,
        $productId,
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.userId = :userId')
            ->andWhere('o.status = \'completed\'')
            ->andWhere('o.productId = :productId')
            ->andWhere('o.endDate > :endDate')
            ->setParameter('productId', $productId)
            ->setParameter('userId', $userId)
            ->setParameter('endDate', $endDate)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $userId
     *
     * @return array
     */
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

            $query->andWhere('o.paymentDate IS NOT NULL');
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
        $query->orderBy('o.creationDate', 'DESC');

        //set all parameters
        $query->setParameters($parameters);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * Search orders by order number and  order owner name.
     *
     * @param String $search
     *
     * @return array
     */
    public function getOrdersBySearch(
        $search
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = o.userId')
            ->where('o.orderNumber LIKE :search')
            ->orWhere('up.name LIKE :search')
            ->orderBy('o.creationDate', 'DESC')
            ->setParameter('search', "%$search%");

        return $query->getQuery()->getResult();
    }

    /**
     * Get list of orders for admin.
     *
     * @return array
     */
    public function getOrdersToExport(
        $type,
        $city,
        $building,
        $userId,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->select("
                    CONCAT(rc.name, ', ', rb.name, ', ', r.number, ', ', r.name) as product_name,
                    r.type,
                    o.userId as employee_id,
                    p.unitPrice,
                    o.price as amount,
                    CONCAT(p.startDate, ' - ', p.endDate) as leasing_time,
                    o.creationDate as order_time,
                    o.modificationDate as payment_time,
                    o.status,
                    up.name,
                    up.phone,
                    up.email
                ")
            ->innerJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'o.userId = u.id')
            ->innerJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'u.id = up.userId')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Room\RoomCity', 'rc', 'WITH', 'rc.id = r.city')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'rb', 'WITH', 'rb.id = r.building');

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

        $query->orderBy('o.creationDate', 'DESC');

        //set all parameters
        $query->setParameters($parameters);

        return $query->getQuery()->getArrayResult();
    }
}
