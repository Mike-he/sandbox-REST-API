<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\Event\EventRegistration;

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
     * @param $flag
     * @param $startDate
     * @param $endDate
     * @param $channel
     * @param $keyword
     * @param $keywordSearch
     * @param $payDate
     * @param $payStart
     * @param $payEnd
     *
     * @return array
     */
    public function getEventOrdersForAdmin(
        $city,
        $flag,
        $startDate,
        $endDate,
        $channel,
        $keyword,
        $keywordSearch,
        $payDate,
        $payStart,
        $payEnd
    ) {
        $query = $this->createQueryBuilder('eo')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = eo.eventId')
            ->where('eo.status != :unpaid')
            ->andWhere('eo.paymentDate IS NOT NULL')
            ->setParameter('unpaid', EventOrder::STATUS_UNPAID);

        // filter by city
        if (!is_null($city)) {
            $query->andWhere('e.city = :city');
            $query->setParameter('city', $city);
        }

        // filter by start date
        if (!is_null($startDate)) {
            $startDate = new \DateTime($startDate);

            if (self::FLAG_EVENT == $flag) {
                $query->andWhere('e.eventEndDate > :startDate');
            } elseif (self::FLAG_EVENT_REGISTRATION == $flag) {
                $query->andWhere('e.registrationEndDate > :startDate');
            }
            $query->setParameter('startDate', $startDate);
        }

        // filter by end date
        if (!is_null($endDate)) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            if (self::FLAG_EVENT == $flag) {
                $query->andWhere('e.eventStartDate <= :endDate');
            } elseif (self::FLAG_EVENT_REGISTRATION == $flag) {
                $query->andWhere('e.registrationStartDate <= :endDate');
            }
            $query->setParameter('endDate', $endDate);
        }

        if (!is_null($channel)) {
            $query->andWhere('eo.payChannel = :channel')
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

        // order by
        $query->orderBy('eo.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }

    /*********************************** sales api *********************************/

    /**
     * @param $city
     * @param $flag
     * @param $startDate
     * @param $endDate
     * @param $channel
     * @param $keyword
     * @param $keywordSearch
     * @param $payDate
     * @param $payStart
     * @param $payEnd
     * @param $salesCompanyId
     *
     * @return array
     */
    public function getEventOrdersForSalesAdmin(
        $city,
        $flag,
        $startDate,
        $endDate,
        $channel,
        $keyword,
        $keywordSearch,
        $payDate,
        $payStart,
        $payEnd,
        $salesCompanyId
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

        // filter by start date
        if (!is_null($startDate)) {
            $startDate = new \DateTime($startDate);

            if (self::FLAG_EVENT == $flag) {
                $query->andWhere('e.eventEndDate > :startDate');
            } elseif (self::FLAG_EVENT_REGISTRATION == $flag) {
                $query->andWhere('e.registrationEndDate > :startDate');
            }
            $query->setParameter('startDate', $startDate);
        }

        // filter by end date
        if (!is_null($endDate)) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);

            if (self::FLAG_EVENT == $flag) {
                $query->andWhere('e.eventStartDate <= :endDate');
            } elseif (self::FLAG_EVENT_REGISTRATION == $flag) {
                $query->andWhere('e.registrationStartDate <= :endDate');
            }
            $query->setParameter('endDate', $endDate);
        }

        if (!is_null($channel)) {
            $query->andWhere('eo.payChannel = :channel')
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

        // order by
        $query->orderBy('eo.creationDate', 'DESC');

        return $query->getQuery()->getResult();
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
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'eo.eventId = e.id')
            ->leftJoin('SandboxApiBundle:Event\EventRegistration', 'er', 'WITH', 'er.eventId = e.id')
            ->where('eo.userId = :userId')
            ->andWhere('er.userId = :userId')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('userId', $userId);

        // filter by status
        if (!is_null($status)) {
            switch ($status) {
                case EventOrder::CLIENT_STATUS_IN_PROCESS:
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
                case EventOrder::CLIENT_STATUS_PASSED:
                    $query->andWhere('
                            (
                                (e.verify = TRUE AND (er.status = :accepted OR er.status = :rejected)) OR 
                                (e.verify = FAlSE AND (eo.status = :paid OR eo.status = :completed))
                            )
                        ')
                        ->setParameter('rejected', EventRegistration::STATUS_REJECTED)
                        ->setParameter('paid', EventOrder::STATUS_PAID)
                        ->setParameter('userId', $userId)
                        ->setParameter('accepted', EventRegistration::STATUS_ACCEPTED)
                        ->setParameter('completed', EventOrder::STATUS_COMPLETED);
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
}
