<?php

namespace Sandbox\ApiBundle\Repository\Order;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Symfony\Component\Validator\Constraints\DateTime;

class OrderRepository extends EntityRepository
{
    const COMPLETED = "'completed'";
    const CANCELLED = "'cancelled'";

    /**
     * @param $id
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOrderByIdAndStatus(
        $id
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->where('o.id = :id')
            ->andWhere('r.type = :type')
            ->andWhere('o.status = :paid')
            ->setParameter('id', $id)
            ->setParameter('type', Room::TYPE_OFFICE)
            ->setParameter('paid', ProductOrder::STATUS_PAID)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param $productId
     * @param $userId
     * @param $startDate
     * @param $endDate
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOrderFromSameUser(
        $productId,
        $userId,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.status != :status')
            ->andWhere('o.startDate = :startDate')
            ->andWhere('o.endDate = :endDate')
            ->andWhere('o.userId = :userId')
            ->andWhere('o.productId = :productId')
            ->setParameter('productId', $productId)
            ->setParameter('userId', $userId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('status', ProductOrder::STATUS_CANCELLED)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param $now
     * @param $meetingTime
     *
     * @return array
     */
    public function getMeetingStartSoonOrders(
        $now,
        $meetingTime,
        $type
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->where('o.status = \'paid\'')
            ->andWhere('o.startDate > :now')
            ->andWhere('r.type = :type')
            ->andWhere('o.startDate <= :meetingTime')
            ->setParameter('meetingTime', $meetingTime)
            ->setParameter('now', $now)
            ->setParameter('type', $type)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $now
     * @param $meetingTime
     *
     * @return array
     */
    public function getMeetingEndSoonOrders(
        $now,
        $meetingTime,
        $type
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->where('(o.status = \'paid\' OR o.status = \'completed\')')
            ->andWhere('o.endDate > :now')
            ->andWhere('r.type = :type')
            ->andWhere('o.endDate <= :meetingTime')
            ->setParameter('meetingTime', $meetingTime)
            ->setParameter('now', $now)
            ->setParameter('type', $type)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $now
     * @param $workspaceTime
     *
     * @return array
     */
    public function getOfficeStartSoonOrders(
        $now,
        $workspaceTime
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->where('o.status = \'paid\'')
            ->andWhere('o.startDate > :now')
            ->andWhere('(r.type = \'office\' AND o.startDate <= :workspaceTime)')
            ->setParameter('workspaceTime', $workspaceTime)
            ->setParameter('now', $now)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $now
     * @param $allowedTime
     * @param $officeTime
     *
     * @return array
     */
    public function getOfficeEndSoonOrders(
        $now,
        $officeTime,
        $allowedTime
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->where('o.status = \'completed\'')
            ->andWhere('o.endDate > :now')
            ->andWhere(
                '(
                    r.type = \'office\' AND
                    o.endDate <= :officeTime AND
                    o.endDate >= :allowedTime
                )'
            )
            ->setParameter('officeTime', $officeTime)
            ->setParameter('allowedTime', $allowedTime)
            ->setParameter('now', $now)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $now
     * @param $workspaceTime
     *
     * @return array
     */
    public function getWorkspaceStartSoonOrders(
        $now,
        $workspaceTime
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->where('o.status = \'paid\'')
            ->andWhere('o.startDate > :now')
            ->andWhere(
                '(
                    (r.type = \'fixed\' OR r.type = \'flexible\')
                    AND
                    o.startDate <= :workspaceTime
                )'
            )
            ->setParameter('workspaceTime', $workspaceTime)
            ->setParameter('now', $now)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $now
     * @param $workspaceTime
     *
     * @return array
     */
    public function getWorkspaceEndSoonOrders(
        $now,
        $workspaceTime
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->where('o.status = \'completed\'')
            ->andWhere('o.endDate > :now')
            ->andWhere(
                '(
                    (r.type = \'fixed\' OR r.type = \'flexible\')
                    AND
                    o.endDate <= :workspaceTime
                )'
            )
            ->setParameter('workspaceTime', $workspaceTime)
            ->setParameter('now', $now)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param int    $userId
     * @param int    $limit
     * @param int    $offset
     * @param string $search
     *
     * @return array
     */
    public function getUserCurrentOrders(
        $userId,
        $limit,
        $offset,
        $search
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Order\InvitedPeople', 'i', 'WITH', 'i.orderId = o.id')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = o.userId')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->leftJoin('SandboxApiBundle:Room\RoomCity', 'c', 'WITH', 'r.cityId = c.id')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'r.buildingId = b.id')
            ->where(
                '(
                    o.userId = :userId OR
                    o.appointed = :userId OR
                    i.userId = :userId
                )'
            )
            ->andWhere(
                '(
                    o.status = \''.ProductOrder::STATUS_PAID.'\' OR '
                    .'o.status = \''.ProductOrder::STATUS_COMPLETED.'\'
                )')
            ->andWhere('o.startDate <= :now AND o.endDate > :now')
            ->setParameter('now', $now)
            ->setParameter('userId', $userId);

        if (!is_null($search)) {
            $query->andWhere(
                    '(
                        up.name LIKE :search OR
                        c.name LIKE :search OR
                        b.name LIKE :search OR
                        r.type LIKE :search
                    )'
                )
                    ->setParameter('search', "%$search%");
        }

        $query = $query->orderBy('o.modificationDate', 'DESC')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $userId
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getUserCancelledOrders(
        $userId,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.status = \'cancelled\'')
            ->andWhere('o.paymentDate <> \'null\'')
            ->andWhere('o.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('o.modificationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * set status to completed when current time passes start time.
     */
    public function getStatusPaid()
    {
        $now = new \DateTime();

        $query = $this->createQueryBuilder('o')
            ->select('o')
            ->where('o.status = \'paid\'')
            ->andWhere('o.startDate <= :now')
            ->andWhere('o.rejected = :rejected')
            ->setParameter('now', $now)
            ->setParameter('rejected', false)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * get orders that need to set invoice.
     */
    public function getInvoiceOrders()
    {
        $query = $this->createQueryBuilder('o')
            ->select('o')
            ->where('o.status = \'completed\'')
            ->andWhere('o.discountPrice > :price')
            ->andWhere('o.payChannel != :account')
            ->andWhere('o.rejected = :rejected')
            ->andWhere('o.invoiced = :invoiced')
            ->andWhere('o.salesInvoice = :salesInvoice')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('invoiced', false)
            ->setParameter('rejected', false)
            ->setParameter('salesInvoice', false)
            ->setParameter('price', 0)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * get orders that need to set invoice.
     *
     * @param $userId
     * @param $limit
     * @param $offset
     * @param $ids
     *
     * @return array
     */
    public function getInvoiceOrdersForApp(
        $userId,
        $limit = null,
        $offset = null,
        $ids = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'r.buildingId = b.id')
            ->where('o.status = \'completed\'')
            ->andWhere('o.userId = :userId')
            ->andWhere('o.discountPrice > :price')
            ->andWhere('o.payChannel != :account')
            ->andWhere('o.rejected = :rejected')
            ->andWhere('o.invoiced = :invoiced')
            ->andWhere('o.salesInvoice = :salesInvoice')
            ->orderBy('b.companyId', 'ASC')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('invoiced', false)
            ->setParameter('rejected', false)
            ->setParameter('userId', $userId)
            ->setParameter('salesInvoice', true)
            ->setParameter('price', 0);

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        // filter by order ids
        if (!is_null($ids) && !empty($ids)) {
            $query->andWhere('o.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * get order that need to set invoice.
     */
    public function getInvoiceOrdersForInvoiced(
        $id,
        $userId
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.status = \'completed\'')
            ->andWhere('o.userId = :userId')
            ->andWhere('o.discountPrice > :price')
            ->andWhere('o.payChannel != :account')
            ->andWhere('o.rejected = :rejected')
            ->andWhere('o.invoiced = :invoiced')
            ->andWhere('o.salesInvoice = :salesInvoice')
            ->andWhere('o.id = :id')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('invoiced', false)
            ->setParameter('rejected', false)
            ->setParameter('userId', $userId)
            ->setParameter('salesInvoice', true)
            ->setParameter('price', 0)
            ->setParameter('id', $id)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * get order invoice amount.
     */
    public function getInvoiceOrdersAmount(
        $userId
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('SUM(o.discountPrice)')
            ->where('o.status = \'completed\'')
            ->andWhere('o.userId = :userId')
            ->andWhere('o.discountPrice > :price')
            ->andWhere('o.payChannel != :account')
            ->andWhere('o.rejected = :rejected')
            ->andWhere('o.invoiced = :invoiced')
            ->andWhere('o.salesInvoice = :salesInvoice')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('invoiced', false)
            ->setParameter('rejected', false)
            ->setParameter('userId', $userId)
            ->setParameter('salesInvoice', true)
            ->setParameter('price', 0)
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * set status to cancelled after 15 minutes.
     */
    public function setStatusCancelled()
    {
        $now = new \DateTime();
        $start = clone $now;
        $start->modify('-5 minutes');
        $nowString = (string) $now->format('Y-m-d H:i:s');
        $nowString = "'$nowString'";

        $query = $this->createQueryBuilder('o')
            ->update()
            ->set('o.status', self::CANCELLED)
            ->set('o.cancelledDate', $nowString)
            ->set('o.modificationDate', $nowString)
            ->where('o.status = \'unpaid\'')
            ->andWhere('(o.type != :preorder OR o.type is NULL)')
            ->andWhere('o.creationDate <= :start')
            ->setParameter('preorder', ProductOrder::PREORDER_TYPE)
            ->setParameter('start', $start)
            ->getQuery();

        $query->execute();
    }

    /**
     * get unpaid preorder product orders.
     */
    public function getUnpaidPreOrders()
    {
        $query = $this->createQueryBuilder('o')
            ->where('o.status = \'unpaid\'')
            ->andWhere('o.type = :preorder')
            ->setParameter('preorder', ProductOrder::PREORDER_TYPE)
            ->getQuery();

        return $query->getResult();
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

        return $query->getOneOrNullResult();
    }

    public function getAlreadyRenewedOrder(
        $userId,
        $productId
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.userId = :userId')
            ->andWhere('o.status = \'unpaid\'')
            ->orWhere('o.status = \'paid\'')
            ->andWhere('o.productId = :productId')
            ->andWhere('o.isRenew = \'true\'')
            ->setParameter('productId', $productId)
            ->setParameter('userId', $userId)
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
            ->andWhere('o.rejected = :rejected')
            ->andWhere(
                '(
                    (o.startDate <= :startDate AND o.endDate > :startDate) OR
                    (o.startDate < :endDate AND o.endDate >= :endDate) OR
                    (o.startDate >= :startDate AND o.endDate <= :endDate)
                )'
            )
            ->setParameter('productId', $productId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('rejected', false)
            ->getQuery();

        return $query->getResult();
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
        $query = $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.productId = :productId')
            ->andWhere('o.status <> \'cancelled\'')
            ->andWhere(
                '(
                    (o.startDate <= :startDate AND o.endDate > :startDate) OR
                    (o.startDate < :endDate AND o.endDate >= :endDate) OR
                    (o.startDate >= :startDate AND o.endDate <= :endDate)
                )'
            )
            ->setParameter('productId', $productId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery();

        return $query->getSingleScalarResult();
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
        $now = new \DateTime('now');
        $query = $this->createQueryBuilder('o')
            ->where('o.productId = :productId')
            ->andWhere('o.status <> \'cancelled\'')
            ->andWhere('o.endDate > :now')
            ->andWhere('o.rejected = :rejected')
            ->orderBy('o.startDate', 'ASC')
            ->setParameter('productId', $id)
            ->setParameter('now', $now)
            ->setParameter('rejected', false)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Get rejected office orders.
     *
     * @param $productId
     * @param $startDate
     * @param $endDate
     * @param $userId
     * @param $orderId
     *
     * @return array
     */
    public function getOfficeRejected(
        $productId,
        $startDate,
        $endDate,
        $userId = null,
        $orderId = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.productId = :productId')
            ->andWhere('o.status <> \'cancelled\'')
            ->andWhere('o.rejected = :rejected')
            ->andWhere(
                '(o.startDate <= :startDate AND o.endDate > :startDate) OR
                (o.startDate < :endDate AND o.endDate >= :endDate) OR
                (o.startDate >= :startDate AND o.endDate <= :endDate)'
            )
            ->setParameter('productId', $productId)
            ->setParameter('rejected', true)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if (!is_null($userId)) {
            $query = $query->andWhere('o.userId = :userId')
                ->setParameter('userId', $userId);
        }

        if (!is_null($orderId)) {
            $query = $query->andWhere('o.id != :orderId')
                ->setParameter('orderId', $orderId);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Get accetped office orders.
     *
     * @param $productId
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    public function getOfficeAccepted(
        $productId,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.productId = :productId')
            ->andWhere('o.status <> \'cancelled\'')
            ->andWhere('o.rejected = :rejected')
            ->andWhere(
                '(o.startDate <= :startDate AND o.endDate > :startDate) OR
                (o.startDate < :endDate AND o.endDate >= :endDate) OR
                (o.startDate >= :startDate AND o.endDate <= :endDate)'
            )
            ->setParameter('productId', $productId)
            ->setParameter('rejected', false)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return $query->getQuery()->getResult();
    }

    /**
     * Get Booked Times for flexible.
     *
     * @param $id
     *
     * @return array
     */
    public function getFlexibleBookedDates(
        $id,
        $monthStart,
        $monthEnd
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.productId = :productId')
            ->andWhere('o.status <> \'cancelled\'')
            ->andWhere(
                '(o.startDate >= :monthStart AND o.startDate <= :monthEnd)
                 OR
                 (o.endDate >= :monthStart AND o.endDate <= :monthEnd)
                 '
            )
            ->setParameter('productId', $id)
            ->setParameter('monthStart', $monthStart)
            ->setParameter('monthEnd', $monthEnd)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Get list of orders for admin.
     *
     * @param string       $channel
     * @param string       $type
     * @param RoomCity     $city
     * @param RoomBuilding $building
     * @param int          $userId
     * @param DateTime     $startDate
     * @param DateTime     $endDate
     * @param DateTime     $payStart
     * @param DateTime     $payEnd
     * @param string       $search
     * @param DateTime     $orderStartPoint
     * @param DateTime     $orderEndPoint
     * @param string       $refundStatus
     *
     * @return array
     */
    public function getOrdersForAdmin(
        $channel,
        $type,
        $city,
        $building,
        $userId,
        $startDate,
        $endDate,
        $payStart,
        $payEnd,
        $search,
        $orderStartPoint,
        $orderEndPoint,
        $refundStatus
    ) {
        $parameters = [];

        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:Order\ProductOrderRecord', 'por', 'WITH', 'por.orderId = o.id')
            ->where('((o.status != :unpaid) AND (o.paymentDate IS NOT NULL) OR (o.type = :preOrder))');

        $parameters['preOrder'] = ProductOrder::PREORDER_TYPE;
        $parameters['unpaid'] = ProductOrder::STATUS_UNPAID;

        //only needed when searching orders
        if (!is_null($search)) {
            $query->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = o.userId');
        }

        // filter by payment channel
        if (!is_null($channel)) {
            $query->andWhere('o.payChannel = :channel');
            $parameters['channel'] = $channel;
        }

        // filter by user id
        if (!is_null($userId)) {
            $query->andWhere('o.userId = :userId');
            $parameters['userId'] = $userId;
        }

        // filter by type
        if (!is_null($type)) {
            $query->andWhere('por.roomType = :type');
            $parameters['type'] = $type;
        }

        // filter by city
        if (!is_null($city)) {
            $query->andWhere('por.cityId = :city');
            $parameters['city'] = $city;
        }

        // filter by building
        if (!is_null($building)) {
            $query->andWhere('por.buildingId = :building');
            $parameters['building'] = $building;
        }

        //filter by start date
        if (!is_null($startDate)) {
            $startDate = new \DateTime($startDate);
            $query->andWhere('o.endDate > :startDate');
            $parameters['startDate'] = $startDate;
        }

        //filter by end date
        if (!is_null($endDate)) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);
            $query->andWhere('o.startDate <= :endDate');
            $parameters['endDate'] = $endDate;
        }

        //filter by payStart
        if (!is_null($payStart)) {
            $payStart = new \DateTime($payStart);
            $query->andWhere('o.creationDate >= :payStart');
            $parameters['payStart'] = $payStart;
        }

        //filter by payEnd
        if (!is_null($payEnd)) {
            $payEnd = new \DateTime($payEnd);
            $payEnd->setTime(23, 59, 59);
            $query->andWhere('o.creationDate <= :payEnd');
            $parameters['payEnd'] = $payEnd;
        }

        //Search orders by order number and order owner name.
        if (!is_null($search)) {
            $query->andWhere('(o.orderNumber LIKE :search OR up.name LIKE :search)');
            $parameters['search'] = "%$search%";
        }

        // filter by order start point
        if (!is_null($orderStartPoint)) {
            $orderStartPoint = new \DateTime($orderStartPoint);
            $orderStartPoint->setTime(00, 00, 00);
            $query->andWhere('o.startDate >= :orderStartPoint');
            $parameters['orderStartPoint'] = $orderStartPoint;

            // filter by order end point
            if (!is_null($orderEndPoint)) {
                $orderEndPoint = new \DateTime($orderEndPoint);
                $orderEndPoint->setTime(23, 59, 59);
                $query->andWhere('o.startDate <= :orderEndPoint');
                $parameters['orderEndPoint'] = $orderEndPoint;
            }
        }

        if ($refundStatus == ProductOrder::REFUNDED_STATUS) {
            $query->andWhere('o.refunded = :refunded');
            $parameters['refunded'] = true;
        }

        //order by
        $query->orderBy('o.creationDate', 'DESC');

        //set all parameters
        $query->setParameters($parameters);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * Get list of orders for admin.
     *
     * @param string   $channel
     * @param string   $type
     * @param int      $city
     * @param int      $building
     * @param int      $userId
     * @param datetime $startDate
     * @param datetime $endDate
     * @param          $payStart
     * @param          $payEnd
     *
     * @return array
     */
    public function getOrdersToExport(
        $channel,
        $type,
        $city,
        $building,
        $userId,
        $startDate,
        $endDate,
        $payStart,
        $payEnd,
        $orderStartPoint,
        $orderEndPoint
    ) {
        $query = $this->createQueryBuilder('o')
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

        // only export order that is paid
        $query->andWhere('o.paymentDate IS NOT NULL');

        // filter by payment channel
        if (!is_null($channel)) {
            $query->andWhere('o.payChannel = :channel');
            $parameters['channel'] = $channel;
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

        // filter by start date
        if (!is_null($startDate)) {
            $startDate = new \DateTime($startDate);
            $query->andWhere('o.endDate > :startDate');
            $parameters['startDate'] = $startDate;
        }

        // filter by end date
        if (!is_null($endDate)) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);
            $query->andWhere('o.startDate <= :endDate');
            $parameters['endDate'] = $endDate;
        }

        // filter by payStart
        if (!is_null($payStart)) {
            $payStart = new \DateTime($payStart);
            $query->andWhere('o.creationDate >= :payStart');
            $parameters['payStart'] = $payStart;
        }

        // filter by payEnd
        if (!is_null($payEnd)) {
            $payEnd = new \DateTime($payEnd);
            $payEnd->setTime(23, 59, 59);
            $query->andWhere('o.creationDate <= :payEnd');
            $parameters['payEnd'] = $payEnd;
        }

        // filter by order start point
        if (!is_null($orderStartPoint)) {
            $orderStartPoint = new \DateTime($orderStartPoint);
            $orderStartPoint->setTime(00, 00, 00);
            $query->andWhere('o.startDate >= :orderStartPoint');
            $parameters['orderStartPoint'] = $orderStartPoint;

            // filter by order end point
            if (!is_null($orderEndPoint)) {
                $orderEndPoint = new \DateTime($orderEndPoint);
                $orderEndPoint->setTime(23, 59, 59);
                $query->andWhere('o.startDate <= :orderEndPoint');
                $parameters['orderEndPoint'] = $orderEndPoint;
            }
        }

        $query->orderBy('o.creationDate', 'DESC');

        //set all parameters
        $query->setParameters($parameters);

        return $query->getQuery()->getResult();
    }

    //-------------------- sales repository --------------------//

    /**
     * Get list of orders for admin.
     *
     * @param string       $channel
     * @param string       $type
     * @param RoomCity     $city
     * @param RoomBuilding $building
     * @param int          $userId
     * @param DateTime     $startDate
     * @param DateTime     $endDate
     * @param              $payStart
     * @param              $payEnd
     * @param string       $search
     * @param array        $myBuildingIds
     * @param DateTime     $orderStartPoint
     * @param DateTime     $orderEndPoint
     *
     * @return array
     */
    public function getSalesOrdersForAdmin(
        $channel,
        $type,
        $city,
        $building,
        $userId,
        $startDate,
        $endDate,
        $payStart,
        $payEnd,
        $search,
        $myBuildingIds,
        $orderStartPoint,
        $orderEndPoint
    ) {
        $parameters = [];

        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:Order\ProductOrderRecord', 'por', 'WITH', 'por.orderId = o.id')
            ->where('((o.status != :unpaid) AND (o.paymentDate IS NOT NULL) OR (o.type = :preOrder))');

        $parameters['preOrder'] = ProductOrder::PREORDER_TYPE;
        $parameters['unpaid'] = ProductOrder::STATUS_UNPAID;

        //only needed when searching orders
        if (!is_null($search)) {
            $query->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = o.userId');
        }

        // filter by payment channel
        if (!is_null($channel)) {
            $query->andWhere('o.payChannel = :channel');
            $parameters['channel'] = $channel;
        }

        // filter by user id
        if (!is_null($userId)) {
            $query->andWhere('o.userId = :userId');
            $parameters['userId'] = $userId;
        }

        // filter by type
        if (!is_null($type)) {
            $query->andWhere('por.roomType = :type');
            $parameters['type'] = $type;
        }

        // filter by city
        if (!is_null($city)) {
            $query->andWhere('por.cityId = :city');
            $parameters['city'] = $city;
        }

        // filter by building
        if (!is_null($building)) {
            $query->andWhere('por.buildingId = :building');
            $parameters['building'] = $building;
        } else {
            $query->andWhere('por.buildingId IN (:buildingIds)');
            $parameters['buildingIds'] = $myBuildingIds;
        }

        //filter by start date
        if (!is_null($startDate)) {
            $startDate = new \DateTime($startDate);
            $query->andWhere('o.endDate > :startDate');
            $parameters['startDate'] = $startDate;
        }

        //filter by end date
        if (!is_null($endDate)) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);
            $query->andWhere('o.startDate <= :endDate');
            $parameters['endDate'] = $endDate;
        }

        //filter by payStart
        if (!is_null($payStart)) {
            $payStart = new \DateTime($payStart);
            $query->andWhere('o.creationDate >= :payStart');
            $parameters['payStart'] = $payStart;
        }

        //filter by payEnd
        if (!is_null($payEnd)) {
            $payEnd = new \DateTime($payEnd);
            $payEnd->setTime(23, 59, 59);
            $query->andWhere('o.creationDate <= :payEnd');
            $parameters['payEnd'] = $payEnd;
        }

        //Search orders by order number and order owner name.
        if (!is_null($search)) {
            $query->andWhere('(o.orderNumber LIKE :search OR up.name LIKE :search)');
            $parameters['search'] = "%$search%";
        }

        // filter by order start point
        if (!is_null($orderStartPoint)) {
            $orderStartPoint = new \DateTime($orderStartPoint);
            $orderStartPoint->setTime(00, 00, 00);
            $query->andWhere('o.startDate >= :orderStartPoint');
            $parameters['orderStartPoint'] = $orderStartPoint;

            // filter by order end point
            if (!is_null($orderEndPoint)) {
                $orderEndPoint = new \DateTime($orderEndPoint);
                $orderEndPoint->setTime(23, 59, 59);
                $query->andWhere('o.startDate <= :orderEndPoint');
                $parameters['orderEndPoint'] = $orderEndPoint;
            }
        }

        //order by
        $query->orderBy('o.creationDate', 'DESC');

        //set all parameters
        $query->setParameters($parameters);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * Get list of orders for admin.
     *
     * @param string   $channel
     * @param string   $type
     * @param int      $city
     * @param int      $building
     * @param int      $userId
     * @param datetime $startDate
     * @param datetime $endDate
     * @param          $payStart
     * @param          $payEnd
     * @param array    $myBuildingIds
     * @param DateTime $orderStartPoint
     * @param DateTime $orderEndPoint
     *
     * @return array
     */
    public function getSalesOrdersToExport(
        $channel,
        $type,
        $city,
        $building,
        $userId,
        $startDate,
        $endDate,
        $payStart,
        $payEnd,
        $myBuildingIds,
        $orderStartPoint,
        $orderEndPoint
    ) {
        $query = $this->createQueryBuilder('o')
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

        // only export order that is paid
        $query->andWhere('o.paymentDate IS NOT NULL');

        // filter by payment channel
        if (!is_null($channel)) {
            $query->andWhere('o.payChannel = :channel');
            $parameters['channel'] = $channel;
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
        } else {
            $query->andWhere('r.buildingId IN (:buildingIds)');
            $parameters['buildingIds'] = $myBuildingIds;
        }

        // filter by start date
        if (!is_null($startDate)) {
            $startDate = new \DateTime($startDate);
            $query->andWhere('o.endDate > :startDate');
            $parameters['startDate'] = $startDate;
        }

        // filter by end date
        if (!is_null($endDate)) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);
            $query->andWhere('o.startDate <= :endDate');
            $parameters['endDate'] = $endDate;
        }

        // filter by payStart
        if (!is_null($payStart)) {
            $payStart = new \DateTime($payStart);
            $query->andWhere('o.creationDate >= :payStart');
            $parameters['payStart'] = $payStart;
        }

        // filter by payEnd
        if (!is_null($payEnd)) {
            $payEnd = new \DateTime($payEnd);
            $payEnd->setTime(23, 59, 59);
            $query->andWhere('o.creationDate <= :payEnd');
            $parameters['payEnd'] = $payEnd;
        }

        // filter by order start point
        if (!is_null($orderStartPoint)) {
            $orderStartPoint = new \DateTime($orderStartPoint);
            $orderStartPoint->setTime(00, 00, 00);
            $query->andWhere('o.startDate >= :orderStartPoint');
            $parameters['orderStartPoint'] = $orderStartPoint;

            // filter by order end point
            if (!is_null($orderEndPoint)) {
                $orderEndPoint = new \DateTime($orderEndPoint);
                $orderEndPoint->setTime(23, 59, 59);
                $query->andWhere('o.startDate <= :orderEndPoint');
                $parameters['orderEndPoint'] = $orderEndPoint;
            }
        }

        $query->orderBy('o.creationDate', 'DESC');

        //set all parameters
        $query->setParameters($parameters);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $buildingIds
     *
     * @return array
     */
    public function getMySalesUsersByOrders(
        $buildingIds
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('DISTINCT o.userId')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->where('r.buildingId IN (:buildingIds)')
            ->setParameter('buildingIds', $buildingIds);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $building
     *
     * @return mixed
     */
    public function countsOrderByBuilding(
        $building
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->where('r.building = :building')
            ->setParameter('building', $building);

        return $query->getQuery()->getSingleScalarResult();
    }
}
