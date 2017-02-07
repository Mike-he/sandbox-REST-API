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
     * @param $amountStart
     * @param $amountEnd
     *
     * @return array
     */
    public function getBillLists(
        $company,
        $status,
        $createStart,
        $createEnd,
        $amountStart,
        $amountEnd
    ) {
        $query = $this->createQueryBuilder('b')
            ->where('b.companyId = :company')
            ->setParameter('company', $company);

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

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
