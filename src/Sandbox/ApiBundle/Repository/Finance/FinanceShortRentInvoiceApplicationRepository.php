<?php

namespace Sandbox\ApiBundle\Repository\Finance;

use Doctrine\ORM\EntityRepository;

/**
 * FinanceShortRentInvoiceApplicationRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FinanceShortRentInvoiceApplicationRepository extends EntityRepository
{
    /**
     * @param $createStart
     * @param $createEnd
     * @param $amountStart
     * @param $amountEnd
     * @param $status
     * @param $salesCompanyId
     *
     * @return mixed
     */
    public function countShortRentInvoiceApplications(
        $createStart,
        $createEnd,
        $amountStart,
        $amountEnd,
        $status,
        $salesCompanyId
    ) {
        $query = $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->where('a.id IS NOT NULL');

        $query = $this->queryForShortRentInvoiceApplicationList(
            $query,
            $createStart,
            $createEnd,
            $amountStart,
            $amountEnd,
            $status,
            $salesCompanyId
        );

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $createStart
     * @param $createEnd
     * @param $amountStart
     * @param $amountEnd
     * @param $status
     * @param $salesCompanyId
     * @param $limit
     * @param $offset
     *
     * @return mixed
     */
    public function getShortRentInvoiceApplications(
        $createStart,
        $createEnd,
        $amountStart,
        $amountEnd,
        $status,
        $limit,
        $offset,
        $salesCompanyId
    ) {
        $query = $this->createQueryBuilder('a')
            ->where('a.id IS NOT NULL');

        $query = $this->queryForShortRentInvoiceApplicationList(
            $query,
            $createStart,
            $createEnd,
            $amountStart,
            $amountEnd,
            $status,
            $salesCompanyId
        );

        $query->orderBy('a.creationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $query
     * @param $createStart
     * @param $createEnd
     * @param $amountStart
     * @param $amountEnd
     * @param $status
     *
     * @return mixed
     */
    private function queryForShortRentInvoiceApplicationList(
        $query,
        $createStart,
        $createEnd,
        $amountStart,
        $amountEnd,
        $status,
        $salesCompanyId
    ) {
        if (!is_null($salesCompanyId)) {
            $query->andWhere('a.companyId = :companyId')
                ->setParameter('companyId', $salesCompanyId);
        }

        if (!is_null($createStart) &&
            !empty($createStart) &&
            !is_null($createEnd) &&
            !empty($createEnd)
        ) {
            $createStart = new \DateTime($createStart);
            $createStart->setTime(0, 0, 0);
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);

            $query->andWhere('a.creationDate >= :start')
                ->andWhere('a.creationDate <= :end')
                ->setParameter('start', $createStart)
                ->setParameter('end', $createEnd);
        }

        if (!is_null($amountStart) &&
            !empty($amountStart) &&
            !is_null($amountEnd) &&
            !empty($amountEnd)
        ) {
            $query->andWhere('a.amount >= :amountStart')
                ->andWhere('a.amount <= :amountEnd')
                ->setParameter('amountStart', $amountStart)
                ->setParameter('amountEnd', $amountEnd);
        }

        if (!is_null($status) && !empty($status)) {
            $query->andWhere('a.status = :status')
                ->setParameter('status', $status);
        }

        return $query;
    }

    /**
     * @param $status
     *
     * @return mixed
     */
    public function countPendingShortRentInvoiceApplications(
        $status
    ) {
        $query = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.status = :status')
            ->setParameter('status', $status);

        return $query->getQuery()->getSingleScalarResult();
    }
}
