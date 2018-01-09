<?php

namespace Sandbox\ApiBundle\Repository\Expert;

use Doctrine\ORM\EntityRepository;

class ExpertOrderRepository extends EntityRepository
{
    /**
     * @param $expert
     * @param $status
     * @param $limit
     * @param $offset
     * @param null $userId
     *
     * @return array
     */
    public function getLists(
        $expert,
        $status,
        $limit,
        $offset,
        $userId = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('
                 o.id,
                 o.userId as user_id,
                 o.expertId as expert_id,
                 o.orderNumber as order_number,
                 o.status,
                 o.creationDate as creation_date,
                 o.cancelledDate as cancelled_date,
                 o.completedDate as completed_date
            ')
            ->where('o.id > 0');

        if (!is_null($userId)) {
            $query->andWhere('o.userId = :userId')
                ->setParameter('userId', $userId);
        }

        if (!is_null($expert)) {
            $query->andWhere('o.expertId = :expert')
                ->setParameter('expert', $expert);
        }

        if ($status) {
            $query->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
