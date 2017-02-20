<?php

namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;

/**
 * SalesCompanyWithdrawalsRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SalesCompanyWithdrawalsRepository extends EntityRepository
{
    public function countSalesCompanyWithdrawals(
        $salesCompanyId,
        $createStart,
        $createEnd,
        $successStart,
        $successEnd,
        $amountStart,
        $amountEnd,
        $status
    ) {
        $query = $this->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.id IS NOT NULL');

        $query = $this->setWithdrawalFilter(
            $query,
            $salesCompanyId,
            $createStart,
            $createEnd,
            $successStart,
            $successEnd,
            $amountStart,
            $amountEnd,
            $status
        );

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $salesCompanyId
     * @param $createStart
     * @param $createEnd
     * @param $successStart
     * @param $successEnd
     * @param $amountStart
     * @param $amountEnd
     * @param $status
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getSalesCompanyWithdrawals(
        $salesCompanyId,
        $createStart,
        $createEnd,
        $successStart,
        $successEnd,
        $amountStart,
        $amountEnd,
        $status,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('w')
            ->where('w.id IS NOT NULL');

        $query = $this->setWithdrawalFilter(
            $query,
            $salesCompanyId,
            $createStart,
            $createEnd,
            $successStart,
            $successEnd,
            $amountStart,
            $amountEnd,
            $status
        );

        $query->orderBy('w.creationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $query
     * @param $salesCompanyId
     * @param $createStart
     * @param $createEnd
     * @param $successStart
     * @param $successEnd
     * @param $amountStart
     * @param $amountEnd
     * @param $status
     *
     * @return mixed
     */
    private function setWithdrawalFilter(
        $query,
        $salesCompanyId,
        $createStart,
        $createEnd,
        $successStart,
        $successEnd,
        $amountStart,
        $amountEnd,
        $status
    ) {
        if (!is_null($salesCompanyId) && !empty($salesCompanyId)) {
            $query->andWhere('w.salesCompanyId = :companyId')
                ->setParameter('companyId', $salesCompanyId);
        }

        if (!is_null($createStart) && !empty($createStart)) {
            $createStart = new \DateTime($createStart);
            $createStart->setTime(0, 0, 0);

            $query->andWhere('w.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if (!is_null($createEnd) && !empty($createEnd)) {
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);

            $query->andWhere('w.creationDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        if (!is_null($successStart) && !empty($successStart)) {
            $successStart = new \DateTime($successStart);
            $successStart->setTime(0, 0, 0);

            $query->andWhere('w.successTime IS NOT NULL')
                ->andWhere('w.successTime >= :successStart')
                ->setParameter('successStart', $successStart);
        }

        if (!is_null($successEnd) && !empty($successEnd)) {
            $successEnd = new \DateTime($successEnd);
            $successEnd->setTime(23, 59, 59);

            $query->andWhere('w.successTime IS NOT NULL')
                ->andWhere('w.successTime <= :successEnd')
                ->setParameter('successEnd', $successEnd);
        }

        if (!is_null($amountStart) && !empty($amountStart)) {
            $query->andWhere('w.amount >= :amountStart')
                ->setParameter('amountStart', $amountStart);
        }

        if (!is_null($amountEnd) && !empty($amountEnd)) {
            $query->andWhere('w.amount <= :amountEnd')
                ->setParameter('amountEnd', $amountEnd);
        }

        if (!is_null($status) && !empty($status)) {
            $query->andWhere('w.status = :status')
                ->setParameter('status', $status);
        }

        return $query;
    }

    /**
     * @param $status
     *
     * @return mixed
     */
    public function countPendingSalesCompanyWithdrawals(
        $status
    ) {
        $query = $this->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.status = :status')
            ->setParameter('status', $status);

        return $query->getQuery()->getSingleScalarResult();
    }
}
