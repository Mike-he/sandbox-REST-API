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
        $createEnd
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
            $query->andWhere('b.company = :company')
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

        // order by
        $query->orderBy('eo.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }

    /*********************************** sales api *********************************/

    /**
     * @param $city
     * @param array $channel
     * @param $keyword
     * @param $keywordSearch
     * @param $payDate
     * @param $payStart
     * @param $payEnd
     * @param $createDateRange
     * @param $createStart
     * @param $createEnd
     * @param $salesCompanyId
     *
     * @return array
     */
    public function getEventOrdersForSalesAdmin(
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
}
