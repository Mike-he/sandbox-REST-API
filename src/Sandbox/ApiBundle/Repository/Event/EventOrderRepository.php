<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\Event\EventRegistration;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;

class EventOrderRepository extends EntityRepository
{
    const COMPLETED = "'completed'";
    const CANCELLED = "'cancelled'";

    const FLAG_EVENT = 'event';
    const FLAG_EVENT_REGISTRATION = 'event_registration';

    public function setStatusCancelled()
    {
        $now = new \DateTime();
        $start = clone $now;
        $start->modify('-5 minutes');
        $nowString = (string) $now->format('Y-m-d H:i:s');
        $nowString = "'$nowString'";

        // update event orders status
        $query = $this->createQueryBuilder('o')
            ->update()
            ->set('o.status', self::CANCELLED)
            ->set('o.cancelledDate', $nowString)
            ->set('o.modificationDate', $nowString)
            ->where('o.status = :unpaid')
            ->andWhere('o.creationDate <= :start')
            ->setParameter('unpaid', EventOrder::STATUS_UNPAID)
            ->setParameter('start', $start)
            ->getQuery();

        $query->execute();
    }

    /**
     * @return array
     */
    public function getStatusCompleted()
    {
        $now = new \DateTime();

        $orderQuery = $this->createQueryBuilder('o')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = o.eventId')
            ->where('o.status = :paid')
            ->andWhere('e.eventStartDate <= :now')
            ->setParameter('paid', EventOrder::STATUS_PAID)
            ->setParameter('now', $now);

        return $orderQuery->getQuery()->getResult();
    }

    /**
     * @param $eventId
     * @param $userId
     * @param $status
     *
     * @return mixed
     */
    public function getLastEventOrder(
        $eventId,
        $userId,
        $status = null
    ) {
        $query = $this->createQueryBuilder('eo')
            ->where('eo.eventId = :eventId')
            ->andWhere('eo.userId = :userId')
            ->setParameter('eventId', $eventId)
            ->setParameter('userId', $userId);

        // filter by status
        if (!is_null($status)) {
            $query->andWhere('eo.status = :status')
                ->setParameter('status', $status);
        }

        // set order by
        $query->orderBy('eo.creationDate', 'DESC');

        // set max number
        $query->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $city
     * @param $company
     * @param $building
     * @param array $channel
     * @param $keyword
     * @param $keywordSearch
     * @param $payDate
     * @param $payStart
     * @param $payEnd
     * @param $createDateRange
     * @param $createStart
     * @param $createEnd
     * @param $userId
     *
     * @return array
     */
    public function getEventOrdersForAdmin(
        $city,
        $company,
        $building,
        $channel,
        $keyword,
        $keywordSearch,
        $payDate,
        $payStart,
        $payEnd,
        $createDateRange,
        $createStart,
        $createEnd,
        $userId = null
    ) {
        $query = $this->createQueryBuilder('eo')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = eo.eventId')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = e.buildingId')
            ->where('eo.status != :unpaid')
            ->andWhere('eo.paymentDate IS NOT NULL')
            ->setParameter('unpaid', EventOrder::STATUS_UNPAID);

        // filter by city
        if (!is_null($city)) {
            $query->andWhere('e.city = :city');
            $query->setParameter('city', $city);
        }

        if (!is_null($company)) {
            $query->andWhere('e.salesCompanyId = :company')
                ->setParameter('company', $company);
        }

        if (!is_null($building)) {
            $query->andWhere('e.buildingId = :building')
                ->setParameter('building', $building->getId());
        }

        if (!is_null($channel) && !empty($channel)) {
            $query->andWhere('eo.payChannel in (:channel)')
                ->setParameter('channel', $channel);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'number':
                    $query->andWhere('eo.orderNumber LIKE :search')
                        ->setParameter('search', '%'.$keywordSearch.'%');
                    break;
                case 'event':
                    $query->andWhere('e.name LIKE :search')
                        ->setParameter('search', '%'.$keywordSearch.'%');
                    break;
            }
        }

        //filter by payDate
        if (!is_null($payDate)) {
            $payDateStart = new \DateTime($payDate);
            $payDateEnd = new \DateTime($payDate);
            $payDateEnd->setTime(23, 59, 59);

            $query->andWhere('eo.paymentDate >= :payStart')
                ->andWhere('eo.paymentDate <= :payEnd')
                ->setParameter('payStart', $payDateStart)
                ->setParameter('payEnd', $payDateEnd);
        } else {
            //filter by payStart
            if (!is_null($payStart)) {
                $payStart = new \DateTime($payStart);
                $query->andWhere('eo.paymentDate >= :payStart')
                    ->setParameter('payStart', $payStart);
            }

            //filter by payEnd
            if (!is_null($payEnd)) {
                $payEnd = new \DateTime($payEnd);
                $payEnd->setTime(23, 59, 59);
                $query->andWhere('eo.paymentDate <= :payEnd')
                    ->setParameter('payEnd', $payEnd);
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
            $query->andWhere('e.eventEndDate >= :createStart')
                ->setParameter('createStart', $lastDate);
        } else {
            // filter by order start point
            if (!is_null($createStart)) {
                $createStart = new \DateTime($createStart);
                $createStart->setTime(00, 00, 00);
                $query->andWhere('e.eventEndDate >= :createStart')
                    ->setParameter('createStart', $createStart);
            }

            // filter by order end point
            if (!is_null($createEnd)) {
                $createEnd = new \DateTime($createEnd);
                $createEnd->setTime(23, 59, 59);
                $query->andWhere('e.eventStartDate <= :createEnd')
                    ->setParameter('createEnd', $createEnd);
            }
        }

        if (!is_null($userId)) {
            $query->andWhere('eo.userId = :userId')
                ->setParameter('userId', $userId);
        }

        // order by
        $query->orderBy('eo.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }

    /*********************************** sales api *********************************/

    /**
     * @param $city
     * @param array $channel
     * @param array $status
     * @param array $eventStatus
     * @param $keyword
     * @param $keywordSearch
     * @param $payDate
     * @param $payStart
     * @param $payEnd
     * @param $createDateRange
     * @param $createStart
     * @param $createEnd
     * @param $salesCompanyId
     * @param $userId
     * @param $limit
     * @param $offset
     * @param $sortColumn
     * @param $direction
     *
     * @return array
     */
    public function getEventOrdersForSalesAdmin(
        $city,
        $channel,
        $status,
        $eventStatus,
        $keyword,
        $keywordSearch,
        $payDate,
        $payStart,
        $payEnd,
        $createDateRange,
        $createStart,
        $createEnd,
        $orderCreateStart,
        $orderCreateEnd,
        $salesCompanyId,
        $userId = null,
        $limit = null,
        $offset = null,
        $sortColumn = null,
        $direction = null
    ) {
        $query = $this->createQueryBuilder('eo')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = eo.eventId')
            ->where('eo.status != :unpaid')
            ->andWhere('eo.paymentDate IS NOT NULL')
            ->andWhere('e.salesCompanyId = :salesCompanyId')
            ->setParameter('unpaid', EventOrder::STATUS_UNPAID)
            ->setParameter('salesCompanyId', $salesCompanyId);

        // filter by city
        if (!is_null($city)) {
            $query->andWhere('e.city = :city');
            $query->setParameter('city', $city);
        }

        if (!is_null($channel) && !empty($channel)) {
            if (in_array('sandbox', $channel)) {
                $channel[] = ProductOrder::CHANNEL_ACCOUNT;
                $channel[] = ProductOrder::CHANNEL_ALIPAY;
                $channel[] = ProductOrder::CHANNEL_UNIONPAY;
                $channel[] = ProductOrder::CHANNEL_WECHAT;
                $channel[] = ProductOrder::CHANNEL_WECHAT_PUB;
            }
            $query->andWhere('eo.payChannel in (:channel)')
                ->setParameter('channel', $channel);
        }

        if (!is_null($status) && !empty($status)) {
            $query->andWhere('eo.status in (:status)')
                ->setParameter('status', $status);
        }

        if (!is_null($eventStatus) && !empty($eventStatus)) {
            $query->andWhere('e.status in (:eventStatus)')
                ->setParameter('eventStatus', $eventStatus);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'all':
                    $query ->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'uc.id = eo.customerId')
                            ->andWhere('
                                eo.orderNumber LIKE :search OR
                                e.name LIKE :search OR
                                uc.name LIKE :search OR
                                uc.phone LIKE :search
                            ');
                    break;
                case 'number':
                    $query->andWhere('eo.orderNumber LIKE :search');
                    break;
                case 'event':
                    $query->andWhere('e.name LIKE :search');
                    break;
                default:
                    $query->andWhere('eo.orderNumber LIKE :search');
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        //filter by payDate
        if (!is_null($payDate)) {
            $payDateStart = new \DateTime($payDate);
            $payDateEnd = new \DateTime($payDate);
            $payDateEnd->setTime(23, 59, 59);

            $query->andWhere('eo.paymentDate >= :payStart')
                ->andWhere('eo.paymentDate <= :payEnd')
                ->setParameter('payStart', $payDateStart)
                ->setParameter('payEnd', $payDateEnd);
        } else {
            //filter by payStart
            if (!is_null($payStart)) {
                $payStart = new \DateTime($payStart);
                $query->andWhere('eo.paymentDate >= :payStart')
                    ->setParameter('payStart', $payStart);
            }

            //filter by payEnd
            if (!is_null($payEnd)) {
                $payEnd = new \DateTime($payEnd);
                $payEnd->setTime(23, 59, 59);
                $query->andWhere('eo.paymentDate <= :payEnd')
                    ->setParameter('payEnd', $payEnd);
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
            $query->andWhere('e.eventEndDate >= :createStart')
                ->setParameter('createStart', $lastDate);
        } else {
            // filter by order start point
            if (!is_null($createStart)) {
                $createStart = new \DateTime($createStart);
                $createStart->setTime(00, 00, 00);
                $query->andWhere('e.eventEndDate >= :createStart')
                    ->setParameter('createStart', $createStart);
            }

            // filter by order end point
            if (!is_null($createEnd)) {
                $createEnd = new \DateTime($createEnd);
                $createEnd->setTime(23, 59, 59);
                $query->andWhere('e.eventStartDate <= :createEnd')
                    ->setParameter('createEnd', $createEnd);
            }
        }

        if (!is_null($orderCreateStart)) {
            $orderCreateStart = new \DateTime($orderCreateStart);
            $query->andWhere('eo.creationDate >= :orderCreateStart')
                ->setParameter('orderCreateStart', $orderCreateStart);
        }

        // filter by order end point
        if (!is_null($orderCreateEnd)) {
            $orderCreateEnd = new \DateTime($orderCreateEnd);
            $orderCreateEnd->setTime(23, 59, 59);
            $query->andWhere('eo.creationDate <= :orderCreateEnd')
                ->setParameter('orderCreateEnd', $orderCreateEnd);
        }

        // filter by user
        if (!is_null($userId)) {
            $query->andWhere('eo.userId = :userId')
                ->setParameter('userId', $userId);
        }

        if (!is_null($sortColumn) && !is_null($direction)) {
            $sortArray = [
                'event_start_date' => 'e.eventStartDate',
                'price' => 'eo.price',
                'creation_date' => 'eo.creationDate',
                'payment_date' => 'eo.paymentDate',
            ];
            $direction = strtoupper($direction);
            $query->orderBy($sortArray[$sortColumn], $direction);
        } else {
            $query->orderBy('eo.creationDate', 'DESC');
        }

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $city
     * @param $channel
     * @param $keyword
     * @param $keywordSearch
     * @param $payDate
     * @param $payStart
     * @param $payEnd
     * @param $createDateRange
     * @param $createStart
     * @param $createEnd
     * @param $salesCompanyId
     * @param null $userId
     *
     * @return mixed
     */
    public function countEventOrdersForSalesAdmin(
        $city,
        $channel,
        $keyword,
        $keywordSearch,
        $payDate,
        $payStart,
        $payEnd,
        $createDateRange,
        $createStart,
        $createEnd,
        $orderCreateStart,
        $orderCreateEnd,
        $salesCompanyId,
        $userId = null
    ) {
        $query = $this->createQueryBuilder('eo')
            ->select('count(eo.id)')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = eo.eventId')
            ->where('eo.status != :unpaid')
            ->andWhere('eo.paymentDate IS NOT NULL')
            ->andWhere('e.salesCompanyId = :salesCompanyId')
            ->setParameter('unpaid', EventOrder::STATUS_UNPAID)
            ->setParameter('salesCompanyId', $salesCompanyId);

        // filter by city
        if (!is_null($city)) {
            $query->andWhere('e.city = :city');
            $query->setParameter('city', $city);
        }

        if (!is_null($channel) && !empty($channel)) {
            if (in_array('sandbox', $channel)) {
                $channel[] = ProductOrder::CHANNEL_ACCOUNT;
                $channel[] = ProductOrder::CHANNEL_ALIPAY;
                $channel[] = ProductOrder::CHANNEL_UNIONPAY;
                $channel[] = ProductOrder::CHANNEL_WECHAT;
                $channel[] = ProductOrder::CHANNEL_WECHAT_PUB;
            }
            $query->andWhere('eo.payChannel in (:channel)')
                ->setParameter('channel', $channel);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'number':
                    $query->andWhere('eo.orderNumber LIKE :search')
                        ->setParameter('search', '%'.$keywordSearch.'%');
                    break;
                case 'event':
                    $query->andWhere('e.name LIKE :search')
                        ->setParameter('search', '%'.$keywordSearch.'%');
                    break;
            }
        }

        //filter by payDate
        if (!is_null($payDate)) {
            $payDateStart = new \DateTime($payDate);
            $payDateEnd = new \DateTime($payDate);
            $payDateEnd->setTime(23, 59, 59);

            $query->andWhere('eo.paymentDate >= :payStart')
                ->andWhere('eo.paymentDate <= :payEnd')
                ->setParameter('payStart', $payDateStart)
                ->setParameter('payEnd', $payDateEnd);
        } else {
            //filter by payStart
            if (!is_null($payStart)) {
                $payStart = new \DateTime($payStart);
                $query->andWhere('eo.paymentDate >= :payStart')
                    ->setParameter('payStart', $payStart);
            }

            //filter by payEnd
            if (!is_null($payEnd)) {
                $payEnd = new \DateTime($payEnd);
                $payEnd->setTime(23, 59, 59);
                $query->andWhere('eo.paymentDate <= :payEnd')
                    ->setParameter('payEnd', $payEnd);
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
            $query->andWhere('e.eventEndDate >= :createStart')
                ->setParameter('createStart', $lastDate);
        } else {
            // filter by order start point
            if (!is_null($createStart)) {
                $createStart = new \DateTime($createStart);
                $createStart->setTime(00, 00, 00);
                $query->andWhere('e.eventEndDate >= :createStart')
                    ->setParameter('createStart', $createStart);
            }

            // filter by order end point
            if (!is_null($createEnd)) {
                $createEnd = new \DateTime($createEnd);
                $createEnd->setTime(23, 59, 59);
                $query->andWhere('e.eventStartDate <= :createEnd')
                    ->setParameter('createEnd', $createEnd);
            }
        }

        if (!is_null($orderCreateStart)) {
            $orderCreateStart = new \DateTime($orderCreateStart);
            $query->andWhere('eo.creationDate >= :orderCreateStart')
                ->setParameter('orderCreateStart', $orderCreateStart);
        }

        // filter by order end point
        if (!is_null($orderCreateEnd)) {
            $orderCreateEnd = new \DateTime($orderCreateEnd);
            $orderCreateEnd->setTime(23, 59, 59);
            $query->andWhere('eo.creationDate <= :orderCreateEnd')
                ->setParameter('orderCreateEnd', $orderCreateEnd);
        }

        // filter by user
        if (!is_null($userId)) {
            $query->andWhere('eo.userId = :userId')
                ->setParameter('userId', $userId);
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return $result;
    }

    /**
     * @param $userId
     * @param $status
     * @param $limit
     * @param $offset
     * @param $search
     *
     * @return array
     */
    public function getClientEventOrders(
        $userId,
        $status,
        $limit,
        $offset,
        $search
    ) {
        $query = $this->createQueryBuilder('eo')
            ->leftJoin('eo.event','e')
            ->leftJoin('SandboxApiBundle:Event\EventRegistration', 'er', 'WITH', 'er.eventId = e.id')
            ->where('eo.userId = :userId')
            ->andWhere('er.userId = :userId')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('userId', $userId);

        $now = new \DateTime();

        // filter by status
        if (!is_null($status)) {
            switch ($status) {
                case EventOrder::CLIENT_STATUS_PENDING:
                    $query->andWhere('
                            (
                                eo.status = :unpaid OR
                                (e.verify = TRUE AND er.status = :pending AND eo.status = :paid)
                            )
                        ')
                        ->setParameter('unpaid', EventOrder::STATUS_UNPAID)
                        ->setParameter('paid', EventOrder::STATUS_PAID)
                        ->setParameter('userId', $userId)
                        ->setParameter('pending', EventRegistration::STATUS_PENDING);
                    break;
                case EventOrder::CLIENT_STATUS_IN_PROCESS:
                    $query->andWhere('
                            (
                                eo.status = :unpaid OR
                                (e.verify = TRUE AND er.status = :pending AND eo.status = :paid)
                                (e.verify = TRUE AND (er.status = :accepted OR er.status = :rejected)) OR 
                                (e.verify = FAlSE AND (eo.status = :paid OR eo.status = :completed))
                            )
                        ')
                        ->andWhere('e.eventEndDate >= :now')
                        ->setParameter('now', $now)
                        ->setParameter('rejected', EventRegistration::STATUS_REJECTED)
                        ->setParameter('paid', EventOrder::STATUS_PAID)
                        ->setParameter('userId', $userId)
                        ->setParameter('accepted', EventRegistration::STATUS_ACCEPTED)
                        ->setParameter('completed', EventOrder::STATUS_COMPLETED);
                    break;
                case EventOrder::CLIENT_STATUS_PASSED:
                    $query->andWhere('e.eventEndDate < :now')
                        ->setParameter('now', $now);
                    break;
                default:
                    break;
            }
        }

        // filter by search
        if (!is_null($search)) {
            $query->andWhere('
                    e.name LIKE :search OR
                    eo.orderNumber LIKE :search
                ')
                ->setParameter('search', $search.'%');
        }

        // order by
        $query->orderBy('eo.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $start
     * @param $end
     * @param null $salesCompanyId
     *
     * @return array
     */
    public function getSumEventOrders(
        $start,
        $end,
        $salesCompanyId = null
    ) {
        $query = $this->createQueryBuilder('eo')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'eo.eventId = e.id')
            ->select('(eo.price - eo.serviceFee) as price, e.salesCompanyId')
            ->where('eo.status = :paid OR eo.status = :completed')
            ->andWhere('eo.paymentDate >= :start')
            ->andWhere('eo.paymentDate <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('completed', EventOrder::STATUS_COMPLETED)
            ->setParameter('paid', EventOrder::STATUS_PAID);

        if (!is_null($salesCompanyId)) {
            $query->andWhere('e.salesCompanyId = :companyId')
                ->setParameter('companyId', $salesCompanyId);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $start
     * @param $end
     * @param null $salesCompanyId
     *
     * @return array
     */
    public function getEventOrderSummary(
        $start,
        $end,
        $salesCompanyId = null
    ) {
        $query = $this->createQueryBuilder('eo')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'eo.eventId = e.id')
            ->where('eo.status = :paid OR eo.status = :completed')
            ->andWhere('eo.paymentDate >= :start')
            ->andWhere('eo.paymentDate <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('completed', EventOrder::STATUS_COMPLETED)
            ->setParameter('paid', EventOrder::STATUS_PAID);

        if (!is_null($salesCompanyId)) {
            $query->andWhere('e.salesCompanyId = :companyId')
                ->setParameter('companyId', $salesCompanyId);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    public function getOfficialEventOrders(
        $startDate,
        $endDate
    ) {
        $eventOrderQuery = $this->createQueryBuilder('eo')
            ->where('eo.paymentDate >= :start')
            ->andWhere('eo.paymentDate <= :end')
            ->andWhere('eo.payChannel != :account')
            ->setParameter('account', EventOrder::CHANNEL_ACCOUNT)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        return $eventOrderQuery->getQuery()->getResult();
    }

    public function countValidOrder(
        $userId,
        $now
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = o.eventId')
            ->where('o.status = :paid OR o.status = :completed')
            ->andWhere('o.userId = :userId')
            ->andWhere('e.eventStartDate <= :now')
            ->andWhere('e.eventEndDate >= :now')
            ->setParameter('paid', EventOrder::STATUS_PAID)
            ->setParameter('completed', EventOrder::STATUS_COMPLETED)
            ->setParameter('userId', $userId)
            ->setParameter('now', $now);

        $result = $query->getQuery()->getSingleScalarResult();

        return (int) $result;
    }

    /**
     * @param $createStart
     * @param $createEnd
     * @param $salesCompanyId
     * @param $channel
     * @param $status
     * @param $eventStatus
     * @param $keyword
     * @param $keywordSearch
     * @param $payStart
     * @param $payEnd
     * @param $eventStart
     * @param $eventEnd
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getEventOrdersForPropertyClient(
        $createStart,
        $createEnd,
        $salesCompanyId,
        $channel,
        $status,
        $eventStatus,
        $keyword,
        $keywordSearch,
        $payStart,
        $payEnd,
        $eventStart,
        $eventEnd,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('eo')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = eo.eventId')
            ->where('eo.status != :unpaid')
            ->andWhere('eo.paymentDate IS NOT NULL')
            ->andWhere('e.salesCompanyId = :salesCompanyId')
            ->setParameter('unpaid', EventOrder::STATUS_UNPAID)
            ->setParameter('salesCompanyId', $salesCompanyId);


        if (!is_null($channel) && !empty($channel)) {
            if (in_array('sandbox', $channel)) {
                $channel[] = ProductOrder::CHANNEL_ACCOUNT;
                $channel[] = ProductOrder::CHANNEL_ALIPAY;
                $channel[] = ProductOrder::CHANNEL_UNIONPAY;
                $channel[] = ProductOrder::CHANNEL_WECHAT;
                $channel[] = ProductOrder::CHANNEL_WECHAT_PUB;
            }
            $query->andWhere('eo.payChannel in (:channel)')
                ->setParameter('channel', $channel);
        }

        if (!is_null($status) && !empty($status)) {
            $query->andWhere('eo.status in (:status)')
                ->setParameter('status', $status);
        }

        if (!is_null($eventStatus) && !empty($eventStatus)) {
            $query->andWhere('e.status in (:eventStatus)')
                ->setParameter('eventStatus', $eventStatus);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'all':
                    $query ->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'uc.id = eo.customerId')
                        ->andWhere('
                                eo.orderNumber LIKE :search OR
                                e.name LIKE :search OR
                                uc.name LIKE :search OR
                                uc.phone LIKE :search
                            ');
                    break;
                default:
                    $query->andWhere('eo.orderNumber LIKE :search');
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }


        //filter by payStart
        if (!is_null($payStart)) {
            $payStart = new \DateTime($payStart);
            $query->andWhere('eo.paymentDate >= :payStart')
                ->setParameter('payStart', $payStart);
        }

        //filter by payEnd
        if (!is_null($payEnd)) {
            $payEnd = new \DateTime($payEnd);
            $payEnd->setTime(23, 59, 59);
            $query->andWhere('eo.paymentDate <= :payEnd')
                ->setParameter('payEnd', $payEnd);
        }

        if (!is_null($eventStart)) {
            $eventStart = new \DateTime($eventStart);
            $eventStart->setTime(00, 00, 00);
            $query->andWhere('e.eventEndDate >= :eventStart')
                ->setParameter('eventStart', $eventStart);
        }

        if (!is_null($eventEnd)) {
            $eventEnd = new \DateTime($eventEnd);
            $eventEnd->setTime(23, 59, 59);
            $query->andWhere('e.eventStartDate <= :eventEnd')
                ->setParameter('eventEnd', $eventEnd);
        }

        // filter by order start point
        if (!is_null($createStart)) {
            $query->andWhere('eo.creationDate >= :createStart')
                    ->setParameter('createStart', $createStart);
        }

        // filter by order end point
        if (!is_null($createEnd)) {
            $query->andWhere('eo.creationDate <= :createEnd')
                    ->setParameter('createEnd', $createEnd);
        }

        $query->orderBy('eo.creationDate', 'DESC');

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $createStart
     * @param $createEnd
     * @param $salesCompanyId
     *
     * @return int
     */
    public function countEventOrdersForPropertyClient(
        $createStart,
        $createEnd,
        $salesCompanyId
    ) {
        $query = $this->createQueryBuilder('eo')
            ->select('count(eo.id)')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = eo.eventId')
            ->where('eo.status != :unpaid')
            ->andWhere('eo.paymentDate IS NOT NULL')
            ->andWhere('e.salesCompanyId = :salesCompanyId')
            ->setParameter('unpaid', EventOrder::STATUS_UNPAID)
            ->setParameter('salesCompanyId', $salesCompanyId);

        // filter by order start point
        if (!is_null($createStart)) {
            $query->andWhere('eo.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        // filter by order end point
        if (!is_null($createEnd)) {
            $query->andWhere('eo.creationDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return (int) $result;
    }

    /**
     * @param $userId
     *
     * @return mixed
     */
    public function countCustomerAllEventOrders(
        $userId
    ) {
        $query = $this->createqueryBUilder('eo')
            ->select('count(eo.id)')
            ->where('eo.userId = :userId')
            ->setParameter('userId', $userId);

        return $query->getQuery()->getSingleScalarResult();
    }
}
