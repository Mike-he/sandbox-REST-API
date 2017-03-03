<?php

namespace Sandbox\ApiBundle\Repository\Order;

use Doctrine\ORM\EntityRepository;

class TopUpOrderRepository extends EntityRepository
{
    /**
     * @param array $channel
     * @param $payStart
     * @param $payEnd
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getTopUpOrdersForAdmin(
        $channel,
        $payDate,
        $payStart,
        $payEnd,
        $keyword,
        $keywordSearch,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->where('o.id IS NOT NULL');

        // filter by payment channel
        if (!is_null($channel) && !empty($channel)) {
            $query->andWhere('o.payChannel in (:channel)')
                ->setParameter('channel', $channel);
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
            if (!is_null($payStart)) {
                $payStart = new \DateTime($payStart);
                $payStart->setTime(0, 0, 0);
                $query->andWhere('o.paymentDate >= :payStart')
                    ->setParameter('payStart', $payStart);
            }

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
            }
        }

        $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('o.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param array $channel
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
        if (!is_null($channel) && !empty($channel)) {
            $query->andWhere('o.payChannel in (:channel)')
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

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    public function getOfficialTopUpOrders(
        $startDate,
        $endDate
    ) {
        $topUpOrdersQuery = $this->createQueryBuilder('to')
            ->where('to.paymentDate >= :start')
            ->andWhere('to.paymentDate <= :end')
            ->andWhere('to.refundToAccount = FALSE')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        return $topUpOrdersQuery->getQuery()->getResult();
    }
}
