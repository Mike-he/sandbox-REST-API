<?php

namespace Sandbox\ApiBundle\Repository\Invoice;

use Doctrine\ORM\EntityRepository;

class TradeInvoiceViewRepository extends EntityRepository
{
    /**
     * @param $userId
     * @param null $limit
     * @param null $offset
     *
     * @return array
     */
    public function getNeedToInvoiceTradeNumbers(
        $userId,
        $limit = null,
        $offset = null
    ) {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('ti.number')
            ->from('SandboxApiBundle:Invoice\TradeInvoiceView', 'ti')
            ->where('ti.userId = :userId')
            ->andWhere('ti.salesInvoice = TRUE')
            ->setParameter('userId', $userId)
            ->orderBy('ti.creationDate', 'DESC');

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        $tradeNumbers = $query->getQuery()->getResult();
        $tradeNumbers = array_map('current', $tradeNumbers);

        return $tradeNumbers;
    }

    /**
     * @param $tradeNumber
     * @param $salesInvoice
     *
     * @return array
     */
    public function getAdminTradeNumbers(
        $tradeNumber,
        $salesInvoice
    ) {
        $query = $this->createQueryBuilder('t')
            ->select('t.number')
            ->where('t.number IS NOT NULL');

        if (!is_null($tradeNumber)) {
            $query->andWhere('t.number LIKE :number')
                ->setParameter('number', '%'.$tradeNumber);
        }

        if (!is_null($salesInvoice)) {
            $query->andWhere('t.salesInvoice = :salesInvoice')
                ->setParameter('salesInvoice', $salesInvoice);
        }

        $tradeNumbers = $query->getQuery()->getResult();
        $tradeNumbers = array_map('current', $tradeNumbers);

        return $tradeNumbers;
    }
}
