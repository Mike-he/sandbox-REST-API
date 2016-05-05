<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventOrder;

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
        $start->modify('-15 minutes');
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

    public function setStatusCompleted()
    {
        $now = new \DateTime();

        $orderQuery = $this->createQueryBuilder('o')
            ->select('o.id')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = o.eventId')
            ->where('o.status = :paid')
            ->andWhere('e.registrationEndDate <= :now')
            ->setParameter('paid', EventOrder::STATUS_PAID)
            ->setParameter('now', $now);

        $orders = $orderQuery->getQuery()->getResult();
        $orderIds = array_map('current', $orders);

        $query = $this->createQueryBuilder('o')
            ->update()
            ->set('o.status', self::COMPLETED)
            ->where('o.id IN (:ids)')
            ->setParameter('ids', $orderIds)
            ->getQuery();

        $query->execute();
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
     * @param $search
     *
     * @return array
     */
    public function getEventOrdersForAdmin(
        $city,
        $flag,
        $startDate,
        $endDate,
        $search
    ) {
        $query = $this->createQueryBuilder('eo')
            ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = eo.eventId')
            ->where('eo.status != :unpaid')
            ->andWhere('eo.paymentDate IS NOT NULL')
            ->setParameter('unpaid', EventOrder::STATUS_UNPAID);

        // searching orders
        if (!is_null($search)) {
            $query->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = o.userId');
            $query->andWhere('(o.orderNumber LIKE :search OR up.name LIKE :search)');
            $parameters['search'] = "%$search%";
        }

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

            if (self::FLAG_EVENT == $flag) {
                $query->andWhere('e.eventStartDate <= :endDate');
            } elseif (self::FLAG_EVENT_REGISTRATION == $flag) {
                $query->andWhere('e.registrationStartDate <= :endDate');
            }
            $query->setParameter('endDate', $endDate);
        }

        // order by
        $query->orderBy('eo.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }
}
