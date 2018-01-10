<?php

namespace Sandbox\ApiBundle\Repository\Order;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Order\OrderOfflineTransfer;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;

class OrderRepository extends EntityRepository
{
    const COMPLETED = "'completed'";
    const CANCELLED = "'cancelled'";

    /**
     * @param $userId
     * @param $customerIds
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getUserAllOrders(
        $userId,
        $customerIds,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.userId = :userId OR o.customerId in (:customerIds)')
            ->setParameter('userId', $userId)
            ->setParameter('customerIds', $customerIds)
            ->orderBy('o.modificationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $userId
     * @param $customerIds
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getUserIncompleteOrders(
        $userId,
        $customerIds,
        $limit,
        $offset
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('o')
            ->where('
                    o.status = \'paid\' OR 
                    o.status = \'unpaid\' OR
                    (o.status = \'completed\' and o.endDate >= :now)
                ')
            ->andWhere('o.userId = :userId OR o.customerId in (:customerIds)')
            ->setParameter('userId', $userId)
            ->setParameter('customerIds', $customerIds)
            ->setParameter('now', $now)
            ->orderBy('o.modificationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $userId
     * @param $customerIds
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getUserPendingOrders(
        $userId,
        $customerIds,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.status = \'paid\' OR o.status = \'unpaid\'')
            ->andWhere('o.userId = :userId OR o.customerId in (:customerIds)')
            ->setParameter('userId', $userId)
            ->setParameter('customerIds', $customerIds)
            ->orderBy('o.modificationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $userId
     * @param $customerIds
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getUserCompletedOrders(
        $userId,
        $customerIds,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('
                o.status = \'completed\' OR 
                (o.status = \'cancelled\' AND o.payChannel IS NULL) OR
                (
                    o.status = \'cancelled\' AND 
                    o.payChannel = :offline AND
                    o.needToRefund = :needToRefund AND 
                    o.refunded = :refunded
                )    
            ')
            ->andWhere('o.userId = :userId OR o.customerId in (:customerIds)')
            ->setParameter('userId', $userId)
            ->setParameter('customerIds', $customerIds)
            ->setParameter('needToRefund', false)
            ->setParameter('refunded', false)
            ->setParameter('offline', ProductOrder::CHANNEL_OFFLINE)
            ->orderBy('o.modificationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $userId
     * @param $customerIds
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getUserRefundOrders(
        $userId,
        $customerIds,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('
                (o.needToRefund = :needToRefund OR
                o.refunded = :refunded)
            ')
            ->andWhere('o.userId = :userId OR o.customerId in (:customerIds)')
            ->setParameter('userId', $userId)
            ->setParameter('customerIds', $customerIds)
            ->setParameter('needToRefund', true)
            ->setParameter('refunded', true)
            ->orderBy('o.modificationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery();

        return $query->getResult();
    }

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
            ->andWhere('o.rejected = :rejected')
            ->andWhere('o.startDate > :now')
            ->andWhere('(r.type = \'office\' AND o.startDate <= :workspaceTime)')
            ->setParameter('workspaceTime', $workspaceTime)
            ->setParameter('now', $now)
            ->setParameter('rejected', false)
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
     * @param $type
     *
     * @return array
     */
    public function getWorkspaceStartSoonOrders(
        $now,
        $workspaceTime,
        $type
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->where('o.status = \'paid\'')
            ->andWhere('o.startDate > :now')
            ->andWhere(
                '(
                    (r.type = :type)
                    AND
                    o.startDate <= :workspaceTime
                )'
            )
            ->setParameter('workspaceTime', $workspaceTime)
            ->setParameter('now', $now)
            ->setParameter('type', $type)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $now
     * @param $workspaceTime
     * @param $type
     *
     * @return array
     */
    public function getWorkspaceEndSoonOrders(
        $now,
        $workspaceTime,
        $type
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->where('o.status = \'completed\'')
            ->andWhere('o.endDate > :now')
            ->andWhere(
                '(
                    (r.type = :type)
                    AND
                    o.endDate <= :workspaceTime
                )'
            )
            ->setParameter('workspaceTime', $workspaceTime)
            ->setParameter('now', $now)
            ->setParameter('type', $type)
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
        $search
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('o')
            ->select('o.id,o.userId, o.startDate, o.endDate, up.name as username, b.address, r.name, r.type, p.roomId, o.productId, o.creationDate')
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
            ->andWhere('o.endDate > :now')
            ->setParameter('now', $now)
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
     * @param $type
     * @param $buildingId
     * @param $orderStartDate
     * @param $orderEndDate
     * @param $payStartDate
     * @param $payEndDate
     * @param $rentStartDate
     * @param $rentEndDate
     * @param $invoiceStartDate
     * @param $invoiceEndDate
     * @param null $salesCompanyId
     *
     * @return array
     */
    public function getAdminNotInvoicedOrders(
        $type,
        $buildingId,
        $orderStartDate,
        $orderEndDate,
        $payStartDate,
        $payEndDate,
        $rentStartDate,
        $rentEndDate,
        $invoiceStartDate,
        $invoiceEndDate,
        $salesCompanyId = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'r.buildingId = b.id')
            ->where('o.status = \'completed\'')
            ->andWhere('o.discountPrice > :price')
            ->andWhere('o.payChannel != :account')
            ->andWhere('o.rejected = :rejected')
            ->andWhere('o.invoiced = :invoiced')
            ->andWhere('o.salesInvoice = :salesInvoice')
            ->orderBy('b.companyId', 'ASC')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('invoiced', false)
            ->setParameter('rejected', false)
            ->setParameter('salesInvoice', true)
            ->setParameter('price', 0);

        // filter by type
        if (!is_null($type)) {
            $query->andWhere('r.type = :type')
                ->setParameter('type', $type);
        }

        // filter by building
        if (!is_null($buildingId)) {
            $query->andWhere('b.id = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        // filter by order create start date
        if (!is_null($orderStartDate)) {
            $query->andWhere('o.creationDate > :orderStartDate')
                ->setParameter('orderStartDate', $orderStartDate);
        }

        // filter by order create end date
        if (!is_null($orderEndDate)) {
            $query->andWhere('o.creationDate < :orderEndDate')
                ->setParameter('orderEndDate', $orderEndDate);
        }

        // filter by pay start date
        if (!is_null($payStartDate)) {
            $query->andWhere('o.paymentDate > :payStartDate')
                ->setParameter('payStartDate', $payStartDate);
        }

        // filter by pay end date
        if (!is_null($payEndDate)) {
            $query->andWhere('o.paymentDate < :payEndDate')
                ->setParameter('payEndDate', $payEndDate);
        }

        // filter by rent start date
        if (!is_null($rentStartDate)) {
            $query->andWhere('o.endDate > :rentStartDate')
                ->setParameter('rentStartDate', $rentStartDate);
        }

        // filter by rent end date
        if (!is_null($rentEndDate)) {
            $query->andWhere('o.startDate < :rentEndDate')
                ->setParameter('rentEndDate', $rentEndDate);
        }

        // filter by invoice start date
        if (!is_null($invoiceStartDate)) {
            $query->andWhere('o.startDate > :invoiceStartDate')
                ->setParameter('invoiceStartDate', $invoiceStartDate);
        }

        // filter by invoice end date
        if (!is_null($invoiceEndDate)) {
            $query->andWhere('o.startDate < :invoiceEndDare')
                ->setParameter('invoiceEndDare', $invoiceEndDate);
        }

        // filter by sales company
        if (!is_null($salesCompanyId)) {
            $query->andWhere('b.companyId = :salesCompanyId')
                ->setParameter('salesCompanyId', $salesCompanyId);
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
            ->andWhere('(o.payChannel != :channel OR o.payChannel IS NULL)')
            ->andWhere('(o.type = :own)')
            ->andWhere('o.creationDate <= :start')
            ->setParameter('own', ProductOrder::OWN_TYPE)
            ->setParameter('start', $start)
            ->setParameter('channel', ProductOrder::CHANNEL_OFFLINE)
            ->getQuery();

        $query->execute();
    }

    /**
     * Set preorder orders status to cancelled.
     */
    public function setPreOrderStatusCancelled()
    {
        $now = new \DateTime();
        $nowString = (string) $now->format('Y-m-d H:i:s');
        $nowString = "'$nowString'";

        $query = $this->createQueryBuilder('o')
            ->update()
            ->set('o.status', self::CANCELLED)
            ->set('o.cancelledDate', $nowString)
            ->set('o.modificationDate', $nowString)
            ->where('o.status = \'unpaid\'')
            ->andWhere('(o.payChannel != :channel OR o.payChannel IS NULL)')
            ->andWhere('(o.type = :preorder)')
            ->andWhere('o.startDate <= :start')
            ->setParameter('preorder', ProductOrder::PREORDER_TYPE)
            ->setParameter('start', $now)
            ->setParameter('channel', ProductOrder::CHANNEL_OFFLINE)
            ->getQuery();

        $query->execute();
    }

    /**
     * get unpaid preorder product orders.
     *
     * @param $myBuildingIds
     * @param $buildingId
     * @param $type
     * @param $startDate
     * @param $endDate
     * @param $keyword
     * @param $keywordSearch
     *
     * @return array
     */
    public function getUnpaidPreOrders(
        $myBuildingIds,
        $buildingId,
        $type,
        $startDate,
        $endDate,
        $keyword,
        $keywordSearch
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('o.product', 'p')
            ->leftJoin('p.room', 'r')
            ->where('o.status = :status')
            ->andWhere('o.type = :preorder')
            ->andWhere('r.buildingId in (:buildings)')
            ->setParameter('status', ProductOrder::STATUS_UNPAID)
            ->setParameter('preorder', ProductOrder::PREORDER_TYPE)
            ->setParameter('buildings', $myBuildingIds);

        if ($buildingId) {
            $query->andWhere('r.buildingId = :building')
                ->setParameter('building', $buildingId);
        }

        if ($type) {
            $query->andWhere('r.type = :type')
                ->setParameter('type', $type);
        }

        if ($startDate) {
            $startDate = new \DateTime($startDate);

            $query->andWhere('o.creationDate >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            $query->andWhere('o.creationDate <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        if ($keyword && $keywordSearch) {
            switch ($keyword) {
                case 'phone':
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'o.customerId = uc.id')
                        ->andWhere('uc.phone LIKE :search');
                    break;
                case 'customer':
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'o.customerId = uc.id')
                        ->andWhere('uc.name LIKE :search');
                    break;
                case 'order':
                    $query->andWhere('o.orderNumber LIKE :search');
                    break;
                default:
                    return array();
            }

            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        $query->orderBy('o.creationDate', 'DESC');

        $result = $query->getQuery()->getResult();

        return $result;
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
        $endDate,
        $seatId = null
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
            ->setParameter('rejected', false);

        if (!is_null($seatId)) {
            $query->andWhere('o.seatId = :seatId')
                ->setParameter('seatId', $seatId);
        }

        return $query->getQuery()->getResult();
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
            ->andWhere('o.endDate >= :startDate')
//            ->andWhere('o.endDate <= :endDate')
            ->orderBy('o.startDate', 'ASC')
            ->setParameter('productId', $id)
            ->setParameter('startDate', $startDate)
//            ->setParameter('endDate', $endDate)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Get Booked Times for Room.
     *
     * @param $id
     * @param $seatId
     *
     * @return array
     */
    public function getBookedDates(
        $id,
        $seatId = null
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
            ->setParameter('rejected', false);

        if (!is_null($seatId)) {
            $query->andWhere('o.seatId = :seatId')
                ->setParameter('seatId', $seatId);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Get rejected office orders.
     *
     * @param $productId
     * @param $startDate
     * @param $endDate
     * @param $userId
     * @param $orderId
     * @param $customerId
     *
     * @return array
     */
    public function getOfficeRejected(
        $productId,
        $startDate,
        $endDate,
        $userId = null,
        $orderId = null,
        $customerId = null
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

        if (!is_null($customerId)) {
            $query = $query->andWhere('o.customerId = :customerId')
                ->setParameter('customerId', $customerId);
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
     * @param array $channel
     * @param array $type
     * @param $city
     * @param $company
     * @param $building
     * @param $room
     * @param $userId
     * @param $rentFilter
     * @param $startDate
     * @param $endDate
     * @param $payDate
     * @param $payStart
     * @param $payEnd
     * @param $keyword
     * @param $keywordSearch
     * @param $createDateRange
     * @param $createStart
     * @param $createEnd
     * @param $status
     * @param $refundStatus
     * @param $refundLow
     * @param $refundHigh
     * @param $refundStart
     * @param $refundEnd
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getOrdersForAdmin(
        $channel,
        $type,
        $city,
        $company,
        $building,
        $room,
        $userId,
        $rentFilter,
        $startDate,
        $endDate,
        $payDate,
        $payStart,
        $payEnd,
        $keyword,
        $keywordSearch,
        $createDateRange,
        $createStart,
        $createEnd,
        $status,
        $refundStatus,
        $refundLow,
        $refundHigh,
        $refundStart,
        $refundEnd,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:Order\ProductOrderRecord', 'por', 'WITH', 'por.orderId = o.id')
            ->leftJoin('p.room', 'r')
            ->leftJoin('SandboxApiBundle:User\UserView', 'u', 'WITH', 'u.id = o.userId')
            ->where('
                    (
                        (o.status != :unpaid) AND (o.paymentDate IS NOT NULL) OR 
                        (o.type = :preOrder) OR 
                        (o.payChannel = :offline)
                    )
               ')
            ->setParameter('preOrder', ProductOrder::PREORDER_TYPE)
            ->setParameter('unpaid', ProductOrder::STATUS_UNPAID)
            ->setParameter('offline', ProductOrder::CHANNEL_OFFLINE);

        // filter by payment channel
        if (!is_null($channel) && !empty($channel)) {
            $query->andWhere('o.payChannel in (:channel)')
                ->setParameter('channel', $channel);
        }

        // filter by status
        if (!is_null($status)) {
            $query->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        // filter by user id
        if (!is_null($userId)) {
            $query->andWhere('o.userId = :userId')
                ->setParameter('userId', $userId);
        }

        // filter by type
        if (!is_null($type) && !empty($type)) {
            $query->andWhere('por.roomType in (:type)')
                ->setParameter('type', $type);
        }

        // filter by city
        if (!is_null($city)) {
            $query->andWhere('por.cityId = :city')
                ->setParameter('city', $city);
        }

        if (!is_null($company)) {
            $query->leftJoin('r.building', 'b')
                ->andWhere('b.company = :company')
                ->setParameter('company', $company);
        }

        // filter by building
        if (!is_null($building)) {
            $query->andWhere('por.buildingId = :building')
                ->setParameter('building', $building);
        }

        if (!is_null($room)) {
            $query->andWhere('p.room = :room')
                ->setParameter('room', $room);
        }

        if (!is_null($rentFilter) && !empty($rentFilter) &&
            !is_null($startDate) && !empty($startDate) &&
            !is_null($endDate) && !empty($endDate)
        ) {
            $startDate = new \DateTime($startDate);
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            switch ($rentFilter) {
                case 'rent_start':
                    $query->andWhere('o.startDate >= :startDate')
                        ->andWhere('o.startDate <= :endDate');
                    break;
                case 'rent_range':
                    $query->andWhere(
                        '(
                            (o.startDate <= :startDate AND o.endDate > :startDate) OR
                            (o.startDate < :endDate AND o.endDate >= :endDate) OR
                            (o.startDate >= :startDate AND o.endDate <= :endDate)
                        )'
                    );
                    break;
                case 'rent_end':
                    $query->andWhere('o.endDate >= :startDate')
                        ->andWhere('o.endDate <= :endDate');
                    break;
                default:
                    $query->andWhere('o.endDate >= :startDate')
                        ->andWhere('o.endDate <= :endDate');
            }
            $query->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        //filter by payDate
        if (!is_null($payDate)) {
            $payDateStart = new \DateTime($payDate);
            $payDateEnd = new \DateTime($payDate);
            $payDateEnd->setTime(23, 59, 59);

            $query->andWhere('o.paymentDate >= :payStart')
                ->andWhere('o.paymentDate <= :payEnd')
                ->setParameter('payStart', $payDateStart)
                ->setParameter('payEnd', $payDateEnd);
        } else {
            //filter by payStart
            if (!is_null($payStart)) {
                $payStart = new \DateTime($payStart);
                $query->andWhere('o.paymentDate >= :payStart')
                    ->setParameter('payStart', $payStart);
            }

            //filter by payEnd
            if (!is_null($payEnd)) {
                $payEnd = new \DateTime($payEnd);
                $payEnd->setTime(23, 59, 59);
                $query->andWhere('o.paymentDate <= :payEnd')
                    ->setParameter('payEnd', $payEnd);
            }
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'number':
                    $query->andWhere('o.orderNumber LIKE :search');
                    break;
                case 'room':
                    $query->andWhere('r.name LIKE :search');
                    break;
                case 'user':
                    $query->andWhere('u.name LIKE :search');
                    break;
                case 'phone':
                    $query->andWhere('u.phone LIKE :search');
                    break;
                case 'email':
                    $query->andWhere('u.email LIKE :search');
                    break;
                default:
                    $query->andWhere('o.orderNumber LIKE :search');
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        if (!is_null($createDateRange)) {
            $now = new \DateTime();
            switch ($createDateRange) {
                case 'last_week':
                    $lastDate = $now->sub(new \DateInterval('P7D'));
                    break;
                case 'last_month':
                    $lastDate = $now->sub(new \DateInterval('P1M'));
                    break;
                default:
                    $lastDate = new \DateTime();
            }
            $query->andWhere('o.creationDate >= :createStart')
                ->setParameter('createStart', $lastDate);
        } else {
            // filter by order start point
            if (!is_null($createStart)) {
                $createStart = new \DateTime($createStart);
                $createStart->setTime(00, 00, 00);
                $query->andWhere('o.creationDate >= :createStart')
                    ->setParameter('createStart', $createStart);
            }

            // filter by order end point
            if (!is_null($createEnd)) {
                $createEnd = new \DateTime($createEnd);
                $createEnd->setTime(23, 59, 59);
                $query->andWhere('o.creationDate <= :createEnd')
                    ->setParameter('createEnd', $createEnd);
            }
        }

        if (!is_null($refundLow)) {
            $query->andWhere('o.actualRefundAmount >= :refundLow')
                ->setParameter('refundLow', $refundLow);
        }

        if (!is_null($refundHigh)) {
            $query->andWhere('o.actualRefundAmount <= :refundHigh')
                ->setParameter('refundHigh', $refundHigh);
        }

        if (!is_null($refundStart)) {
            $refundStart = new \DateTime($refundStart);
            $refundStart->setTime(00, 00, 00);
            $query->andWhere('o.refundProcessed = TRUE')
                ->andWhere('o.refundProcessedDate >= :refundStart')
                ->setParameter('refundStart', $refundStart);
        }

        if (!is_null($refundEnd)) {
            $refundEnd = new \DateTime($refundEnd);
            $refundEnd->setTime(23, 59, 59);
            $query->andWhere('o.refundProcessed = TRUE')
                ->andWhere('o.refundProcessedDate <= :refundEnd')
                ->setParameter('refundEnd', $refundEnd);
        }

        // refund status filter
        if (ProductOrder::REFUNDED_STATUS == $refundStatus) {
            $query->andWhere('o.refunded = TRUE')
                ->orderBy('o.modificationDate', 'DESC');
        } elseif (ProductOrder::NEED_TO_REFUND == $refundStatus) {
            $query->andWhere('o.refunded = FALSE')
                ->andWhere('o.needToRefund = TRUE')
                ->orderBy('o.modificationDate', 'ASC');
        } elseif (ProductOrder::ALL_REFUND == $refundStatus) {
            $query->andWhere('(o.refunded = TRUE OR o.needToRefund = TRUE)')
                ->orderBy('o.modificationDate', 'DESC');
        } else {
            $query->orderBy('o.creationDate', 'DESC');
        }

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param array $channel
     * @param array $type
     * @param $city
     * @param $building
     * @param $room
     * @param $userId
     * @param $rentFilter
     * @param $startDate
     * @param $endDate
     * @param $payDate
     * @param $payStart
     * @param $payEnd
     * @param $keyword
     * @param $keywordSearch
     * @param $createDateRange
     * @param $createStart
     * @param $createEnd
     * @param $status
     * @param $refundStatus
     * @param $refundLow
     * @param $refundHigh
     * @param $refundStart
     * @param $refundEnd
     *
     * @return mixed
     */
    public function countOrdersForAdmin(
        $channel,
        $type,
        $city,
        $company,
        $building,
        $room,
        $userId,
        $rentFilter,
        $startDate,
        $endDate,
        $payDate,
        $payStart,
        $payEnd,
        $keyword,
        $keywordSearch,
        $createDateRange,
        $createStart,
        $createEnd,
        $status,
        $refundStatus,
        $refundLow,
        $refundHigh,
        $refundStart,
        $refundEnd
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:Order\ProductOrderRecord', 'por', 'WITH', 'por.orderId = o.id')
            ->leftJoin('p.room', 'r')
            ->leftJoin('SandboxApiBundle:User\UserView', 'u', 'WITH', 'u.id = o.userId')
            ->where('
                    (
                        (o.status != :unpaid) AND (o.paymentDate IS NOT NULL) OR
                        (o.type = :preOrder) OR 
                        (o.payChannel = :offline)
                    )
               ')
            ->setParameter('preOrder', ProductOrder::PREORDER_TYPE)
            ->setParameter('unpaid', ProductOrder::STATUS_UNPAID)
            ->setParameter('offline', ProductOrder::CHANNEL_OFFLINE);

        // filter by payment channel
        if (!is_null($channel) && !empty($channel)) {
            $query->andWhere('o.payChannel in (:channel)')
                ->setParameter('channel', $channel);
        }

        // filter by status
        if (!is_null($status)) {
            $query->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        // filter by user id
        if (!is_null($userId)) {
            $query->andWhere('o.userId = :userId')
                ->setParameter('userId', $userId);
        }

        // filter by type
        if (!is_null($type) && !empty($type)) {
            $query->andWhere('por.roomType in (:type)')
                ->setParameter('type', $type);
        }

        // filter by city
        if (!is_null($city)) {
            $query->andWhere('por.cityId = :city')
                ->setParameter('city', $city);
        }

        if (!is_null($company)) {
            $query->leftJoin('r.building', 'b')
                ->andWhere('b.company = :company')
                ->setParameter('company', $company);
        }

        // filter by building
        if (!is_null($building)) {
            $query->andWhere('por.buildingId = :building')
                ->setParameter('building', $building);
        }

        if (!is_null($room)) {
            $query->andWhere('p.room = :room')
                ->setParameter('room', $room);
        }

        if (!is_null($rentFilter) && !empty($rentFilter) &&
            !is_null($startDate) && !empty($startDate) &&
            !is_null($endDate) && !empty($endDate)
        ) {
            $startDate = new \DateTime($startDate);
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            switch ($rentFilter) {
                case 'rent_start':
                    $query->andWhere('o.startDate >= :startDate')
                        ->andWhere('o.startDate <= :endDate');
                    break;
                case 'rent_range':
                    $query->andWhere(
                        '(
                            (o.startDate <= :startDate AND o.endDate > :startDate) OR
                            (o.startDate < :endDate AND o.endDate >= :endDate) OR
                            (o.startDate >= :startDate AND o.endDate <= :endDate)
                        )'
                    );
                    break;
                case 'rent_end':
                    $query->andWhere('o.endDate >= :startDate')
                        ->andWhere('o.endDate <= :endDate');
                    break;
                default:
                    $query->andWhere('o.endDate >= :startDate')
                        ->andWhere('o.endDate <= :endDate');
            }
            $query->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        //filter by payDate
        if (!is_null($payDate)) {
            $payDateStart = new \DateTime($payDate);
            $payDateEnd = new \DateTime($payDate);
            $payDateEnd->setTime(23, 59, 59);

            $query->andWhere('o.paymentDate >= :payStart')
                ->andWhere('o.paymentDate <= :payEnd')
                ->setParameter('payStart', $payDateStart)
                ->setParameter('payEnd', $payDateEnd);
        } else {
            //filter by payStart
            if (!is_null($payStart)) {
                $payStart = new \DateTime($payStart);
                $query->andWhere('o.paymentDate >= :payStart')
                    ->setParameter('payStart', $payStart);
            }

            //filter by payEnd
            if (!is_null($payEnd)) {
                $payEnd = new \DateTime($payEnd);
                $payEnd->setTime(23, 59, 59);
                $query->andWhere('o.paymentDate <= :payEnd')
                    ->setParameter('payEnd', $payEnd);
            }
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'number':
                    $query->andWhere('o.orderNumber LIKE :search');
                    break;
                case 'room':
                    $query->andWhere('r.name LIKE :search');
                    break;
                case 'user':
                    $query->andWhere('u.name LIKE :search');
                    break;
                case 'phone':
                    $query->andWhere('u.phone LIKE :search');
                    break;
                case 'email':
                    $query->andWhere('u.email LIKE :search');
                    break;
                default:
                    $query->andWhere('o.orderNumber LIKE :search');
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        if (!is_null($createDateRange)) {
            $now = new \DateTime();
            switch ($createDateRange) {
                case 'last_week':
                    $lastDate = $now->sub(new \DateInterval('P7D'));
                    break;
                case 'last_month':
                    $lastDate = $now->sub(new \DateInterval('P1M'));
                    break;
                default:
                    $lastDate = new \DateTime();
            }
            $query->andWhere('o.creationDate >= :createStart')
                ->setParameter('createStart', $lastDate);
        } else {
            // filter by order start point
            if (!is_null($createStart)) {
                $createStart = new \DateTime($createStart);
                $createStart->setTime(00, 00, 00);
                $query->andWhere('o.creationDate >= :createStart')
                    ->setParameter('createStart', $createStart);
            }

            // filter by order end point
            if (!is_null($createEnd)) {
                $createEnd = new \DateTime($createEnd);
                $createEnd->setTime(23, 59, 59);
                $query->andWhere('o.creationDate <= :createEnd')
                    ->setParameter('createEnd', $createEnd);
            }
        }

        if (!is_null($refundLow)) {
            $query->andWhere('o.actualRefundAmount >= :refundLow')
                ->setParameter('refundLow', $refundLow);
        }

        if (!is_null($refundHigh)) {
            $query->andWhere('o.actualRefundAmount <= :refundHigh')
                ->setParameter('refundHigh', $refundHigh);
        }

        if (!is_null($refundStart)) {
            $refundStart = new \DateTime($refundStart);
            $refundStart->setTime(00, 00, 00);
            $query->andWhere('o.refundProcessed = TRUE')
                ->andWhere('o.refundProcessedDate >= :refundStart')
                ->setParameter('refundStart', $refundStart);
        }

        if (!is_null($refundEnd)) {
            $refundEnd = new \DateTime($refundEnd);
            $refundEnd->setTime(23, 59, 59);
            $query->andWhere('o.refundProcessed = TRUE')
                ->andWhere('o.refundProcessedDate <= :refundEnd')
                ->setParameter('refundEnd', $refundEnd);
        }

        // refund status filter
        if (ProductOrder::REFUNDED_STATUS == $refundStatus) {
            $query->andWhere('o.refunded = TRUE');
        } elseif (ProductOrder::NEED_TO_REFUND == $refundStatus) {
            $query->andWhere('o.refunded = FALSE')
                ->andWhere('o.needToRefund = TRUE');
        } elseif (ProductOrder::ALL_REFUND == $refundStatus) {
            $query->andWhere('(o.refunded = TRUE OR o.needToRefund = TRUE)');
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return $result;
    }

    /**
     * @param array $channel
     * @param array $type
     * @param $city
     * @param $company
     * @param $building
     * @param $userId
     * @param $rentFilter
     * @param $startDate
     * @param $endDate
     * @param $payDate
     * @param $payStart
     * @param $payEnd
     * @param $keyword
     * @param $keywordSearch
     * @param $createDateRange
     * @param $createStart
     * @param $createEnd
     * @param $status
     *
     * @return array
     */
    public function getOrdersToExport(
        $channel,
        $type,
        $city,
        $company,
        $building,
        $userId,
        $rentFilter,
        $startDate,
        $endDate,
        $payDate,
        $payStart,
        $payEnd,
        $keyword,
        $keywordSearch,
        $createDateRange,
        $createStart,
        $createEnd,
        $status
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:Order\ProductOrderRecord', 'por', 'WITH', 'por.orderId = o.id');

        // only export order that is paid
        $query->where('o.paymentDate IS NOT NULL');

        // filter by user id
        if (!is_null($userId)) {
            $query->andWhere('o.userId = :userId')
                ->setParameter('userId', $userId);
        } else {
            $query->andWhere('o.status != :unpaid')
                ->setParameter('unpaid', ProductOrder::STATUS_UNPAID);
        }

        // filter by payment channel
        if (!is_null($channel) && !empty($channel)) {
            $query->andWhere('o.payChannel in (:channel)')
                ->setParameter('channel', $channel);
        }

        // filter by status
        if (!is_null($status)) {
            $query->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        // filter by type
        if (!is_null($type) && !empty($type)) {
            $query->andWhere('por.roomType in (:type)')
                ->setParameter('type', $type);
        }

        // filter by city
        if (!is_null($city)) {
            $query->andWhere('por.cityId = :city')
                ->setParameter('city', $city);
        }

        if (!is_null($company)) {
            $query->leftJoin('p.room', 'r')
                ->leftJoin('r.building', 'b')
                ->andWhere('b.company = :company')
                ->setParameter('company', $company);
        }

        // filter by building
        if (!is_null($building)) {
            $query->andWhere('por.buildingId = :building')
                ->setParameter('building', $building);
        }

        if (!is_null($rentFilter) && !empty($rentFilter) &&
            !is_null($startDate) && !empty($startDate) &&
            !is_null($endDate) && !empty($endDate)
        ) {
            $startDate = new \DateTime($startDate);
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            switch ($rentFilter) {
                case 'rent_start':
                    $query->andWhere('o.startDate >= :startDate')
                        ->andWhere('o.startDate <= :endDate');
                    break;
                case 'rent_range':
                    $query->andWhere(
                        '(
                            (o.startDate <= :startDate AND o.endDate > :startDate) OR
                            (o.startDate < :endDate AND o.endDate >= :endDate) OR
                            (o.startDate >= :startDate AND o.endDate <= :endDate)
                        )'
                    );
                    break;
                case 'rent_end':
                    $query->andWhere('o.endDate >= :startDate')
                        ->andWhere('o.endDate <= :endDate');
                    break;
                default:
                    $query->andWhere('o.endDate >= :startDate')
                        ->andWhere('o.endDate <= :endDate');
            }
            $query->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        //filter by payDate
        if (!is_null($payDate)) {
            $payDateStart = new \DateTime($payDate);
            $payDateEnd = new \DateTime($payDate);
            $payDateEnd->setTime(23, 59, 59);

            $query->andWhere('o.paymentDate >= :payStart')
                ->andWhere('o.paymentDate <= :payEnd')
                ->setParameter('payStart', $payDateStart)
                ->setParameter('payEnd', $payDateEnd);
        } else {
            //filter by payStart
            if (!is_null($payStart)) {
                $payStart = new \DateTime($payStart);
                $query->andWhere('o.paymentDate >= :payStart')
                    ->setParameter('payStart', $payStart);
            }

            //filter by payEnd
            if (!is_null($payEnd)) {
                $payEnd = new \DateTime($payEnd);
                $payEnd->setTime(23, 59, 59);
                $query->andWhere('o.paymentDate <= :payEnd')
                    ->setParameter('payEnd', $payEnd);
            }
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'number':
                    $query->andWhere('o.orderNumber LIKE :search')
                        ->setParameter('search', '%'.$keywordSearch.'%');
                    break;
                case 'room':
                    $query->andWhere('r.name LIKE :search')
                        ->setParameter('search', '%'.$keywordSearch.'%');
                    break;
            }
        }

        if (!is_null($createDateRange)) {
            $now = new \DateTime();
            switch ($createDateRange) {
                case 'last_week':
                    $lastDate = $now->sub(new \DateInterval('P7D'));
                    break;
                case 'last_month':
                    $lastDate = $now->sub(new \DateInterval('P1M'));
                    break;
                default:
                    $lastDate = new \DateTime();
            }
            $query->andWhere('o.creationDate >= :createStart')
                ->setParameter('createStart', $lastDate);
        } else {
            // filter by order start point
            if (!is_null($createStart)) {
                $createStart = new \DateTime($createStart);
                $createStart->setTime(00, 00, 00);
                $query->andWhere('o.creationDate >= :createStart')
                    ->setParameter('createStart', $createStart);
            }

            // filter by order end point
            if (!is_null($createEnd)) {
                $createEnd = new \DateTime($createEnd);
                $createEnd->setTime(23, 59, 59);
                $query->andWhere('o.creationDate <= :createEnd')
                    ->setParameter('createEnd', $createEnd);
            }
        }

        //order by
        $query->orderBy('o.creationDate', 'DESC');

        $result = $query->getQuery()->getResult();

        return $result;
    }

    //-------------------- sales repository --------------------//

    /**
     * Get list of orders for admin.
     *
     * @param $allOrder
     * @param array $channel
     * @param array $type
     * @param $city
     * @param $building
     * @param $userId
     * @param $rentFilter
     * @param $startDate
     * @param $endDate
     * @param $payDate
     * @param $payStart
     * @param $payEnd
     * @param $keyword
     * @param $keywordSearch
     * @param $myBuildingIds
     * @param $createDateRange
     * @param $createStart
     * @param $createEnd
     * @param $status
     * @param $orderType
     * @param $productId
     * @param $room
     * @param $limit
     * @param $offset
     * @param  $sortColumn,
     * @param $direction
     *
     * @return array
     */
    public function getSalesOrdersForAdmin(
        $allOrder,
        $channel,
        $type,
        $city,
        $building,
        $userId,
        $rentFilter,
        $startDate,
        $endDate,
        $payDate,
        $payStart,
        $payEnd,
        $keyword,
        $keywordSearch,
        $myBuildingIds,
        $createDateRange,
        $createStart,
        $createEnd,
        $status,
        $orderType,
        $productId,
        $room,
        $limit = null,
        $offset = null,
        $sortColumn = null,
        $direction = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('o.product', 'p')
            ->leftJoin('p.room', 'r')
            ->leftJoin('SandboxApiBundle:Order\ProductOrderRecord', 'por', 'WITH', 'por.orderId = o.id')
            ->where('por.buildingId IN (:buildingIds)')
            ->setParameter('buildingIds', $myBuildingIds);

        if (!$allOrder) {
            $query->andWhere('
                    (
                        (o.status != :unpaid) AND 
                        (o.paymentDate IS NOT NULL) OR 
                        (o.type = :preOrder) OR 
                        (o.type = :officialPreOrder)
                    )
                ')
                ->setParameter('unpaid', ProductOrder::STATUS_UNPAID)
                ->setParameter('preOrder', ProductOrder::PREORDER_TYPE)
                ->setParameter('officialPreOrder', ProductOrder::OFFICIAL_PREORDER_TYPE);
        }

        // filter by payment channelP
        if (!is_null($channel) && !empty($channel)) {
            if (in_array('sandbox', $channel)) {
                $channel[] = ProductOrder::CHANNEL_ACCOUNT;
                $channel[] = ProductOrder::CHANNEL_ALIPAY;
                $channel[] = ProductOrder::CHANNEL_UNIONPAY;
                $channel[] = ProductOrder::CHANNEL_WECHAT;
                $channel[] = ProductOrder::CHANNEL_WECHAT_PUB;
            }
            $query->leftJoin('SandboxApiBundle:Finance\FinanceReceivables', 'fr', 'WITH', 'o.orderNumber = fr.orderNumber')
                ->andWhere('o.payChannel in (:channel) or fr.payChannel in (:channel)')
                ->setParameter('channel', $channel);
        }

        // filter by status
        if (!is_null($status) && !empty($status)) {
            $query->andWhere('o.status in (:status)')
                ->setParameter('status', $status);
        }

        // filter by user id
        if (!is_null($userId)) {
            $query->andWhere('o.userId = :userId')
                ->setParameter('userId', $userId);
        }

        // filter by type
        if (!is_null($type) && !empty($type)) {
            $query->andWhere('por.roomType in (:type)')
                ->setParameter('type', $type);
        }

        // filter by city
        if (!is_null($city)) {
            $query->andWhere('por.cityId = :city')
                ->setParameter('city', $city);
        }

        // filter by building
        if (!is_null($building) && !empty($building)) {
            $query->andWhere('por.buildingId in (:building)')
                ->setParameter('building', $building);
        }

        if (!is_null($productId)) {
            $query->andWhere('o.product = :product')
                ->setParameter('product', $productId);
        }

        if (!is_null($room)) {
            $query->andWhere('p.room = :room')
                ->setParameter('room', $room);
        }

        // filter by order type
        if (!is_null($orderType) && !empty($orderType)) {
            $query->andWhere('o.type in (:orderType)')
                ->setParameter('orderType', $orderType);
        }

        if (!is_null($rentFilter) && !empty($rentFilter) &&
            !is_null($startDate) && !empty($startDate) &&
            !is_null($endDate) && !empty($endDate)
        ) {
            $startDate = new \DateTime($startDate);
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            switch ($rentFilter) {
                case 'rent_start':
                    $query->andWhere('o.startDate >= :startDate')
                        ->andWhere('o.startDate <= :endDate');
                    break;
                case 'rent_range':
                    $query->andWhere(
                        '(
                            (o.startDate <= :startDate AND o.endDate > :startDate) OR
                            (o.startDate < :endDate AND o.endDate >= :endDate) OR
                            (o.startDate >= :startDate AND o.endDate <= :endDate)
                        )'
                    );
                    break;
                case 'rent_end':
                    $query->andWhere('o.endDate >= :startDate')
                        ->andWhere('o.endDate <= :endDate');
                    break;
                default:
                    $query->andWhere('o.endDate >= :startDate')
                        ->andWhere('o.endDate <= :endDate');
            }
            $query->setParameter('startDate', $startDate)
                    ->setParameter('endDate', $endDate);
        }

        //filter by payDate
        if (!is_null($payDate) && !empty($payDate)) {
            $payDateStart = new \DateTime($payDate);
            $payDateEnd = new \DateTime($payDate);
            $payDateEnd->setTime(23, 59, 59);

            $query->andWhere('o.paymentDate >= :payStart')
                ->andWhere('o.paymentDate <= :payEnd')
                ->setParameter('payStart', $payDateStart)
                ->setParameter('payEnd', $payDateEnd);
        } else {
            //filter by payStart
            if (!is_null($payStart)) {
                $payStart = new \DateTime($payStart);
                $query->andWhere('o.paymentDate >= :payStart')
                    ->setParameter('payStart', $payStart);
            }

            //filter by payEnd
            if (!is_null($payEnd)) {
                $payEnd = new \DateTime($payEnd);
                $payEnd->setTime(23, 59, 59);
                $query->andWhere('o.paymentDate <= :payEnd')
                    ->setParameter('payEnd', $payEnd);
            }
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'all':
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'uc.id = o.customerId')
                        ->andWhere('
                                (o.orderNumber LIKE :search OR
                                r.name LIKE :search OR
                                uc.name LIKE :search OR
                                uc.phone LIKE :search)
                            ');
                    break;
                case 'number':
                    $query->andWhere('o.orderNumber LIKE :search');
                    break;
                case 'phone':
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'uc.id = o.customerId')
                        ->andWhere('uc.phone LIKE :search');
                    break;
                case 'name':
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'uc.id = o.customerId')
                        ->andWhere('uc.name LIKE :search');
                    break;
                default:
                    $query->andWhere('o.orderNumber LIKE :search');
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        if (!is_null($createDateRange)) {
            $now = new \DateTime();
            switch ($createDateRange) {
                case 'last_week':
                    $lastDate = $now->sub(new \DateInterval('P7D'));
                    break;
                case 'last_month':
                    $lastDate = $now->sub(new \DateInterval('P1M'));
                    break;
                default:
                    $lastDate = new \DateTime();
            }
            $query->andWhere('o.creationDate >= :createStart')
                ->setParameter('createStart', $lastDate);
        } else {
            // filter by order start point
            if (!is_null($createStart)) {
                $createStart = new \DateTime($createStart);
                $createStart->setTime(00, 00, 00);
                $query->andWhere('o.creationDate >= :createStart')
                    ->setParameter('createStart', $createStart);
            }

            // filter by order end point
            if (!is_null($createEnd)) {
                $createEnd = new \DateTime($createEnd);
                $createEnd->setTime(23, 59, 59);
                $query->andWhere('o.creationDate <= :createEnd')
                    ->setParameter('createEnd', $createEnd);
            }
        }

        if (!is_null($sortColumn) && !is_null($direction)) {
            $sortArray = [
                'base_price' => 'o.basePrice',
                'start_date' => 'o.startDate',
                'end_date' => 'o.endDate',
                'price' => 'o.price',
                'discount_price' => 'o.discountPrice',
                'creation_date' => 'o.creation_date',
            ];
            $direction = strtoupper($direction);
            $query->orderBy($sortArray[$sortColumn], $direction);
        } else {
            $query->orderBy('o.creationDate', 'DESC');
        }

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $channel
     * @param $type
     * @param $city
     * @param $building
     * @param $userId
     * @param $rentFilter
     * @param $startDate
     * @param $endDate
     * @param $payDate
     * @param $payStart
     * @param $payEnd
     * @param $keyword
     * @param $keywordSearch
     * @param $myBuildingIds
     * @param $createDateRange
     * @param $createStart
     * @param $createEnd
     * @param $status
     * @param $orderType
     * @param $productId
     * @param $room
     *
     * @return array
     */
    public function countSalesOrdersForAdmin(
        $allOrder,
        $channel,
        $type,
        $city,
        $building,
        $userId,
        $rentFilter,
        $startDate,
        $endDate,
        $payDate,
        $payStart,
        $payEnd,
        $keyword,
        $keywordSearch,
        $myBuildingIds,
        $createDateRange,
        $createStart,
        $createEnd,
        $status,
        $orderType,
        $productId,
        $room
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->leftJoin('o.product', 'p')
            ->leftJoin('p.room', 'r')
            ->leftJoin('SandboxApiBundle:Order\ProductOrderRecord', 'por', 'WITH', 'por.orderId = o.id')
            ->where('por.buildingId IN (:buildingIds)')
            ->setParameter('buildingIds', $myBuildingIds);

        if (!$allOrder) {
            $query->andWhere('
                    (
                        (o.status != :unpaid) AND 
                        (o.paymentDate IS NOT NULL) OR 
                        (o.type = :preOrder) OR 
                        (o.type = :officialPreOrder)
                    )
                ')
                ->setParameter('unpaid', ProductOrder::STATUS_UNPAID)
                ->setParameter('preOrder', ProductOrder::PREORDER_TYPE)
                ->setParameter('officialPreOrder', ProductOrder::OFFICIAL_PREORDER_TYPE);
        }

        // filter by payment channel
        if (!is_null($channel) && !empty($channel)) {
            if (in_array('sandbox', $channel)) {
                $channel[] = ProductOrder::CHANNEL_ACCOUNT;
                $channel[] = ProductOrder::CHANNEL_ALIPAY;
                $channel[] = ProductOrder::CHANNEL_UNIONPAY;
                $channel[] = ProductOrder::CHANNEL_WECHAT;
                $channel[] = ProductOrder::CHANNEL_WECHAT_PUB;
            }
            $query->leftJoin('SandboxApiBundle:Finance\FinanceReceivables', 'fr', 'WITH', 'o.orderNumber = fr.orderNumber')
                ->andWhere('o.payChannel in (:channel) or fr.payChannel in (:channel)')
                ->setParameter('channel', $channel);
        }

        // filter by status
        if (!is_null($status) && !empty($status)) {
            $query->andWhere('o.status in (:status)')
                ->setParameter('status', $status);
        }

        // filter by user id
        if (!is_null($userId)) {
            $query->andWhere('o.userId = :userId')
                ->setParameter('userId', $userId);
        }

        // filter by type
        if (!is_null($type) && !empty($type)) {
            $query->andWhere('por.roomType in (:type)')
                ->setParameter('type', $type);
        }

        // filter by city
        if (!is_null($city)) {
            $query->andWhere('por.cityId = :city')
                ->setParameter('city', $city);
        }

        // filter by building
        if (!is_null($building) && !empty($building)) {
            $query->andWhere('por.buildingId in (:building)')
                ->setParameter('building', $building);
        }

        if (!is_null($productId)) {
            $query->andWhere('o.product = :product')
                ->setParameter('product', $productId);
        }

        if (!is_null($room)) {
            $query->andWhere('p.room = :room')
                ->setParameter('room', $room);
        }

        // filter by order type
        if (!is_null($orderType) && !empty($orderType)) {
            $query->andWhere('o.type in (:orderType)')
                ->setParameter('orderType', $orderType);
        }

        if (!is_null($rentFilter) && !empty($rentFilter) &&
            !is_null($startDate) && !empty($startDate) &&
            !is_null($endDate) && !empty($endDate)
        ) {
            $startDate = new \DateTime($startDate);
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            switch ($rentFilter) {
                case 'rent_start':
                    $query->andWhere('o.startDate >= :startDate')
                        ->andWhere('o.startDate <= :endDate');
                    break;
                case 'rent_range':
                    $query->andWhere(
                        '(
                            (o.startDate <= :startDate AND o.endDate > :startDate) OR
                            (o.startDate < :endDate AND o.endDate >= :endDate) OR
                            (o.startDate >= :startDate AND o.endDate <= :endDate)
                        )'
                    );
                    break;
                case 'rent_end':
                    $query->andWhere('o.endDate >= :startDate')
                        ->andWhere('o.endDate <= :endDate');
                    break;
                default:
                    $query->andWhere('o.endDate >= :startDate')
                        ->andWhere('o.endDate <= :endDate');
            }
            $query->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        //filter by payDate
        if (!is_null($payDate) && !empty($payDate)) {
            $payDateStart = new \DateTime($payDate);
            $payDateEnd = new \DateTime($payDate);
            $payDateEnd->setTime(23, 59, 59);

            $query->andWhere('o.paymentDate >= :payStart')
                ->andWhere('o.paymentDate <= :payEnd')
                ->setParameter('payStart', $payDateStart)
                ->setParameter('payEnd', $payDateEnd);
        } else {
            //filter by payStart
            if (!is_null($payStart)) {
                $payStart = new \DateTime($payStart);
                $query->andWhere('o.paymentDate >= :payStart')
                    ->setParameter('payStart', $payStart);
            }

            //filter by payEnd
            if (!is_null($payEnd)) {
                $payEnd = new \DateTime($payEnd);
                $payEnd->setTime(23, 59, 59);
                $query->andWhere('o.paymentDate <= :payEnd')
                    ->setParameter('payEnd', $payEnd);
            }
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'all':
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'uc.id = o.customerId')
                        ->andWhere(
                        'o.orderNumber LIKE :search OR
                                r.name LIKE :search OR
                                uc.name LIKE :search OR
                                uc.phone LIKE :search
                            ');
                    break;
                case 'number':
                    $query->andWhere('o.orderNumber LIKE :search');
                    break;
                case 'phone':
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'uc.id = o.customerId')
                        ->andWhere('uc.phone LIKE :search');
                    break;
                case 'name':
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'uc.id = o.customerId')
                        ->andWhere('uc.name LIKE :search');
                    break;
                default:
                    $query->andWhere('o.orderNumber LIKE :search');
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        if (!is_null($createDateRange)) {
            $now = new \DateTime();
            switch ($createDateRange) {
                case 'last_week':
                    $lastDate = $now->sub(new \DateInterval('P7D'));
                    break;
                case 'last_month':
                    $lastDate = $now->sub(new \DateInterval('P1M'));
                    break;
                default:
                    $lastDate = new \DateTime();
            }
            $query->andWhere('o.creationDate >= :createStart')
                ->setParameter('createStart', $lastDate);
        } else {
            // filter by order start point
            if (!is_null($createStart)) {
                $createStart = new \DateTime($createStart);
                $createStart->setTime(00, 00, 00);
                $query->andWhere('o.creationDate >= :createStart')
                    ->setParameter('createStart', $createStart);
            }

            // filter by order end point
            if (!is_null($createEnd)) {
                $createEnd = new \DateTime($createEnd);
                $createEnd->setTime(23, 59, 59);
                $query->andWhere('o.creationDate <= :createEnd')
                    ->setParameter('createEnd', $createEnd);
            }
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return $result;
    }

    /**
     * @param $building
     * @param $payStart
     * @param $payEnd
     * @param $myBuildingIds
     *
     * @return array
     */
    public function getSalesOrderNumbersForInvoice(
        $building,
        $payStart,
        $payEnd,
        $myBuildingIds
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('o.orderNumber')
            ->leftJoin('SandboxApiBundle:Order\ProductOrderRecord', 'por', 'WITH', 'por.orderId = o.id')
            ->where('
                    (
                        (o.status != :unpaid) AND 
                        (o.paymentDate IS NOT NULL) OR 
                        (o.type = :preOrder)
                    )
                ')
            ->setParameter('unpaid', ProductOrder::STATUS_UNPAID)
            ->setParameter('preOrder', ProductOrder::PREORDER_TYPE);

        // filter by building
        if (!is_null($building)) {
            $query->andWhere('por.buildingId = :building')
                ->setParameter('building', $building);
        } else {
            $query->andWhere('por.buildingId IN (:buildingIds)')
                ->setParameter('buildingIds', $myBuildingIds);
        }

        //filter by payStart
        if (!is_null($payStart)) {
            $payStart = new \DateTime($payStart);
            $query->andWhere('o.paymentDate >= :payStart')
                    ->setParameter('payStart', $payStart);
        }

        //filter by payEnd
        if (!is_null($payEnd)) {
            $payEnd = new \DateTime($payEnd);
            $payEnd->setTime(23, 59, 59);
            $query->andWhere('o.paymentDate <= :payEnd')
                    ->setParameter('payEnd', $payEnd);
        }

        $query->orderBy('o.creationDate', 'DESC');

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param array $channel
     * @param array $type
     * @param $city
     * @param $building
     * @param $userId
     * @param $rentFilter
     * @param $startDate
     * @param $endDate
     * @param $payDate
     * @param $payStart
     * @param $payEnd
     * @param $keyword
     * @param $keywordSearch
     * @param $myBuildingIds
     * @param $createDateRange
     * @param $createStart
     * @param $createEnd
     * @param $status
     *
     * @return array
     */
    public function getSalesOrdersToExport(
        $channel,
        $type,
        $city,
        $building,
        $userId,
        $rentFilter,
        $startDate,
        $endDate,
        $payDate,
        $payStart,
        $payEnd,
        $keyword,
        $keywordSearch,
        $myBuildingIds,
        $createDateRange,
        $createStart,
        $createEnd,
        $status
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('o.product', 'p')
            ->leftJoin('p.room', 'r')
            ->leftJoin('SandboxApiBundle:Order\ProductOrderRecord', 'por', 'WITH', 'por.orderId = o.id')
            ->where('o.paymentDate IS NOT NULL');

        // filter by payment channel
        if (!is_null($channel) && !empty($channel)) {
            $query->andWhere('o.payChannel in (:channel)')
                ->setParameter('channel', $channel);
        }

        // filter by status
        if (!is_null($status)) {
            $query->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        // filter by user id
        if (!is_null($userId)) {
            $query->andWhere('o.userId = :userId')
                ->setParameter('userId', $userId);
        }

        // filter by type
        if (!is_null($type) && !empty($type)) {
            $query->andWhere('por.roomType in (:type)')
                ->setParameter('type', $type);
        }

        // filter by city
        if (!is_null($city)) {
            $query->andWhere('por.cityId = :city')
                ->setParameter('city', $city);
        }

        // filter by building
        if (!is_null($building)) {
            $query->andWhere('por.buildingId = :building')
                ->setParameter('building', $building);
        } else {
            $query->andWhere('por.buildingId IN (:buildingIds)')
                ->setParameter('buildingIds', $myBuildingIds);
        }

        if (!is_null($rentFilter) && !empty($rentFilter) &&
            !is_null($startDate) && !empty($startDate) &&
            !is_null($endDate) && !empty($endDate)
        ) {
            $startDate = new \DateTime($startDate);
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            switch ($rentFilter) {
                case 'rent_start':
                    $query->andWhere('o.startDate >= :startDate')
                        ->andWhere('o.startDate <= :endDate');
                    break;
                case 'rent_range':
                    $query->andWhere(
                        '(
                            (o.startDate <= :startDate AND o.endDate > :startDate) OR
                            (o.startDate < :endDate AND o.endDate >= :endDate) OR
                            (o.startDate >= :startDate AND o.endDate <= :endDate)
                        )'
                    );
                    break;
                case 'rent_end':
                    $query->andWhere('o.endDate >= :startDate')
                        ->andWhere('o.endDate <= :endDate');
                    break;
                default:
                    $query->andWhere('o.endDate >= :startDate')
                        ->andWhere('o.endDate <= :endDate');
            }
            $query->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        //filter by payDate
        if (!is_null($payDate)) {
            $payDateStart = new \DateTime($payDate);
            $payDateEnd = new \DateTime($payDate);
            $payDateEnd->setTime(23, 59, 59);

            $query->andWhere('o.paymentDate >= :payStart')
                ->andWhere('o.paymentDate <= :payEnd')
                ->setParameter('payStart', $payDateStart)
                ->setParameter('payEnd', $payDateEnd);
        } else {
            //filter by payStart
            if (!is_null($payStart)) {
                $payStart = new \DateTime($payStart);
                $query->andWhere('o.paymentDate >= :payStart')
                    ->setParameter('payStart', $payStart);
            }

            //filter by payEnd
            if (!is_null($payEnd)) {
                $payEnd = new \DateTime($payEnd);
                $payEnd->setTime(23, 59, 59);
                $query->andWhere('o.paymentDate <= :payEnd')
                    ->setParameter('payEnd', $payEnd);
            }
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'number':
                    $query->andWhere('o.orderNumber LIKE :search')
                        ->setParameter('search', '%'.$keywordSearch.'%');
                    break;
                case 'room':
                    $query->andWhere('r.name LIKE :search')
                        ->setParameter('search', '%'.$keywordSearch.'%');
                    break;
            }
        }

        if (!is_null($createDateRange)) {
            $now = new \DateTime();
            switch ($createDateRange) {
                case 'last_week':
                    $lastDate = $now->sub(new \DateInterval('P7D'));
                    break;
                case 'last_month':
                    $lastDate = $now->sub(new \DateInterval('P1M'));
                    break;
                default:
                    $lastDate = new \DateTime();
            }
            $query->andWhere('o.creationDate >= :createStart')
                ->setParameter('createStart', $lastDate);
        } else {
            // filter by order start point
            if (!is_null($createStart)) {
                $createStart = new \DateTime($createStart);
                $createStart->setTime(00, 00, 00);
                $query->andWhere('o.creationDate >= :createStart')
                    ->setParameter('createStart', $createStart);
            }

            // filter by order end point
            if (!is_null($createEnd)) {
                $createEnd = new \DateTime($createEnd);
                $createEnd->setTime(23, 59, 59);
                $query->andWhere('o.creationDate <= :createEnd')
                    ->setParameter('createEnd', $createEnd);
            }
        }

        $query->orderBy('o.creationDate', 'DESC');

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

    /**
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     * @param null $buildingId
     * @param null $companyId
     *
     * @return mixed
     */
    public function countPaidOrders(
        $startDate,
        $endDate,
        $payChannel = null,
        $buildingId = null,
        $companyId = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'r.buildingId = b.id')
            ->select('count(o.id) as number , SUM(o.discountPrice) as price')
            ->where('(o.status = :paid or o.status = :cancelled)')
            ->andWhere('o.paymentDate >= :start')
            ->andWhere('o.paymentDate <= :end')
            ->setParameter('paid', ProductOrder::STATUS_PAID)
            ->setParameter('cancelled', ProductOrder::STATUS_CANCELLED)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $query->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!is_null($companyId)) {
            $query->andWhere('b.company = :companyId')
                ->setParameter('companyId', $companyId);
        }

        $query = $query->getQuery();

        return  $query->getSingleResult();
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     * @param null $buildingId
     * @param null $companyId
     *
     * @return mixed
     */
    public function countCompletedOrders(
        $startDate,
        $endDate,
        $payChannel = null,
        $buildingId = null,
        $companyId = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'r.buildingId = b.id')
            ->select('count(o.id) as number , SUM(o.discountPrice) as price')
            ->where('o.status = :completed')
            ->andWhere('o.startDate >= :start')
            ->andWhere('o.startDate <= :end')
            ->setParameter('completed', ProductOrder::STATUS_COMPLETED)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $query->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!is_null($companyId)) {
            $query->andWhere('b.company = :companyId')
                ->setParameter('companyId', $companyId);
        }

        $query = $query->getQuery();

        return  $query->getSingleResult();
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     * @param null $buildingId
     * @param null $companyId
     *
     * @return mixed
     */
    public function countRefundOrders(
        $startDate,
        $endDate,
        $payChannel = null,
        $buildingId = null,
        $companyId = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'r.buildingId = b.id')
            ->select('count(o.id) as number , SUM(o.actualRefundAmount) as price')
            ->where('o.status = :cancelled')
            ->andWhere('
                (o.needToRefund = :needToRefund OR
                o.refunded = :refunded)
            ')
            ->andWhere('o.cancelledDate >= :start')
            ->andWhere('o.cancelledDate <= :end')
            ->setParameter('cancelled', ProductOrder::STATUS_CANCELLED)
            ->setParameter('needToRefund', true)
            ->setParameter('refunded', true)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $query->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!is_null($companyId)) {
            $query->andWhere('b.company = :companyId')
                ->setParameter('companyId', $companyId);
        }

        $query = $query->getQuery();

        return  $query->getSingleResult();
    }

    /**
     * @param $status
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     * @param null $buildingId
     * @param null $companyId
     * @param null $limit
     * @param null $offset
     *
     * @return array|void
     */
    public function getOrdersList(
        $status,
        $startDate,
        $endDate,
        $payChannel = null,
        $buildingId = null,
        $companyId = null,
        $limit = null,
        $offset = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'r.buildingId = b.id');

        switch ($status) {
            case ProductOrder::STATUS_PAID:
                $query->where('(o.status = :paid or o.status = :cancelled)')
                    ->andWhere('o.paymentDate >= :start')
                    ->andWhere('o.paymentDate <= :end')
                    ->setParameter('paid', ProductOrder::STATUS_PAID)
                    ->setParameter('cancelled', ProductOrder::STATUS_CANCELLED)
                    ->setParameter('start', $startDate)
                    ->setParameter('end', $endDate);
                break;
            case ProductOrder::STATUS_COMPLETED:
                $query->where('o.status = :completed')
                    ->andWhere('o.startDate >= :start')
                    ->andWhere('o.startDate <= :end')
                    ->setParameter('completed', ProductOrder::STATUS_COMPLETED)
                    ->setParameter('start', $startDate)
                    ->setParameter('end', $endDate);
                break;
            case ProductOrder::STATUS_CANCELLED:
                $query->where('o.status = :cancelled')
                    ->andWhere('
                        (o.needToRefund = :needToRefund OR
                        o.refunded = :refunded)
                    ')
                    ->andWhere('o.cancelledDate >= :start')
                    ->andWhere('o.cancelledDate <= :end')
                    ->setParameter('cancelled', ProductOrder::STATUS_CANCELLED)
                    ->setParameter('needToRefund', true)
                    ->setParameter('refunded', true)
                    ->setParameter('start', $startDate)
                    ->setParameter('end', $endDate);
                break;
            default:
                return;
        }

        if (!is_null($payChannel)) {
            $query->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('r.building= :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!is_null($companyId)) {
            $query->andWhere('b.company = :companyId')
                ->setParameter('companyId', $companyId);
        }

        if (!is_null($limit) && !is_null($offset)) {
            $query->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $status
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     * @param null $buildingId
     *
     * @return int|mixed
     */
    public function countOrdersList(
        $status,
        $startDate,
        $endDate,
        $payChannel = null,
        $buildingId = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->select('COUNT(o)');

        switch ($status) {
           case ProductOrder::STATUS_PAID:
               $query->where('o.status = :paid')
                   ->andWhere('o.paymentDate >= :start')
                   ->andWhere('o.paymentDate <= :end')
                   ->setParameter('paid', ProductOrder::STATUS_PAID)
                   ->setParameter('start', $startDate)
                   ->setParameter('end', $endDate);
               break;
           case ProductOrder::STATUS_COMPLETED:
               $query->where('o.status = :completed')
                   ->andWhere('o.startDate >= :start')
                   ->andWhere('o.startDate <= :end')
                   ->setParameter('completed', ProductOrder::STATUS_COMPLETED)
                   ->setParameter('start', $startDate)
                   ->setParameter('end', $endDate);
               break;
           case ProductOrder::STATUS_CANCELLED:
               $query->where('o.status = :cancelled')
                   ->andWhere('
                        (o.needToRefund = :needToRefund OR
                        o.refunded = :refunded)
                    ')
                   ->andWhere('o.cancelledDate >= :start')
                   ->andWhere('o.cancelledDate <= :end')
                   ->setParameter('cancelled', ProductOrder::STATUS_CANCELLED)
                   ->setParameter('needToRefund', true)
                   ->setParameter('refunded', true)
                   ->setParameter('start', $startDate)
                   ->setParameter('end', $endDate);
               break;
           default:
               return 0;
       }

        if (!is_null($payChannel)) {
            $query->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('r.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $channel
     * @param $buildingId
     * @param $typeName
     * @param $startDate
     * @param $endDate
     * @param $status
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function sumOrdersByType(
        $channel,
        $buildingId,
        $typeName,
        $startDate,
        $endDate,
        $status
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'r.buildingId = b.id')
            ->select('SUM(o.discountPrice)')
            ->where('o.status = :status')
            ->andWhere('o.payChannel = :payChannel')
            ->andWhere('b.id = :buildingId')
            ->andWhere('r.type = :type')
            ->setParameter('type', $typeName)
            ->setParameter('buildingId', $buildingId)
            ->setParameter('payChannel', $channel)
            ->setParameter('status', $status);

        if (ProductOrder::STATUS_COMPLETED == $status) {
            $query->andWhere('o.startDate >= :start')
                    ->andWhere('o.startDate <= :end');
        } else {
            $query->andWhere('o.paymentDate >= :start')
                    ->andWhere('o.paymentDate <= :end');
        }

        $query->setParameter('start', $startDate)
                ->setParameter('end', $endDate);

        return  $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    public function getRoomBuildingWithOrders(
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'r.buildingId = b.id')
            ->select('DISTINCT b')
            ->where('(
                (o.status = :completed AND o.startDate >= :start AND o.startDate <= :end) OR
                (o.status = :paid AND o.paymentDate >= :start AND o.paymentDate <= :end)
            )')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->setParameter('completed', ProductOrder::STATUS_COMPLETED)
            ->setParameter('paid', ProductOrder::STATUS_PAID);

        return  $query->getQuery()->getResult();
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return mixed
     */
    public function countRefundToAccountOrders(
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('SUM(o.actualRefundAmount)')
            ->where('o.refunded = TRUE')
            ->andWhere('o.refundTo = :account')
            ->andWhere('o.refundProcessedDate > :startDate')
            ->andWhere('o.refundProcessedDate < :endDate')
            ->setParameter('account', ProductOrder::REFUND_TO_ACCOUNT)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return (float) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $orderNumber
     *
     * @return array
     */
    public function getOrderIdsByOrderNumber(
        $orderNumber
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('o.id')
            ->where('o.orderNumber LIKE :orderNumber')
            ->setParameter('orderNumber', $orderNumber.'%');

        $result = $query->getQuery()->getResult();
        $result = array_map('current', $result);

        return $result;
    }

    /**
     * @param $ids
     *
     * @return array
     */
    public function getOrdersNumbers(
        $ids
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $ids
     *
     * @return array
     */
    public function getProductOrdersByIds(
        $ids
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $channel
     * @param $payStart
     * @param $payEnd
     * @param $keyword
     * @param $keywordSearch
     * @param $status
     * @param $amountStart
     * @param $amountEnd
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getOrdersForFinance(
        $channel,
        $payStart,
        $payEnd,
        $keyword,
        $keywordSearch,
        $status,
        $amountStart,
        $amountEnd,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:Order\OrderOfflineTransfer', 't', 'with', 't.orderId = o.id')
            ->leftJoin('SandboxApiBundle:User\UserView', 'u', 'WITH', 'u.id = o.userId')
            ->where('o.payChannel = :channel')
            ->andWhere('t.id is not null')
            ->andWhere('t.transferStatus != :unpaid')
            ->setParameter('channel', $channel)
            ->setParameter('unpaid', OrderOfflineTransfer::STATUS_UNPAID);

        if (!is_null($status)) {
            switch ($status) {
                case 'pending':
                    $query->andWhere('
                            (
                                (t.transferStatus = :pending) OR
                                (t.transferStatus = :verify)
                            )
                        ')
                        ->setParameter('pending', OrderOfflineTransfer::STATUS_PENDING)
                        ->setParameter('verify', OrderOfflineTransfer::STATUS_VERIFY);
                    break;
                case 'processed':
                    $query->andWhere('
                            (
                                (t.transferStatus = :paid) OR
                                (t.transferStatus = :rejectRefund) OR
                                (t.transferStatus = :acceptRefund)
                            )
                        ')
                        ->setParameter('paid', OrderOfflineTransfer::STATUS_PAID)
                        ->setParameter('rejectRefund', OrderOfflineTransfer::STATUS_REJECT_REFUND)
                        ->setParameter('acceptRefund', OrderOfflineTransfer::STATUS_ACCEPT_REFUND);
                    break;
                case 'returned':
                    $query->andWhere('t.transferStatus = :returned')
                        ->setParameter('returned', OrderOfflineTransfer::STATUS_RETURNED);
                    break;
            }
        }

        if (!is_null($payStart)) {
            $payStart = new \DateTime($payStart);
            $query->andWhere('o.paymentDate >= :payStart')
                ->setParameter('payStart', $payStart);
        }

        if (!is_null($payEnd)) {
            $payEnd = new \DateTime($payEnd);
            $payEnd->setTime(23, 59, 59);
            $query->andWhere('o.paymentDate <= :payEnd')
                ->setParameter('payEnd', $payEnd);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'number':
                    $query->andWhere('o.orderNumber LIKE :search');
                    break;
                case 'user':
                    $query->andWhere('u.name LIKE :search');
                    break;
                case 'account':
                    $query->andWhere('
                            (u.phone LIKE :search OR 
                            u.email LIKE :search)
                        ');
                    break;
                default:
                    $query->andWhere('o.orderNumber LIKE :search');
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        if (!is_null($amountStart)) {
            $query->andWhere('o.discountPrice >= :amountStart')
                ->setParameter('amountStart', $amountStart);
        }

        if (!is_null($amountEnd)) {
            $query->andWhere('o.discountPrice <= :amountEnd')
                ->setParameter('amountEnd', $amountEnd);
        }

        $query->orderBy('o.creationDate', 'DESC');

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $channel
     * @param $payStart
     * @param $payEnd
     * @param $keyword
     * @param $keywordSearch
     * @param $status
     * @param $amountStart
     * @param $amountEnd
     *
     * @return mixed
     */
    public function countOrdersForFinance(
        $channel,
        $payStart,
        $payEnd,
        $keyword,
        $keywordSearch,
        $status,
        $amountStart,
        $amountEnd
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:Order\OrderOfflineTransfer', 't', 'with', 't.orderId = o.id')
            ->leftJoin('SandboxApiBundle:User\UserView', 'u', 'WITH', 'u.id = o.userId')
            ->select('COUNT(o)')
            ->where('o.payChannel = :channel')
            ->andWhere('t.id is not null')
            ->andWhere('t.transferStatus != :unpaid')
            ->setParameter('channel', $channel)
            ->setParameter('unpaid', OrderOfflineTransfer::STATUS_UNPAID);

        if (!is_null($status)) {
            switch ($status) {
                case 'pending':
                    $query->andWhere('
                            (
                                (t.transferStatus = :pending) OR
                                (t.transferStatus = :verify)
                            )
                            
                        ')
                        ->setParameter('pending', OrderOfflineTransfer::STATUS_PENDING)
                        ->setParameter('verify', OrderOfflineTransfer::STATUS_VERIFY);
                    break;
                case 'processed':
                    $query->andWhere('
                            (
                                (t.transferStatus = :paid) OR
                                (t.transferStatus = :rejectRefund) OR
                                (t.transferStatus = :acceptRefund)
                            )
                        ')
                        ->setParameter('paid', OrderOfflineTransfer::STATUS_PAID)
                        ->setParameter('rejectRefund', OrderOfflineTransfer::STATUS_REJECT_REFUND)
                        ->setParameter('acceptRefund', OrderOfflineTransfer::STATUS_ACCEPT_REFUND);
                    break;
                case 'returned':
                    $query->andWhere('t.transferStatus = :returned')
                        ->setParameter('returned', OrderOfflineTransfer::STATUS_RETURNED);
                    break;
            }
        }

        if (!is_null($payStart)) {
            $payStart = new \DateTime($payStart);
            $query->andWhere('o.paymentDate >= :payStart')
                ->setParameter('payStart', $payStart);
        }

        if (!is_null($payEnd)) {
            $payEnd = new \DateTime($payEnd);
            $payEnd->setTime(23, 59, 59);
            $query->andWhere('o.paymentDate <= :payEnd')
                ->setParameter('payEnd', $payEnd);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'number':
                    $query->andWhere('o.orderNumber LIKE :search');
                    break;
                case 'user':
                    $query->andWhere('u.name LIKE :search');
                    break;
                case 'account':
                    $query->andWhere('
                            (u.phone LIKE :search OR 
                            u.email LIKE :search)
                        ');
                    break;
                default:
                    $query->andWhere('o.orderNumber LIKE :search');
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        if (!is_null($amountStart)) {
            $query->andWhere('o.discountPrice >= :amountStart')
                ->setParameter('amountStart', $amountStart);
        }

        if (!is_null($amountEnd)) {
            $query->andWhere('o.discountPrice <= :amountEnd')
                ->setParameter('amountEnd', $amountEnd);
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return $result;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param null $companyId
     *
     * @return mixed
     */
    public function getCompletedOrders(
        $startDate,
        $endDate,
        $companyId = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'r.buildingId = b.id')
            ->select('o.discountPrice, o.serviceFee, b.companyId')
            ->where('o.status = :completed')
            ->andWhere('o.startDate >= :start')
            ->andWhere('o.startDate <= :end')
            ->setParameter('completed', ProductOrder::STATUS_COMPLETED)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($companyId)) {
            $query->andWhere('b.company = :companyId')
                ->setParameter('companyId', $companyId);
        }

        return  $query->getQuery()->getResult();
    }

    public function sumCompletedPreorder(
        $startDate,
        $endDate,
        $companyId
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'r.buildingId = b.id')
            ->select('sum(o.discountPrice)')
            ->where('o.status = :completed')
            ->andWhere('o.startDate >= :start')
            ->andWhere('o.startDate <= :end')
            ->andWhere('b.company = :companyId')
            ->andWhere('o.type = :type')
            ->setParameter('completed', ProductOrder::STATUS_COMPLETED)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->setParameter('companyId', $companyId)
            ->setParameter('type', ProductOrder::PREORDER_TYPE)
        ;

        return  $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return mixed
     */
    public function getOfficialAdminCompletedOrderSummary(
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.paymentDate >= :start')
            ->andWhere('o.paymentDate <= :end')
            ->andWhere('o.payChannel != :account')
            ->andWhere('(o.refundTo IS NULL OR o.refundTo = :account)')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        return  $query->getQuery()->getResult();
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param null  $companyId
     * @param array $orderTypes
     *
     * @return array
     */
    public function getCompletedOrderSummary(
        $startDate,
        $endDate,
        $companyId = null,
        $orderTypes = array()
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'o.productId = p.id')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'p.roomId = r.id')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'r.buildingId = b.id')
            ->where('
                    o.status = :completed  OR
                    (
                        o.status = :cancelled AND 
                        (o.needToRefund = :needToRefund OR
                        o.refunded = :refunded
                        )
                        
                    )    
                ')
            ->andWhere('o.startDate >= :start')
            ->andWhere('o.startDate <= :end')
            ->setParameter('completed', ProductOrder::STATUS_COMPLETED)
            ->setParameter('cancelled', ProductOrder::STATUS_CANCELLED)
            ->setParameter('needToRefund', true)
            ->setParameter('refunded', true)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($companyId)) {
            $query->andWhere('b.company = :companyId')
                ->setParameter('companyId', $companyId);
        }

        if (!empty($orderTypes)) {
            $query->andWhere('o.type in (:type)')
                ->setParameter('type', $orderTypes);
        }

        $query->orderBy('o.creationDate', 'DESC');

        return  $query->getQuery()->getResult();
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     *
     * @return float|mixed
     */
    public function getIncomingTotalAmount(
        $startDate,
        $endDate,
        $payChannel = null
    ) {
        // get product order amount
        $productOrderAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Order\ProductOrder', 'o')
            ->select('SUM(o.discountPrice)')
            ->where('o.paymentDate >= :start')
            ->andWhere('o.paymentDate <= :end')
            ->andWhere('o.payChannel != :account')
            ->andWhere('(o.refundTo IS NULL OR o.refundTo = :account)')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $productOrderAmountQuery->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $productOrderAmount = $productOrderAmountQuery->getQuery()
            ->getSingleScalarResult();
        $productOrderAmount = (float) $productOrderAmount;

        // get shop order amount
        $shopOrderAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Shop\ShopOrder', 'so')
            ->select('SUM(so.price)')
            ->where('so.paymentDate >= :start')
            ->andWhere('so.paymentDate <= :end')
            ->andWhere('so.payChannel != :account')
            ->andWhere('so.unoriginal = FALSE')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $shopOrderAmountQuery->andWhere('so.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $shopOrderAmount = $shopOrderAmountQuery->getQuery()
            ->getSingleScalarResult();
        $shopOrderAmount = (float) $shopOrderAmount;

        // get event order amount
        $eventOrderAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Event\EventOrder', 'eo')
            ->select('SUM(eo.price)')
            ->where('eo.paymentDate >= :start')
            ->andWhere('eo.paymentDate <= :end')
            ->andWhere('eo.payChannel != :account')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $eventOrderAmountQuery->andWhere('eo.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $eventOrderAmount = $eventOrderAmountQuery->getQuery()
            ->getSingleScalarResult();
        $eventOrderAmount = (float) $eventOrderAmount;

        // lease bill amount
        $leaseBillAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Lease\LeaseBill', 'b')
            ->select('SUM(b.revisedAmount)')
            ->where('b.paymentDate >= :start')
            ->andWhere('b.paymentDate <= :end')
            ->andWhere('b.payChannel != :account')
            ->andWhere('b.payChannel != :salesOffline')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('salesOffline', LeaseBill::CHANNEL_SALES_OFFLINE)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $leaseBillAmountQuery->andWhere('b.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $leaseBillAmount = $leaseBillAmountQuery->getQuery()
            ->getSingleScalarResult();
        $leaseBillAmount = (float) $leaseBillAmount;

        // top up order amount
        $topUpAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Order\TopUpOrder', 'to')
            ->select('SUM(to.price)')
            ->where('to.paymentDate >= :start')
            ->andWhere('to.paymentDate <= :end')
            ->andWhere('to.refundToAccount = FALSE')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $topUpAmountQuery->andWhere('to.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $topUpAmount = $topUpAmountQuery->getQuery()
            ->getSingleScalarResult();
        $topUpAmount = (float) $topUpAmount;

        // membership card order amount
        $cardOrderAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:MembershipCard\MembershipOrder', 'mo')
            ->select('SUM(mo.price)')
            ->where('mo.paymentDate >= :start')
            ->andWhere('mo.paymentDate <= :end')
            ->andWhere('mo.payChannel != :account')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $cardOrderAmountQuery->andWhere('mo.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $cardOrderAmount = $cardOrderAmountQuery->getQuery()
            ->getSingleScalarResult();
        $cardOrderAmount = (float) $cardOrderAmount;

        // service order amount
        $serviceOrderAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Service\ServiceOrder', 'so')
            ->select('SUM(so.price)')
            ->where('so.paymentDate >= :start')
            ->andWhere('so.paymentDate <= :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $serviceOrderAmountQuery->andWhere('so.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $serviceOrderAmount = $serviceOrderAmountQuery->getQuery()
            ->getSingleScalarResult();
        $serviceOrderAmount = (float) $serviceOrderAmount;

        $totalAmount = $productOrderAmount
            + $shopOrderAmount
            + $eventOrderAmount
            + $leaseBillAmount
            + $topUpAmount
            + $cardOrderAmount
            + $serviceOrderAmount;

        return $totalAmount;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     *
     * @return float|mixed
     */
    public function countIncomingOrders(
        $startDate,
        $endDate,
        $payChannel = null
    ) {
        // get product order count
        $productOrderCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Order\ProductOrder', 'o')
            ->select('COUNT(o.discountPrice)')
            ->where('o.paymentDate >= :start')
            ->andWhere('o.paymentDate <= :end')
            ->andWhere('o.payChannel != :account')
            ->andWhere('(o.refundTo IS NULL OR o.refundTo = :account)')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $productOrderCountQuery->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $productOrderCount = $productOrderCountQuery->getQuery()
            ->getSingleScalarResult();
        $productOrderCount = (int) $productOrderCount;

        // get shop order count
        $shopOrderCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Shop\ShopOrder', 'so')
            ->select('COUNT(so.price)')
            ->where('so.status = :completed')
            ->andWhere('so.modificationDate >= :start')
            ->andWhere('so.modificationDate <= :end')
            ->andWhere('so.payChannel != :account')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('completed', ShopOrder::STATUS_COMPLETED)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $shopOrderCountQuery->andWhere('so.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $shopOrderCount = $shopOrderCountQuery->getQuery()
            ->getSingleScalarResult();
        $shopOrderCount = (int) $shopOrderCount;

        // get event order count
        $eventOrderCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Event\EventOrder', 'eo')
            ->select('COUNT(eo.price)')
            ->where('eo.status = :completed')
            ->andWhere('eo.paymentDate >= :start')
            ->andWhere('eo.paymentDate <= :end')
            ->andWhere('eo.payChannel != :account')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('completed', EventOrder::STATUS_COMPLETED)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $eventOrderCountQuery->andWhere('eo.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $eventOrderCount = $eventOrderCountQuery->getQuery()
            ->getSingleScalarResult();
        $eventOrderCount = (int) $eventOrderCount;

        // lease bill count
        $leaseBillCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Lease\LeaseBill', 'b')
            ->select('COUNT(b.revisedAmount)')
            ->andWhere('b.paymentDate >= :start')
            ->andWhere('b.paymentDate <= :end')
            ->andWhere('b.payChannel != :account')
            ->andWhere('b.payChannel != :salesOffline')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('salesOffline', LeaseBill::CHANNEL_SALES_OFFLINE)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $leaseBillCountQuery->andWhere('b.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $leaseBillCount = $leaseBillCountQuery->getQuery()
            ->getSingleScalarResult();
        $leaseBillCount = (int) $leaseBillCount;

        // top up order count
        $topUpCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Order\TopUpOrder', 'to')
            ->select('COUNT(to.price)')
            ->where('to.paymentDate >= :start')
            ->andWhere('to.paymentDate <= :end')
            ->andWhere('to.payChannel != :account')
            ->andWhere('to.refundToAccount = FALSE')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $topUpCountQuery->andWhere('to.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $topUpCount = $topUpCountQuery->getQuery()
            ->getSingleScalarResult();
        $topUpCount = (int) $topUpCount;

        // membership card order count
        $cardOrderCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:MembershipCard\MembershipOrder', 'mo')
            ->select('COUNT(mo.price)')
            ->where('mo.paymentDate >= :start')
            ->andWhere('mo.paymentDate <= :end')
            ->andWhere('mo.payChannel != :account')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $cardOrderCountQuery->andWhere('mo.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $cardOrderCount = $cardOrderCountQuery->getQuery()
            ->getSingleScalarResult();
        $cardOrderCount = (int) $cardOrderCount;

        // service order count
        $serviceOrderCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Service\ServiceOrder', 'so')
            ->select('COUNT(so.price)')
            ->where('so.paymentDate >= :start')
            ->andWhere('so.paymentDate <= :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $serviceOrderCountQuery->andWhere('so.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $serviceOrderCount = $serviceOrderCountQuery->getQuery()
            ->getSingleScalarResult();
        $serviceOrderCount = (int) $serviceOrderCount;

        $totalCount = $productOrderCount
            + $shopOrderCount
            + $eventOrderCount
            + $leaseBillCount
            + $topUpCount
            + $cardOrderCount
            + $serviceOrderCount
        ;

        return $totalCount;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     *
     * @return float
     */
    public function getRefundedOrderAmount(
        $startDate,
        $endDate,
        $payChannel = null
    ) {
        // get product order amount
        $productOrderAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Order\ProductOrder', 'o')
            ->select('SUM(o.actualRefundAmount)')
            ->where('o.refunded = TRUE')
            ->andWhere('o.refundProcessedDate >= :start')
            ->andWhere('o.refundProcessedDate <= :end')
            ->andWhere('o.payChannel != :account')
            ->andWhere('o.refundTo IS NULL')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $productOrderAmountQuery->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $productOrderAmount = $productOrderAmountQuery->getQuery()
            ->getSingleScalarResult();
        $productOrderAmount = (float) $productOrderAmount;

        // get shop order amount
        $shopOrderAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Shop\ShopOrder', 'so')
            ->select('SUM(so.refundAmount)')
            ->where('so.refunded = TRUE')
            ->andWhere('so.refundProcessedDate >= :start')
            ->andWhere('so.refundProcessedDate <= :end')
            ->andWhere('so.payChannel != :account')
            ->andWhere('so.unoriginal = FALSE')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $shopOrderAmountQuery->andWhere('so.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $shopOrderAmount = $shopOrderAmountQuery->getQuery()
            ->getSingleScalarResult();
        $shopOrderAmount = (float) $shopOrderAmount;

        $totalRefundedAmount = $productOrderAmount + $shopOrderAmount;

        return $totalRefundedAmount;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     *
     * @return int
     */
    public function countRefundedOrders(
        $startDate,
        $endDate,
        $payChannel = null
    ) {
        // get product order count
        $productOrderCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Order\ProductOrder', 'o')
            ->select('COUNT(o)')
            ->where('o.refunded = TRUE')
            ->andWhere('o.refundProcessedDate >= :start')
            ->andWhere('o.refundProcessedDate <= :end')
            ->andWhere('o.payChannel != :account')
            ->andWhere('o.refundTo IS NULL')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $productOrderCountQuery->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $productOrderCount = $productOrderCountQuery->getQuery()
            ->getSingleScalarResult();
        $productOrderCount = (int) $productOrderCount;

        // get shop order count
        $shopOrderCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Shop\ShopOrder', 'so')
            ->select('COUNT(so)')
            ->where('so.refunded = TRUE')
            ->andWhere('so.refundProcessedDate >= :start')
            ->andWhere('so.refundProcessedDate <= :end')
            ->andWhere('so.payChannel != :account')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $shopOrderCountQuery->andWhere('so.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $shopOrderCount = $shopOrderCountQuery->getQuery()
            ->getSingleScalarResult();
        $shopOrderCount = (int) $shopOrderCount;

        $totalRefundedCount = $productOrderCount + $shopOrderCount;

        return $totalRefundedCount;
    }

    /**
     * @return int
     */
    public function countNeedToRefundOrders()
    {
        // get product order count
        $productOrderCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Order\ProductOrder', 'o')
            ->select('COUNT(o.discountPrice)')
            ->where('o.refunded = FALSE')
            ->andWhere('o.needToRefund = TRUE');

        $productOrderCount = $productOrderCountQuery->getQuery()
            ->getSingleScalarResult();
        $productOrderCount = (int) $productOrderCount;

        // get shop order count
        $shopOrderCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Shop\ShopOrder', 'so')
            ->select('COUNT(so.price)')
            ->where('so.refunded = :refunded')
            ->andWhere('so.needToRefund = :needed')
            ->andWhere('so.status = :status')
            ->andWhere('so.unoriginal = :unoriginal')
            ->setParameter('unoriginal', false)
            ->setParameter('status', ShopOrder::STATUS_REFUNDED)
            ->setParameter('needed', true)
            ->setParameter('refunded', false);

        $shopOrderCount = $shopOrderCountQuery->getQuery()
            ->getSingleScalarResult();
        $shopOrderCount = (int) $shopOrderCount;

        $totalRefundedCount = $productOrderCount + $shopOrderCount;

        return $totalRefundedCount;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     *
     * @return mixed
     */
    public function getTopUpAmount(
        $startDate,
        $endDate,
        $payChannel = null
    ) {
        $topUpAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Order\TopUpOrder', 'to')
            ->select('SUM(to.price)')
            ->where('to.payChannel IS NOT NULL')
            ->andWhere('to.paymentDate >= :start')
            ->andWhere('to.paymentDate <= :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $topUpAmountQuery->andWhere('to.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $topUpAmount = $topUpAmountQuery->getQuery()
            ->getSingleScalarResult();
        $topUpAmount = (float) $topUpAmount;

        return $topUpAmount;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     *
     * @return float|mixed
     */
    public function countTopUpOrder(
        $startDate,
        $endDate,
        $payChannel = null
    ) {
        $topUpCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Order\TopUpOrder', 'to')
            ->select('COUNT(to.price)')
            ->where('to.payChannel IS NOT NULL')
            ->andWhere('to.paymentDate >= :start')
            ->andWhere('to.paymentDate <= :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $topUpCountQuery->andWhere('to.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        $topUpCount = $topUpCountQuery->getQuery()
            ->getSingleScalarResult();
        $topUpCount = (int) $topUpCount;

        return $topUpCount;
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return float
     */
    public function getRefundedToBalanceAmount(
        $startDate,
        $endDate
    ) {
        $productOrderRefundAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Order\ProductOrder', 'o')
            ->select('SUM(o.actualRefundAmount)')
            ->where('o.status = :cancelled')
            ->andWhere('(o.payChannel = :account OR o.refundTo = :account)')
            ->andWhere('o.refunded = TRUE')
            ->andWhere('o.refundProcessedDate >= :start')
            ->andWhere('o.refundProcessedDate <= :end')
            ->setParameter('cancelled', ProductOrder::STATUS_CANCELLED)
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        $productOrderRefundAmount = $productOrderRefundAmountQuery->getQuery()
            ->getSingleScalarResult();
        $productOrderRefundAmount = (float) $productOrderRefundAmount;

        $shopOrderRefundAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Shop\ShopOrder', 'so')
            ->select('SUM(so.refundAmount)')
            ->where('so.status = :cancelled')
            ->andWhere('so.payChannel = :account')
            ->andWhere('so.refunded = TRUE')
            ->andWhere('so.refundProcessedDate >= :start')
            ->andWhere('so.refundProcessedDate <= :end')
            ->setParameter('cancelled', ProductOrder::STATUS_CANCELLED)
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        $shopOrderRefundAmount = $shopOrderRefundAmountQuery->getQuery()
            ->getSingleScalarResult();
        $shopOrderRefundAmount = (float) $shopOrderRefundAmount;

        $totalRefundToBalanceAmount = $productOrderRefundAmount + $shopOrderRefundAmount;

        return $totalRefundToBalanceAmount;
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return float
     */
    public function countRefundedToBalance(
        $startDate,
        $endDate
    ) {
        $productOrderRefundCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Order\ProductOrder', 'o')
            ->select('COUNT(o.actualRefundAmount)')
            ->where('o.status = :cancelled')
            ->andWhere('(o.payChannel = :account OR o.refundTo = :account)')
            ->andWhere('o.refunded = TRUE')
            ->andWhere('o.refundProcessedDate >= :start')
            ->andWhere('o.refundProcessedDate <= :end')
            ->setParameter('cancelled', ProductOrder::STATUS_CANCELLED)
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        $productOrderRefundCount = $productOrderRefundCountQuery->getQuery()
            ->getSingleScalarResult();
        $productOrderRefundCount = (int) $productOrderRefundCount;

        $shopOrderRefundCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Shop\ShopOrder', 'so')
            ->select('SUM(so.refundAmount)')
            ->where('so.status = :cancelled')
            ->andWhere('so.payChannel = :account')
            ->andWhere('so.refunded = TRUE')
            ->andWhere('so.refundProcessedDate >= :start')
            ->andWhere('so.refundProcessedDate <= :end')
            ->setParameter('cancelled', ProductOrder::STATUS_CANCELLED)
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        $shopOrderRefundCount = $shopOrderRefundCountQuery->getQuery()
            ->getSingleScalarResult();
        $shopOrderRefundCount = (int) $shopOrderRefundCount;

        $totalRefundToBalanceCount = $productOrderRefundCount + $shopOrderRefundCount;

        return $totalRefundToBalanceCount;
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return float|mixed
     */
    public function spaceOrderByAccountAmount(
        $startDate,
        $endDate
    ) {
        // get product order amount
        $productOrderAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Order\ProductOrder', 'o')
            ->select('SUM(o.discountPrice)')
            ->where('o.paymentDate >= :start')
            ->andWhere('o.paymentDate <= :end')
            ->andWhere('o.payChannel = :account')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        $productOrderAmount = $productOrderAmountQuery->getQuery()
            ->getSingleScalarResult();
        $productOrderAmount = (float) $productOrderAmount;

        return $productOrderAmount;
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return int|mixed
     */
    public function countSpaceOrderByAccount(
        $startDate,
        $endDate
    ) {
        // get product order count
        $productOrderCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Order\ProductOrder', 'o')
            ->select('COUNT(o.discountPrice)')
            ->where('o.paymentDate >= :start')
            ->andWhere('o.paymentDate <= :end')
            ->andWhere('o.payChannel = :account')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        $productOrderCount = $productOrderCountQuery->getQuery()
            ->getSingleScalarResult();
        $productOrderCount = (int) $productOrderCount;

        return $productOrderCount;
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return float|mixed
     */
    public function shopOrderByAccountAmount(
        $startDate,
        $endDate
    ) {
        $shopOrderAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Shop\ShopOrder', 'so')
            ->select('SUM(so.price)')
            ->where('so.status = :completed')
            ->andWhere('so.modificationDate >= :start')
            ->andWhere('so.modificationDate <= :end')
            ->andWhere('so.payChannel = :account')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('completed', ShopOrder::STATUS_COMPLETED)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        $shopOrderAmount = $shopOrderAmountQuery->getQuery()
            ->getSingleScalarResult();
        $shopOrderAmount = (float) $shopOrderAmount;

        return $shopOrderAmount;
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return float|mixed
     */
    public function countShopOrderByAccount(
        $startDate,
        $endDate
    ) {
        $shopOrderCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Shop\ShopOrder', 'so')
            ->select('COUNT(so.price)')
            ->where('so.status = :completed')
            ->andWhere('so.modificationDate >= :start')
            ->andWhere('so.modificationDate <= :end')
            ->andWhere('so.payChannel = :account')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('completed', ShopOrder::STATUS_COMPLETED)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        $shopOrderCount = $shopOrderCountQuery->getQuery()
            ->getSingleScalarResult();
        $shopOrderCount = (int) $shopOrderCount;

        return $shopOrderCount;
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return float|mixed
     */
    public function activityOrderByAccountAmount(
        $startDate,
        $endDate
    ) {
        $eventOrderAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Event\EventOrder', 'eo')
            ->select('SUM(eo.price)')
            ->where('eo.paymentDate >= :start')
            ->andWhere('eo.paymentDate <= :end')
            ->andWhere('eo.payChannel = :account')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        $eventOrderAmount = $eventOrderAmountQuery->getQuery()
            ->getSingleScalarResult();
        $eventOrderAmount = (float) $eventOrderAmount;

        return $eventOrderAmount;
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return float|mixed
     */
    public function countActivityOrderByAccount(
        $startDate,
        $endDate
    ) {
        $eventOrderCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Event\EventOrder', 'eo')
            ->select('COUNT(eo.price)')
            ->where('eo.paymentDate >= :start')
            ->andWhere('eo.paymentDate <= :end')
            ->andWhere('eo.payChannel = :account')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        $eventOrderCount = $eventOrderCountQuery->getQuery()
            ->getSingleScalarResult();
        $eventOrderCount = (int) $eventOrderCount;

        return $eventOrderCount;
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return float|mixed
     */
    public function membershipCardOrderByAccount(
        $startDate,
        $endDate
    ) {
        $cardOrderAmountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:MembershipCard\MembershipOrder', 'mo')
            ->select('SUM(mo.price)')
            ->where('mo.paymentDate >= :start')
            ->andWhere('mo.paymentDate <= :end')
            ->andWhere('mo.payChannel = :account')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        $cardOrderAmount = $cardOrderAmountQuery->getQuery()
            ->getSingleScalarResult();
        $cardOrderAmount = (float) $cardOrderAmount;

        return $cardOrderAmount;
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return float|mixed
     */
    public function countMembershipCardOrderByAccount(
        $startDate,
        $endDate
    ) {
        $cardOrderCountQuery = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:MembershipCard\MembershipOrder', 'mo')
            ->select('COUNT(mo.price)')
            ->where('mo.paymentDate >= :start')
            ->andWhere('mo.paymentDate <= :end')
            ->andWhere('mo.payChannel = :account')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        $cardOrderCount = $cardOrderCountQuery->getQuery()
            ->getSingleScalarResult();
        $cardOrderCount = (int) $cardOrderCount;

        return $cardOrderCount;
    }

    /**
     * @param $productId
     * @param $start
     * @param $end
     *
     * @return array
     */
    public function getRoomUsersUsage(
        $productId,
        $start,
        $end
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.productId = :productId')
            ->andWhere('o.rejected = FALSE')
            ->andWhere('
                o.status = :paid OR 
                o.status = :completed OR
                (o.status = :unpaid AND o.type = :preorder)
            ')
            ->andWhere('
                (o.startDate <= :start AND o.endDate > :start) OR
                (o.startDate < :end AND o.endDate >= :end) OR
                (o.startDate >= :start AND o.endDate <= :end)
            ')
            ->setParameter('productId', $productId)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('paid', ProductOrder::STATUS_PAID)
            ->setParameter('unpaid', ProductOrder::STATUS_UNPAID)
            ->setParameter('completed', ProductOrder::STATUS_COMPLETED)
            ->setParameter('preorder', ProductOrder::PREORDER_TYPE);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $buildingIds
     * @param $date
     *
     * @return array
     */
    public function getUsingOrder(
        $buildingIds,
        $date
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('o.product', 'p')
            ->leftJoin('p.room', 'r')
            ->where('r.building in (:building)')
            ->andWhere('o.endDate >= :date')
            ->andWhere('o.status = :paid OR o.status = :completed')
            ->andWhere('o.rejected = :rejected')
            ->setParameter('building', $buildingIds)
            ->setParameter('date', $date)
            ->setParameter('paid', ProductOrder::STATUS_PAID)
            ->setParameter('completed', ProductOrder::STATUS_COMPLETED)
            ->setParameter('rejected', false);

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
    public function findPendingEvaluationOrder(
        $userId,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.status = :completed')
            ->andWhere('o.userId = :userId')
            ->andWhere('o.hasEvaluated = false')
            ->setParameter('completed', ProductOrder::STATUS_COMPLETED)
            ->setParameter('userId', $userId);

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        $query->orderBy('o.modificationDate', 'DESC');

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $userId
     *
     * @return int
     */
    public function countPendingEvaluationOrder(
        $userId
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.status = :completed')
            ->andWhere('o.userId = :userId')
            ->andWhere('o.hasEvaluated = false')
            ->setParameter('completed', ProductOrder::STATUS_COMPLETED)
            ->setParameter('userId', $userId);

        $result = $query->getQuery()->getSingleScalarResult();

        return (int) $result;
    }

    public function sumPendingEvaluationOrder(
        $userId,
        $startDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('SUM(o.discountPrice)')
            ->where('o.status = :completed')
            ->andWhere('o.userId = :userId')
            ->andWhere('o.hasEvaluated = false')
            ->andWhere('o.endDate >= :startDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('completed', ProductOrder::STATUS_COMPLETED)
            ->setParameter('userId', $userId);

        $result = $query->getQuery()->getSingleScalarResult();

        return (float) $result;
    }

    public function findTipOrders(
        $userId
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.status = :completed')
            ->andWhere('o.userId = :userId')
            ->andWhere('o.tip = false')
            ->andWhere('o.hasEvaluated = false')
            ->setParameter('completed', ProductOrder::STATUS_COMPLETED)
            ->setParameter('userId', $userId);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    public function countValidOrder(
        $userId,
        $now
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->leftJoin('SandboxApiBundle:Order\InvitedPeople', 'i', 'WITH', 'i.orderId = o.id')
            ->where(
                '(
                    o.userId = :userId OR
                    o.appointed = :userId OR
                    i.userId = :userId
                )'
            )
            ->andWhere('o.status != :cancelled')
            ->andWhere('o.startDate <= :now AND o.endDate > :now')
            ->setParameter('now', $now)
            ->setParameter('userId', $userId)
            ->setParameter('cancelled', ProductOrder::STATUS_CANCELLED);

        $result = $query->getQuery()->getSingleScalarResult();

        return (int) $result;
    }

    public function getPreOrders(
        $companyId,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('o.orderNumber as order_number')
            ->leftJoin('o.product', 'p')
            ->leftJoin('p.room', 'r')
            ->leftJoin('r.building', 'b')
            ->andWhere('o.type = :preorder')
            ->setParameter('preorder', ProductOrder::PREORDER_TYPE);

        if ($startDate) {
            $query->andWhere('o.creationDate >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $query->andWhere('o.creationDate <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        if (!is_null($companyId)) {
            $query->andWhere('b.company = :companyId')
                ->setParameter('companyId', $companyId);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $myBuildingIds
     * @param $allOrder
     * @param $status
     * @param null $startDate
     * @param null $endDate
     *
     * @return int
     */
    public function countOrders(
        $myBuildingIds,
        $allOrder,
        $status,
        $startDate = null,
        $endDate = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->leftJoin('o.product', 'p')
            ->leftJoin('p.room', 'r')
            ->where('r.buildingId in (:buildings)')
            ->setParameter('buildings', $myBuildingIds);

        if (!$allOrder) {
            $query->andWhere('
                    (
                        (o.status != :unpaid) AND 
                        (o.paymentDate IS NOT NULL) OR 
                        (o.type = :preOrder) OR 
                        (o.type = :officialPreOrder)
                    )
                ')
                ->setParameter('unpaid', ProductOrder::STATUS_UNPAID)
                ->setParameter('preOrder', ProductOrder::PREORDER_TYPE)
                ->setParameter('officialPreOrder', ProductOrder::OFFICIAL_PREORDER_TYPE);
        }

        if ($status) {
            $query->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        if ($startDate) {
            $query->andWhere('o.creationDate >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $query->andWhere('o.creationDate <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return (int) $result;
    }

    /**
     * @param $customerId
     * @param $myBuildingIds
     *
     * @return mixed
     */
    public function countCustomerAllProductOrders(
        $customerId,
        $myBuildingIds
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->leftJoin('SandboxApiBundle:Order\ProductOrderRecord', 'por', 'WITH', 'por.orderId = o.id')
            ->where('o.customerId = :customerId')
            ->andWhere('por.buildingId IN (:buildingIds)')
            ->setParameter('customerId', $customerId)
            ->setParameter('buildingIds', $myBuildingIds);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $myBuildingIds
     * @param $allOrder
     * @param $status
     * @param null $startDate
     * @param null $endDate
     * @param null $limit
     * @param null $offset
     *
     * @return array
     */
    public function getOrderLists(
        $myBuildingIds,
        $allOrder,
        $status,
        $startDate = null,
        $endDate = null,
        $limit = null,
        $offset = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('o.product', 'p')
            ->leftJoin('p.room', 'r')
            ->where('r.buildingId in (:buildings)')
            ->setParameter('buildings', $myBuildingIds);

        if (!$allOrder) {
            $query->andWhere('
                    (
                        (o.status != :unpaid) AND 
                        (o.paymentDate IS NOT NULL) OR 
                        (o.type = :preOrder) OR 
                        (o.type = :officialPreOrder)
                    )
                ')
                ->setParameter('unpaid', ProductOrder::STATUS_UNPAID)
                ->setParameter('preOrder', ProductOrder::PREORDER_TYPE)
                ->setParameter('officialPreOrder', ProductOrder::OFFICIAL_PREORDER_TYPE);
        }

        if ($status) {
            $query->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        if ($startDate) {
            $query->andWhere('o.creationDate >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $query->andWhere('o.creationDate <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        $query->orderBy('o.startDate', 'DESC');

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $customerId
     * @param $buildingIds
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function findCustomerProductsOrder(
        $customerId,
        $buildingIds,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('o.product', 'p')
            ->leftJoin('p.room', 'r')
            ->where('o.customerId = :customerId')
            ->andWhere('r.building in (:buildingId)')
            ->setParameter('customerId', $customerId)
            ->setParameter('buildingId', $buildingIds);

        $query->orderBy('o.creationDate', 'DESC');

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }
}
