<?php

namespace Sandbox\ApiBundle\Repository\Finance;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Finance\FinanceShortRentInvoice;

class FinanceShortRentInvoiceRepository extends EntityRepository
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
    public function countShortRentInvoices(
        $createStart,
        $createEnd,
        $amountStart,
        $amountEnd,
        $status,
        $salesCompanyId
    ) {
        $query = $this->createQueryBuilder('i')
            ->select('COUNT(i)')
            ->where('i.companyId = :companyId')
            ->setParameter('companyId', $salesCompanyId);

        $query = $this->queryForShortRentInvoiceList(
            $query,
            $createStart,
            $createEnd,
            $amountStart,
            $amountEnd,
            $status
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
    public function getShortRentInvoices(
        $createStart,
        $createEnd,
        $amountStart,
        $amountEnd,
        $status,
        $salesCompanyId,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('i')
            ->where('i.companyId = :companyId')
            ->setParameter('companyId', $salesCompanyId);

        $query = $this->queryForShortRentInvoiceList(
            $query,
            $createStart,
            $createEnd,
            $amountStart,
            $amountEnd,
            $status
        );

        $query->orderBy('i.creationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $salesCompanyId
     *
     * @return mixed
     */
    public function sumPendingShortRentInvoices($salesCompanyId)
    {
        $query = $this->createQueryBuilder('i')
            ->select('SUM(i.amount)')
            ->where('i.companyId = :companyId')
            ->andWhere('i.status = :incomplete')
            ->setParameter('companyId', $salesCompanyId)
            ->setParameter('pending', FinanceShortRentInvoice::STATUS_INCOMPLETE);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $salesCompanyId
     * @param $ids
     *
     * @return mixed
     */
    public function getShortRentInvoicesByIds(
        $salesCompanyId,
        $ids
    ) {
        $query = $this->createQueryBuilder('i')
            ->where('i.companyId = :companyId')
            ->andWhere('i.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->setParameter('companyId', $salesCompanyId);

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
    private function queryForShortRentInvoiceList(
        $query,
        $createStart,
        $createEnd,
        $amountStart,
        $amountEnd,
        $status
    ) {
        if (!is_null($createStart) &&
            !empty($createStart) &&
            !is_null($createEnd) &&
            !empty($createEnd)
        ) {
            $createStart = new \DateTime($createStart);
            $createStart->setTime(0, 0, 0);
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);

            $query->andWhere('i.creationDate >= :start')
                ->andWhere('i.creationDate <= :end')
                ->setParameter('start', $createStart)
                ->setParameter('end', $createEnd);
        }

        if (!is_null($amountStart) &&
            !empty($amountStart) &&
            !is_null($amountEnd) &&
            !empty($amountEnd)
        ) {
            $query->andWhere('i.amount >= :amountStart')
                ->andWhere('i.creationDate <= :amountEnd')
                ->setParameter('amountStart', $amountStart)
                ->setParameter('amountEnd', $amountEnd);
        }

        if (!is_null($status) && !empty($status)) {
            $query->andWhere('i.status = :status')
                ->setParameter('status', $status);
        }

        return $query;
    }
}
