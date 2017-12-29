<?php

namespace Sandbox\ApiBundle\Repository\Service;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Service\ServiceOrder;

class ServiceOrderRepository extends EntityRepository
{
    /**
     * @param $userId
     * @param $numbers
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getInvoiceServiceOrders(
        $userId,
        $numbers = null,
        $limit = null,
        $offset = null
    ) {
        $query = $this->createQueryBuilder('so')
            ->where('so.status = :completed')
            ->andWhere('so.price > 0')
            ->andWhere('so.userId = :userId')
            ->orderBy('so.paymentDate', 'DESC')
            ->setParameter('userId', $userId)
            ->setParameter('completed', ServiceOrder::STATUS_COMPLETED);

        if (!is_null($numbers)) {
            $query->andWhere('so.orderNumber IN (:numbers)')
                ->setParameter('numbers', $numbers);
        }

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        return $query->getQuery()->getResult();
    }
}
