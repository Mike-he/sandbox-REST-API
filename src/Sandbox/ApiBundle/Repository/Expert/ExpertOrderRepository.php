<?php

namespace Sandbox\ApiBundle\Repository\Expert;

use Doctrine\ORM\EntityRepository;

class ExpertOrderRepository extends EntityRepository
{
    public function getLists(
       $expert,
       $status,
       $limit,
       $offset
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('
                 o.id,
                 o.userId as user_id,
                 o.orderNumber as order_number,
                 o.status,
                 o.creationDate as creation_date,
                 o.cancelledDate as cancelled_date,
                 o.completedDate as completed_date
            ')
            ->where('o.expertId = :expert')
            ->setParameter('expert', $expert);

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
