<?php

namespace Sandbox\ApiBundle\Repository\Finance;

use Doctrine\ORM\EntityRepository;

class FinanceLongRentServiceBillRepository extends EntityRepository
{
    /**
     * @param $company
     * @param $type
     * @param $keyword
     * @param $keywordSearch
     * @param $createStart
     * @param $createEnd
     * @param $amountStart
     * @param $amountEnd
     *
     * @return array
     */
    public function findServiceBillList(
        $company,
        $type,
        $keyword,
        $keywordSearch,
        $createStart,
        $createEnd,
        $amountStart,
        $amountEnd
    ) {
        $query = $this->createQueryBuilder('sb')
            ->where('sb.companyId = :company')
            ->setParameter('company', $company);

        if (!is_null($type)) {
            $query->andWhere('sb.type = :type')
                ->setParameter('type', $type);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'service':
                    $query->andWhere('sb.serialNumber = :number')
                        ->setParameter('number', $keywordSearch);
                    break;
                case 'bill':
                    $query->leftJoin('sb.bill', 'b')
                        ->andWhere('b.serialNumber = :number')
                        ->setParameter('number', $keywordSearch);
                    break;
                case 'lease':
                    $query->leftJoin('sb.bill', 'b')
                        ->leftJoin('b.lease', 'l')
                        ->andWhere('l.serialNumber = :number')
                        ->setParameter('number', $keywordSearch);
                    break;
            }
        }

        if (!is_null($createStart)) {
            $query->andWhere('sb.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if (!is_null($createEnd)) {
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);

            $query->andWhere('sb.creationDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        if (!is_null($amountStart)) {
            $query->andWhere('sb.amount >= :amountStart')
                ->setParameter('amountStart', $amountStart);
        }

        if (!is_null($amountEnd)) {
            $query->andWhere('sb.amount <= :amountEnd')
                ->setParameter('amountEnd', $amountEnd);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
