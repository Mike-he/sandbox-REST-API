<?php

namespace Sandbox\ApiBundle\Repository\Order;

use Doctrine\ORM\EntityRepository;

class TopUpOrderRepository extends EntityRepository
{
    /**
     * @param $channel
     * @param $payStart
     * @param $payEnd
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getTopUpOrdersForAdmin(
        $channel,
        $payStart,
        $payEnd,
        $search,
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

        if (!is_null($search) && !empty($search)) {
            $query->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = o.userId')
                ->andWhere('o.orderNumber LIKE :search OR up.name LIKE :search')
                ->setParameter('search', "%$search%");
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
     *
     * @return mixed
     */
    public function countTopUpOrdersForAdmin(
        $channel,
        $payStart,
        $payEnd,
        $search
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

        if (!is_null($search) && !empty($search)) {
            $query->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = o.userId')
                ->andWhere('o.orderNumber LIKE :search OR up.name LIKE :search')
                ->setParameter('search', "%$search%");
        }

        return $query->getQuery()->getSingleScalarResult();
    }
}
