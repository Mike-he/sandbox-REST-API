<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventOrder;

class EventOrderRepository extends EntityRepository
{
    const COMPLETED = "'completed'";
    const CANCELLED = "'cancelled'";

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
}
