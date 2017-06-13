<?php

namespace Sandbox\ApiBundle\Repository\Offline;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Offline\OfflineTransfer;

class OfflineTransferRepository extends EntityRepository
{
    /**
     * @param $type
     * @param $status
     * @param $keyword
     * @param $keywordSearch
     * @param $amountStart
     * @param $amountEnd
     * @param $payStart
     * @param $payEnd
     *
     * @return array
     */
    public function getOfflineTransferForAdmin(
        $type,
        $status,
        $keyword,
        $keywordSearch,
        $amountStart,
        $amountEnd,
        $payStart,
        $payEnd
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('o.orderNumber')
            ->where('o.transferStatus != :unpaid')
            ->setParameter('unpaid', OfflineTransfer::STATUS_UNPAID);

        if (!is_null($type)) {
            $query->andWhere('o.type = :type')
                ->setParameter('type', $type);
        }

        if (!is_null($status)) {
            $query->andWhere('o.transferStatus = :status')
                ->setParameter('status', $status);
        }

        if (!is_null($amountStart)) {
            $query->andWhere('o.price >= :amountStart')
                ->setParameter('amountStart', $amountStart);
        }

        if (!is_null($amountEnd)) {
            $query->andWhere('o.price <= :amountEnd')
                ->setParameter('amountEnd', $amountEnd);
        }

        if (!is_null($payStart)) {
            $payStart = new \DateTime($payStart);
            $query->andWhere('o.creationDate >= :payStart')
                ->setParameter('payStart', $payStart);
        }

        if (!is_null($payEnd)) {
            $payEnd = new \DateTime($payEnd);
            $payEnd->setTime(23, 59, 59);
            $query->andWhere('o.creationDate <= :payEnd')
                ->setParameter('payEnd', $payEnd);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'number':
                    $query->andWhere('o.orderNumber LIKE :search')
                        ->setParameter('search', '%'.$keywordSearch.'%');
                    break;
            }
        }

        $query->groupBy('o.orderNumber');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $status
     * @param $userId
     *
     * @return array
     */
    public function getTopupTransfersForClient(
        $userId,
        $status
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('o.orderNumber')
            ->where('o.userId = :userId')
            ->andWhere('o.type = :topup')
            ->setParameter('userId', $userId)
            ->setParameter('topup', OfflineTransfer::TYPE_TOPUP);

        if (!is_null($status)) {
            $query->andWhere('o.transferStatus IN (:status)')
                ->setParameter('status', $status);
        }

        $query->groupBy('o.orderNumber');

        $query->orderBy('o.orderNumber', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $orderNumber
     *
     * @return array
     */
    public function getAttachments(
        $orderNumber
    ) {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('ota')
            ->from('SandboxApiBundle:Offline\OfflineTransferAttachment', 'ota')
            ->leftJoin('ota.transfer', 't')
            ->where('t.orderNumber = :orderNumber')
            ->setParameter('orderNumber', $orderNumber);

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
