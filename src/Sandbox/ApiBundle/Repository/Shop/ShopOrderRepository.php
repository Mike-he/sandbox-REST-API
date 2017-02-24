<?php

namespace Sandbox\ApiBundle\Repository\Shop;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;

/**
 * ShopOrderRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ShopOrderRepository extends EntityRepository
{
    /**
     * @param $shopId
     * @param $status
     * @param $start
     * @param $end
     * @param $sort
     * @param $search
     * @param $platform
     * @param $$myShopIds
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getAdminShopOrders(
        $shopId,
        $status,
        $start,
        $end,
        $sort,
        $search,
        $platform,
        $myShopIds,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.status != :unpaid')
            ->andWhere('o.status != :cancelled')
            ->andWhere('o.shopId IN (:shopIds)')
            ->orderBy('o.modificationDate', $sort)
            ->setParameter('shopIds', $myShopIds)
            ->setParameter('unpaid', ShopOrder::STATUS_UNPAID)
            ->setParameter('cancelled', ShopOrder::STATUS_CANCELLED);

        if ($platform == ShopOrder::PLATFORM_BACKEND
            && (is_null($search) || empty($search))) {
            $query = $query->andWhere('o.unoriginal = :unoriginal')
                ->setParameter('unoriginal', false);
        }

        if (!is_null($shopId) && !empty($shopId)) {
            $query = $query->andWhere('o.shopId = :shopId')
                ->setParameter('shopId', $shopId);
        }

        if (!is_null($status) && !empty($status)) {
            $query = $query->andWhere('o.status IN (:status)')
                ->setParameter('status', $status);
        }

        if (!is_null($start) && !empty($start)) {
            $start = new \DateTime($start);
            $query = $query->andWhere('o.paymentDate >= :start')
                ->setParameter('start', $start);
        }

        if (!is_null($end) && !empty($end)) {
            $end = new \DateTime($end);
            $end->setTime(23, 59, 59);
            $query = $query->andWhere('o.paymentDate <= :end')
                ->setParameter('end', $end);
        }

        // Search products by product Id or product name.
        if (!is_null($search) && !empty($search)) {
            $query->join('SandboxApiBundle:User\UserProfile', 'u', 'WITH', 'u.userId = o.userId')
                ->andWhere('o.orderNumber LIKE :search OR u.name LIKE :search')
                ->setParameter('search', "%$search%");
        }

        if (!is_null($limit) && !is_null($offset)) {
            $query = $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        return $query->getQuery()->getResult();
    }

    public function countAdminShopOrders(
        $shopId,
        $status,
        $start,
        $end,
        $search,
        $myShopIds
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->where('o.status != :unpaid')
            ->andWhere('o.status != :cancelled')
            ->andWhere('o.shopId IN (:shopIds)')
            ->setParameter('shopIds', $myShopIds)
            ->setParameter('unpaid', ShopOrder::STATUS_UNPAID)
            ->setParameter('cancelled', ShopOrder::STATUS_CANCELLED);

        if (is_null($search) || empty($search)) {
            $query = $query->andWhere('o.unoriginal = :unoriginal')
                ->setParameter('unoriginal', false);
        }

        if (!is_null($shopId) && !empty($shopId)) {
            $query = $query->andWhere('o.shopId = :shopId')
                ->setParameter('shopId', $shopId);
        }

        if (!is_null($status) && !empty($status)) {
            $query = $query->andWhere('o.status IN (:status)')
                ->setParameter('status', $status);
        }

        if (!is_null($start) && !empty($start)) {
            $start = new \DateTime($start);
            $query = $query->andWhere('o.paymentDate >= :start')
                ->setParameter('start', $start);
        }

        if (!is_null($end) && !empty($end)) {
            $end = new \DateTime($end);
            $end->setTime(23, 59, 59);
            $query = $query->andWhere('o.paymentDate <= :end')
                ->setParameter('end', $end);
        }

        // Search products by product Id or product name.
        if (!is_null($search) && !empty($search)) {
            $query->join('SandboxApiBundle:User\UserProfile', 'u', 'WITH', 'u.userId = o.userId')
                ->andWhere('o.orderNumber LIKE :search OR u.name LIKE :search')
                ->setParameter('search', "%$search%");
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $shopId
     * @param array $channel
     * @param $status
     * @param $payDate
     * @param $payStart
     * @param $payEnd
     * @param $keyword
     * @param $keywordSearch
     * @param $user
     * @param $cityId
     * @param $company
     * @param $buildingId
     * @param $shopId
     * @param $refundStatus
     * @param null $limit
     * @param null $offset
     *
     * @return array
     */
    public function getAdminShopOrdersForBackend(
        $shopId,
        $channel,
        $status,
        $payDate,
        $payStart,
        $payEnd,
        $keyword,
        $keywordSearch,
        $user,
        $cityId,
        $company,
        $buildingId,
        $refundStatus,
        $limit = null,
        $offset = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->join('SandboxApiBundle:Shop\Shop', 's', 'WITH', 's.id = o.shopId')
            ->join('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = s.buildingId')
            ->join('SandboxApiBundle:Room\RoomCity', 'c', 'WITH', 'c.id = b.cityId');

        if (!is_null($shopId) && !empty($shopId)) {
            $query->andWhere('o.shopId = :shopId')
                ->setParameter('shopId', $shopId);
        }

        if (!is_null($user) && !empty($user)) {
            $query->andWhere('o.userId = :userId')
                ->setParameter('userId', $user);
        }

        if (!is_null($channel) && !empty($channel)) {
            $query->andWhere('o.payChannel in (:channel)')
                ->setParameter('channel', $channel);
        }

        if (!is_null($status) && !empty($status)) {
            $query->andWhere('o.status IN (:status)')
                ->setParameter('status', $status);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'number':
                    $query->andWhere('o.orderNumber LIKE :search')
                        ->setParameter('search', '%'.$keywordSearch.'%');
                    break;
            }
        }

        if (!is_null($payDate)) {
            $payDateStart = new \DateTime($payDate);
            $payDateEnd = new \DateTime($payDate);
            $payDateEnd->setTime(23, 59, 59);

            $query->andWhere('o.paymentDate >= :payStart')
                ->andWhere('o.paymentDate <= :payEnd')
                ->setParameter('payStart', $payDateStart)
                ->setParameter('payEnd', $payDateEnd);
        } else {
            if (!is_null($payStart) && !empty($payStart)) {
                $payStart = new \DateTime($payStart);
                $query->andWhere('o.paymentDate >= :start')
                    ->setParameter('start', $payStart);
            }

            if (!is_null($payEnd) && !empty($payEnd)) {
                $payEnd = new \DateTime($payEnd);
                $payEnd->setTime(23, 59, 59);
                $query->andWhere('o.paymentDate <= :end')
                    ->setParameter('end', $payEnd);
            }
        }

        if (!is_null($cityId)) {
            $query->andWhere('c.id = :cityId')
                ->setParameter('cityId', $cityId);
        }

        if (!is_null($company)) {
            $query->andWhere('b.company = :company')
                ->setParameter('company', $company);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('b.id = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if ($refundStatus == ProductOrder::REFUNDED_STATUS) {
            $query->andWhere('o.refunded = :refunded')
                ->setParameter('refunded', true)
                ->orderBy('o.modificationDate', 'DESC');
        } elseif ($refundStatus == ProductOrder::NEED_TO_REFUND) {
            $query->andWhere('o.refunded = :refunded')
                ->andWhere('o.needToRefund = :needed')
                ->andWhere('o.status = :refunded')
                ->andWhere('o.unoriginal = :unoriginal')
                ->setParameter('unoriginal', false)
                ->setParameter('refunded', ShopOrder::STATUS_REFUNDED)
                ->setParameter('needed', true)
                ->setParameter('refunded', false)
                ->orderBy('o.modificationDate', 'ASC');
        } else {
            $query->orderBy('o.modificationDate', 'DESC');
        }

        if (!is_null($limit) && !is_null($offset)) {
            $query->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $shopId
     * @param array $channel
     * @param $status
     * @param $payDate
     * @param $payStart
     * @param $payEnd
     * @param $keyword
     * @param $keywordSearch
     * @param $user
     * @param $cityId
     * @param $company
     * @param $buildingId
     * @param $shopId
     * @param $refundStatus
     *
     * @return mixed
     */
    public function countAdminShopOrdersForBackend(
        $shopId,
        $channel,
        $status,
        $payDate,
        $payStart,
        $payEnd,
        $keyword,
        $keywordSearch,
        $user,
        $cityId,
        $company,
        $buildingId,
        $refundStatus
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->join('SandboxApiBundle:Shop\Shop', 's', 'WITH', 's.id = o.shopId')
            ->join('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = s.buildingId')
            ->join('SandboxApiBundle:Room\RoomCity', 'c', 'WITH', 'c.id = b.cityId');

        if (!is_null($shopId) && !empty($shopId)) {
            $query->andWhere('o.shopId = :shopId')
                ->setParameter('shopId', $shopId);
        }

        if (!is_null($user) && !empty($user)) {
            $query->andWhere('o.userId = :userId')
                ->setParameter('userId', $user);
        }

        if (!is_null($channel) && !empty($channel)) {
            $query->andWhere('o.payChannel in (:channel)')
                ->setParameter('channel', $channel);
        }

        if (!is_null($status) && !empty($status)) {
            $query->andWhere('o.status IN (:status)')
                ->setParameter('status', $status);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'number':
                    $query->andWhere('o.orderNumber LIKE :search')
                        ->setParameter('search', '%'.$keywordSearch.'%');
                    break;
            }
        }

        if (!is_null($payDate)) {
            $payDateStart = new \DateTime($payDate);
            $payDateEnd = new \DateTime($payDate);
            $payDateEnd->setTime(23, 59, 59);

            $query->andWhere('o.paymentDate >= :payStart')
                ->andWhere('o.paymentDate <= :payEnd')
                ->setParameter('payStart', $payDateStart)
                ->setParameter('payEnd', $payDateEnd);
        } else {
            if (!is_null($payStart) && !empty($payStart)) {
                $payStart = new \DateTime($payStart);
                $query->andWhere('o.paymentDate >= :start')
                    ->setParameter('start', $payStart);
            }

            if (!is_null($payEnd) && !empty($payEnd)) {
                $payEnd = new \DateTime($payEnd);
                $payEnd->setTime(23, 59, 59);
                $query->andWhere('o.paymentDate <= :end')
                    ->setParameter('end', $payEnd);
            }
        }

        if (!is_null($cityId)) {
            $query->andWhere('c.id = :cityId')
                ->setParameter('cityId', $cityId);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('b.id = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!is_null($company)) {
            $query->andWhere('b.company = :company')
                ->setParameter('company', $company);
        }

        if ($refundStatus == ProductOrder::REFUNDED_STATUS) {
            $query->andWhere('o.refunded = :refunded')
                ->setParameter('refunded', true);
        } elseif ($refundStatus == ProductOrder::NEED_TO_REFUND) {
            $query->andWhere('o.refunded = :refunded')
                ->andWhere('o.needToRefund = :needed')
                ->andWhere('o.status = :refunded')
                ->andWhere('o.unoriginal = :unoriginal')
                ->setParameter('unoriginal', false)
                ->setParameter('refunded', ShopOrder::STATUS_REFUNDED)
                ->setParameter('needed', true)
                ->setParameter('refunded', false);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * get unpaid shop orders.
     */
    public function getUnpaidShopOrders()
    {
        $now = new \DateTime();
        $start = clone $now;
        $start->modify('-5 minutes');

        $query = $this->createQueryBuilder('o')
            ->where('o.status = :unpaid')
            ->andWhere('o.creationDate <= :start')
            ->andWhere('o.unoriginal = :unoriginal')
            ->setParameter('start', $start)
            ->setParameter('unpaid', ShopOrder::STATUS_UNPAID)
            ->setParameter('unoriginal', false)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $orderId
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAdminShopOrderById(
        $orderId
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.id = :orderId')
            ->andWhere('o.status != :unpaid')
            ->andWhere('o.status != :cancelled')
            ->setParameter('unpaid', ShopOrder::STATUS_UNPAID)
            ->setParameter('cancelled', ShopOrder::STATUS_CANCELLED)
            ->setParameter('orderId', $orderId)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param $shopId
     * @param $time
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAdminShopOrdersByTime(
        $shopId,
        $time
    ) {
        $time = new \DateTime($time);

        $query = $this->createQueryBuilder('o')
            ->where('o.status = :paid')
            ->andWhere('o.shopId = :shopId')
            ->andWhere('o.paymentDate >= :time')
            ->setParameter('paid', ShopOrder::STATUS_PAID)
            ->setParameter('time', $time)
            ->setParameter('shopId', $shopId)
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
    public function getUserPendingOrders(
        $userId,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Shop\ShopOrder', 'so', 'WITH', 'so.id = o.linkedOrderId')
            ->where('o.unoriginal = :unoriginal')
            ->andWhere('o.userId = :userId')
            ->andWhere('(
                o.status = :unpaid OR
                o.status = :paid OR
                o.status = :issue OR
                o.status = :ready OR
                (
                    o.status = :waiting AND 
                    o.linkedOrderId IS NOT NULL AND
                    (so.status != :waiting AND so.status != :completed)
                )
            )')
            ->orderBy('o.modificationDate', 'DESC')
            ->setParameter('unpaid', ShopOrder::STATUS_UNPAID)
            ->setParameter('paid', ShopOrder::STATUS_PAID)
            ->setParameter('issue', ShopOrder::STATUS_ISSUE)
            ->setParameter('ready', ShopOrder::STATUS_READY)
            ->setParameter('waiting', ShopOrder::STATUS_TO_BE_REFUNDED)
            ->setParameter('completed', ShopOrder::STATUS_COMPLETED)
            ->setParameter('unoriginal', false)
            ->setParameter('userId', $userId)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
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
    public function getUserCompletedOrders(
        $userId,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.unoriginal = :unoriginal')
            ->andWhere('o.userId = :userId')
            ->andWhere('(
                o.status = :completed OR
                o.status = :cancelled 
            )')
            ->orderBy('o.modificationDate', 'DESC')
            ->setParameter('completed', ShopOrder::STATUS_COMPLETED)
            ->setParameter('cancelled', ShopOrder::STATUS_CANCELLED)
            ->setParameter('unoriginal', false)
            ->setParameter('userId', $userId)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
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
    public function getUserRefundOrders(
        $userId,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Shop\ShopOrder', 'so', 'WITH', 'so.id = o.linkedOrderId')
            ->where('o.unoriginal = :unoriginal')
            ->andWhere('o.userId = :userId')
            ->andWhere('(
                o.status = :refunded OR
                o.status = :waiting
            )')
            ->orderBy('o.modificationDate', 'DESC')
            ->setParameter('waiting', ShopOrder::STATUS_TO_BE_REFUNDED)
            ->setParameter('refunded', ShopOrder::STATUS_REFUNDED)
            ->setParameter('unoriginal', false)
            ->setParameter('userId', $userId)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     * @param null $buildingId
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
            ->join('SandboxApiBundle:Shop\Shop', 's', 'WITH', 's.id = o.shopId')
            ->join('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = s.buildingId')
            ->select('count(o.id) as number , SUM(o.price) as price')
            ->where('o.unoriginal = :unoriginal')
            ->andWhere('o.status = :completed')
            ->andWhere('o.modificationDate >= :start')
            ->andWhere('o.modificationDate <= :end')
            ->setParameter('unoriginal', false)
            ->setParameter('completed', ShopOrder::STATUS_COMPLETED)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $query->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('b.id = :buildingId')
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
            ->join('SandboxApiBundle:Shop\Shop', 's', 'WITH', 's.id = o.shopId')
            ->join('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = s.buildingId')
            ->leftJoin('SandboxApiBundle:Shop\ShopOrder', 'so', 'WITH', 'so.id = o.linkedOrderId')
            ->select('count(o.id) as number , SUM(o.refundAmount) as refundAmount')
            ->where('o.unoriginal = :unoriginal')
            ->andWhere('(
                o.status = :refunded OR
                (
                    o.status = :waiting AND 
                    (so.status = :waiting OR so.status = :completed)
                )
            )')
            ->andWhere('o.modificationDate >= :start')
            ->andWhere('o.modificationDate <= :end')
            ->setParameter('unoriginal', false)
            ->setParameter('waiting', ShopOrder::STATUS_TO_BE_REFUNDED)
            ->setParameter('refunded', ShopOrder::STATUS_REFUNDED)
            ->setParameter('completed', ShopOrder::STATUS_COMPLETED)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $query->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('b.id = :buildingId')
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
     * @param null $limit
     * @param null $offset
     *
     * @return array
     */
    public function getCompletedOrdersList(
        $startDate,
        $endDate,
        $payChannel = null,
        $buildingId = null,
        $companyId = null,
        $limit = null,
        $offset = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->join('SandboxApiBundle:Shop\Shop', 's', 'WITH', 's.id = o.shopId')
            ->join('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = s.buildingId')
            ->where('o.unoriginal = :unoriginal')
            ->andWhere('o.status = :completed')
            ->andWhere('o.modificationDate >= :start')
            ->andWhere('o.modificationDate <= :end')
            ->setParameter('unoriginal', false)
            ->setParameter('completed', ShopOrder::STATUS_COMPLETED)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $query->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('b.id = :buildingId')
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
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     * @param null $buildingId
     *
     * @return int
     */
    public function countCompletedOrdersList(
        $startDate,
        $endDate,
        $payChannel = null,
        $buildingId = null,
        $companyId = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->join('SandboxApiBundle:Shop\Shop', 's', 'WITH', 's.id = o.shopId')
            ->join('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = s.buildingId')
            ->select('COUNT(o)')
            ->where('o.unoriginal = :unoriginal')
            ->andWhere('o.status = :completed')
            ->andWhere('o.modificationDate >= :start')
            ->andWhere('o.modificationDate <= :end')
            ->setParameter('unoriginal', false)
            ->setParameter('completed', ShopOrder::STATUS_COMPLETED)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $query->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('b.id = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!is_null($companyId)) {
            $query->andWhere('b.company = :companyId')
                ->setParameter('companyId', $companyId);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     * @param null $buildingId
     * @param null $limit
     * @param null $offset
     *
     * @return array
     */
    public function getRefundedOrdersList(
        $startDate,
        $endDate,
        $payChannel = null,
        $buildingId = null,
        $companyId = null,
        $limit = null,
        $offset = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->join('SandboxApiBundle:Shop\Shop', 's', 'WITH', 's.id = o.shopId')
            ->join('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = s.buildingId')
            ->leftJoin('SandboxApiBundle:Shop\ShopOrder', 'so', 'WITH', 'so.id = o.linkedOrderId')
            ->where('o.unoriginal = :unoriginal')
            ->andWhere('(
                o.status = :refunded OR
                (
                    o.status = :waiting AND 
                    (so.status = :waiting OR so.status = :completed)
                )
            )')
            ->andWhere('o.modificationDate >= :start')
            ->andWhere('o.modificationDate <= :end')
            ->setParameter('unoriginal', false)
            ->setParameter('waiting', ShopOrder::STATUS_TO_BE_REFUNDED)
            ->setParameter('refunded', ShopOrder::STATUS_REFUNDED)
            ->setParameter('completed', ShopOrder::STATUS_COMPLETED)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $query->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('b.id = :buildingId')
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
     * @param $startDate
     * @param $endDate
     * @param null $payChannel
     * @param null $buildingId
     *
     * @return int
     */
    public function countRefundedOrdersList(
        $startDate,
        $endDate,
        $payChannel = null,
        $buildingId = null,
        $companyId = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->join('SandboxApiBundle:Shop\Shop', 's', 'WITH', 's.id = o.shopId')
            ->join('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = s.buildingId')
            ->leftJoin('SandboxApiBundle:Shop\ShopOrder', 'so', 'WITH', 'so.id = o.linkedOrderId')
            ->select('COUNT(o)')
            ->where('o.unoriginal = :unoriginal')
            ->andWhere('(
                o.status = :refunded OR
                (
                    o.status = :waiting AND 
                    (so.status = :waiting OR so.status = :completed)
                )
            )')
            ->andWhere('o.modificationDate >= :start')
            ->andWhere('o.modificationDate <= :end')
            ->setParameter('unoriginal', false)
            ->setParameter('waiting', ShopOrder::STATUS_TO_BE_REFUNDED)
            ->setParameter('refunded', ShopOrder::STATUS_REFUNDED)
            ->setParameter('completed', ShopOrder::STATUS_COMPLETED)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if (!is_null($payChannel)) {
            $query->andWhere('o.payChannel = :payChannel')
                ->setParameter('payChannel', $payChannel);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('b.id = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!is_null($companyId)) {
            $query->andWhere('b.company = :companyId')
                ->setParameter('companyId', $companyId);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    public function getShopWithOrders(
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Shop\Shop', 's', 'WITH', 's.id = o.shopId')
            ->select('DISTINCT s')
            ->where('o.unoriginal = FALSE')
            ->andWhere('o.paymentDate IS NOT NULL')
            ->andWhere('o.paymentDate >= :start')
            ->andWhere('o.paymentDate <= :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $shop
     * @param $channel
     * @param $startDate
     * @param $endDate
     *
     * @return mixed
     */
    public function getOrderPaidSums(
        $shop,
        $channel,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('SUM(o.price)')
            ->where('o.unoriginal = :unoriginal')
            ->andWhere('o.payChannel = :channel')
            ->andWhere('o.status = :completed')
            ->andWhere('o.shop = :shop')
            ->andWhere('o.paymentDate >= :start')
            ->andWhere('o.paymentDate <= :end')
            ->setParameter('unoriginal', false)
            ->setParameter('shop', $shop)
            ->setParameter('completed', ShopOrder::STATUS_COMPLETED)
            ->setParameter('channel', $channel)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $shop
     * @param $channel
     * @param $startDate
     * @param $endDate
     *
     * @return mixed
     */
    public function getOrderRefundSums(
        $shop,
        $channel,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('SUM(o.refundAmount)')
            ->where('o.unoriginal = :unoriginal')
            ->andWhere('o.payChannel = :channel')
            ->andWhere('o.status = :status')
            ->andWhere('o.refunded = :refunded')
            ->andWhere('o.shop = :shop')
            ->andWhere('o.paymentDate >= :start')
            ->andWhere('o.paymentDate <= :end')
            ->setParameter('unoriginal', false)
            ->setParameter('shop', $shop)
            ->setParameter('status', ShopOrder::STATUS_REFUNDED)
            ->setParameter('refunded', true)
            ->setParameter('channel', $channel)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        return $query->getQuery()->getSingleScalarResult();
    }
}
