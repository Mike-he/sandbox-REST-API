<?php

namespace Sandbox\ApiBundle\Repository\Finance;

use Doctrine\ORM\EntityRepository;

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
        $query = $this->createQueryBuilder('wf')
            ->select('SUM(wf.changeAmount)')
            ->where('wf.companyId = :companyId')
            ->andWhere('wf.creationDate >= :startDate')
            ->andWhere('wf.creationDate <= :endDate')
            ->setParameter('companyId', $salesCompanyId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return (float) $query->getQuery()->getSingleScalarResult();
    }
}
