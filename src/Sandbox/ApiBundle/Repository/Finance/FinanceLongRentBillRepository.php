<?php

namespace Sandbox\ApiBundle\Repository\Finance;

use Doctrine\ORM\EntityRepository;

class FinanceLongRentBillRepository extends EntityRepository
{
    /**
     * @param $company
     * @param $status
     * @param $createStart
     * @param $createEnd
     * @param null $amountStart
     * @param null $amountEnd
     *
     * @return array
     */
    public function getBillLists(
        $company,
        $status,
        $createStart,
        $createEnd,
        $amountStart = null,
        $amountEnd = null
    ) {
        $query = $this->createQueryBuilder('b')
            ->where('1=1');

        if (!is_null($company)) {
            $query->andWhere('b.companyId = :company')
                ->setParameter('company', $company);
        }

        if (!is_null($status)) {
            $query->andWhere('b.status = :status')
                ->setParameter('status', $status);
        }

        if (!is_null($createStart)) {
            $query->andWhere('b.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if (!is_null($createEnd)) {
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);

            $query->andWhere('b.creationDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        if (!is_null($amountStart)) {
            $query->andWhere('b.amount >= :amountStart')
                ->setParameter('amountStart', $amountStart);
        }

        if (!is_null($amountEnd)) {
            $query->andWhere('b.amount <= :amountEnd')
                ->setParameter('amountEnd', $amountEnd);
        }

        $query->orderBy('b.creationDate', 'DESC');

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $company
     * @param $status
     *
     * @return mixed
     */
    public function sumBillAmount(
        $company,
        $status
    ) {
        $query = $this->createQueryBuilder('b')
            ->select('SUM(b.amount)')
            ->where('b.companyId = :company')
            ->andWhere('b.status = :status')
            ->setParameter('company', $company)
            ->setParameter('status', $status);

        return $query->getQuery()->getSingleScalarResult();
    }
}
