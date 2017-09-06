<?php

namespace Sandbox\ApiBundle\Repository\Finance;

use Doctrine\ORM\EntityRepository;

class FinanceReceivablesRepository extends EntityRepository
{
    public function getOrderLists(
        $companyId,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('r')
            ->where('r.companyId = :companyId')
            ->setParameter('companyId', $companyId);

        if ($startDate) {
            $startDate = new \DateTime($startDate);
            $query->andWhere('r.creationDate >= :createStart')
                ->setParameter('createStart', $startDate);
        }

        if ($endDate) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);
            $query->andWhere('r.creationDate <= :createEnd')
                ->setParameter('createEnd', $endDate);
        }
        $query->orderBy('r.id', 'DESC');

        return $query->getQuery()->getResult();
    }
}
