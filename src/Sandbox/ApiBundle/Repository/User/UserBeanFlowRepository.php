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

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param null $type
     *
     * @return mixed
     */
    public function sumBeans(
        $startDate,
        $endDate,
        $type = null
    ) {
        $query = $this->createQueryBuilder('ubf')
            ->select('sum(ubf.changeAmount)')
            ->where('ubf.creationDate >= :startDate')
            ->andWhere('ubf.creationDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($type) {
            $query->andWhere('ubf.type = :type')
                ->setParameter('type', $type);
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return $result;
    }
}
