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
            ->setParameter('companyId', $salesCompanyId);

        if (!is_object($startDate)) {
            $startDate = new \DateTime($startDate);
            $startDate->setTime(00, 00, 00);
        }
        $query->andWhere('wf.creationDate >= :createStart')
            ->setParameter('createStart', $startDate);

        if (!is_object($endDate)) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);
        }
        $query->andWhere('wf.creationDate <= :createEnd')
            ->setParameter('createEnd', $endDate);

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
            FinanceSalesWalletFlow::REALTIME_ORDERS_AMOUNT,
            FinanceSalesWalletFlow::REALTIME_SERVICE_ORDERS_AMOUNT,
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
