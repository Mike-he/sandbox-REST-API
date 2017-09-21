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
     * @param $limit
     * @param $offset
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
        $amountEnd,
        $limit = null,
        $offset = null
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
                    $query->andWhere('sb.serialNumber LIKE :search');
                    break;
                case 'bill':
                    $query->leftJoin('sb.bill', 'b')
                        ->andWhere('b.serialNumber LIKE :search');
                    break;
                case 'lease':
                    $query->leftJoin('sb.bill', 'b')
                        ->leftJoin('b.lease', 'l')
                        ->andWhere('l.serialNumber LIKE :search');
                    break;
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
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

        $query->orderBy('sb.creationDate', 'DESC');

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    public function countServiceBillList(
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
            ->select('count(sb.id)')
            ->where('sb.companyId = :company')
            ->setParameter('company', $company);

        if (!is_null($type)) {
            $query->andWhere('sb.type = :type')
                ->setParameter('type', $type);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'service':
                    $query->andWhere('sb.serialNumber LIKE :search');
                    break;
                case 'bill':
                    $query->leftJoin('sb.bill', 'b')
                        ->andWhere('b.serialNumber LIKE :search');
                    break;
                case 'lease':
                    $query->leftJoin('sb.bill', 'b')
                        ->leftJoin('b.lease', 'l')
                        ->andWhere('l.serialNumber LIKE :search');
                    break;
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
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

        $query->orderBy('sb.creationDate', 'DESC');

        $result = $query->getQuery()->getSingleScalarResult();

        return $result;
    }

    /**
     * @param $company
     *
     * @return mixed
     */
    public function sumAmount(
        $company
    ) {
        $query = $this->createQueryBuilder('sb')
            ->select('SUM(sb.amount)')
            ->where('sb.companyId = :company')
            ->setParameter('company', $company);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $firstDate
     * @param $lastDate
     * @param null $companyId
     *
     * @return array
     */
    public function getServiceBillsByMonth(
        $firstDate,
        $lastDate,
        $companyId = null
    ) {
        $query = $this->createQueryBuilder('sb')
            ->where('sb.creationDate >= :start')
            ->andWhere('sb.creationDate <= :end')
            ->setParameter('start', $firstDate)
            ->setParameter('end', $lastDate);

        if (!is_null($companyId)) {
            $query->andWhere('sb.companyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        return $query->getQuery()->getResult();
    }
}
