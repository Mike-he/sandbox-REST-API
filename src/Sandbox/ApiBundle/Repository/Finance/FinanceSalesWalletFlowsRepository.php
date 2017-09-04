<?php

namespace Sandbox\ApiBundle\Repository\Finance;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Finance\FinanceSalesWalletFlow;

class FinanceSalesWalletFlowsRepository extends EntityRepository
{
    /**
     * @param $salesCompanyId
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    public function getAdminWalletFlows(
        $salesCompanyId,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('wf')
            ->where('wf.companyId = :companyId')
            ->andWhere('wf.creationDate >= :startDate')
            ->andWhere('wf.creationDate <= :endDate')
            ->setParameter('companyId', $salesCompanyId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        $query->orderBy('wf.id', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $salesCompanyId
     * @param $startDate
     * @param $endDate
     *
     * @return float
     */
    public function getAdminWalletInput(
        $salesCompanyId,
        $startDate,
        $endDate
    ) {
        $inputTypes = [
            FinanceSalesWalletFlow::MONTHLY_ORDER_AMOUNT,
            FinanceSalesWalletFlow::REALTIME_BILLS_AMOUNT,
        ];

        $query = $this->createQueryBuilder('wf')
            ->select('SUM(wf.changeAmount)')
            ->where('wf.companyId = :companyId')
            ->andWhere('wf.creationDate >= :startDate')
            ->andWhere('wf.creationDate <= :endDate')
            ->andWhere('wf.title IN (:types)')
            ->setParameter('companyId', $salesCompanyId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('types', $inputTypes);

        return (float) $query->getQuery()->getSingleScalarResult();
    }
}
