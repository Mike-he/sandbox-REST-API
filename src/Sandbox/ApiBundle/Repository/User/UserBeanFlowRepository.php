<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserBeanFlowRepository extends EntityRepository
{
    public function checkExits(
        $userId,
        $source,
        $tradeId,
        $startDate = null,
        $endDate = null
    ) {
        $query = $this->createQueryBuilder('ubf')
            ->where('ubf.userId = :userId')
            ->andWhere('ubf.source =:source')
            ->setParameter('userId', $userId)
            ->setParameter('source', $source);

        if ($tradeId) {
            $query->andWhere('ubf.tradeId = :tradeId')
                ->setParameter('tradeId', $tradeId);
        }

        if ($startDate) {
            $query->andWhere('ubf.creationDate >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $query->andWhere('ubf.creationDate <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        $result = $query->getQuery()->getOneOrNullResult();

        return $result;
    }
}
