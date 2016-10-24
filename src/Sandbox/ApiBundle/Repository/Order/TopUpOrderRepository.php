<?php

namespace Sandbox\ApiBundle\Repository\Order;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Symfony\Component\Validator\Constraints\DateTime;

class TopUpOrderRepository extends EntityRepository
{
    /**
     * @param $channel
     * @param $payStart
     * @param $payEnd
     * @param $limit
     * @param $offset
     * @return array
     */
    public function getTopUpOrdersForAdmin(
        $channel,
        $payStart,
        $payEnd,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.id IS NOT NULL');

        // filter by payment channel
        if (!is_null($channel)) {
            $query->andWhere('o.payChannel = :channel')
                ->setParameter('channel', $channel);
        }

        //filter by payStart
        if (!is_null($payStart)) {
            $payStart = new \DateTime($payStart);
            $payStart->setTime(0, 0, 0);
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

        $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('o.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $channel
     * @param $payStart
     * @param $payEnd
     * @return mixed
     */
    public function countTopUpOrdersForAdmin(
        $channel,
        $payStart,
        $payEnd
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->where('o.id IS NOT NULL');

        // filter by payment channel
        if (!is_null($channel)) {
            $query->andWhere('o.payChannel = :channel')
                ->setParameter('channel', $channel);
        }

        //filter by payStart
        if (!is_null($payStart)) {
            $payStart = new \DateTime($payStart);
            $payStart->setTime(0, 0, 0);
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

        return $query->getQuery()->getSingleScalarResult();
    }
}
