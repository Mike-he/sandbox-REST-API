<?php

namespace Sandbox\ApiBundle\Repository\Service;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Service\ServiceOrder;

class ServiceOrderRepository extends EntityRepository
{
    /**
     * @param $userId
     * @param $numbers
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getInvoiceServiceOrders(
        $userId,
        $numbers = null,
        $limit = null,
        $offset = null
    ) {
        $query = $this->createQueryBuilder('so')
            ->where('so.status = :completed')
            ->andWhere('so.price > 0')
            ->andWhere('so.userId = :userId')
            ->andWhere('so.invoiced = FALSE')
            ->orderBy('so.paymentDate', 'DESC')
            ->setParameter('userId', $userId)
            ->setParameter('completed', ServiceOrder::STATUS_COMPLETED);

        if (!is_null($numbers)) {
            $query->andWhere('so.orderNumber IN (:numbers)')
                ->setParameter('numbers', $numbers);
        }

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     *
     * @return mixed
     */
    public function getInvoiceServiceOrdersAmount(
        $userId
    ) {
        $query = $this->createQueryBuilder('so')
            ->select('SUM(so.price)')
            ->where('so.status = :completed')
            ->andWhere('so.price > 0')
            ->andWhere('so.userId = :userId')
            ->andWhere('so.invoiced = FALSE')
            ->setParameter('userId', $userId)
            ->setParameter('completed', ServiceOrder::STATUS_COMPLETED);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $companyId
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getServiceOrders(
        $companyId,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('so')
            ->where('so.companyId = :companyId')
            ->setParameter('companyId', $companyId);

        $query->orderBy('so.id', 'DESC');

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $companyId
     *
     * @return mixed
     */
    public function countServiceOrders(
        $companyId
    ) {
        $query = $this->createQueryBuilder('so')
            ->select('count(so.id)')
            ->where('so.companyId = :companyId')
            ->setParameter('companyId', $companyId);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $userId
     * @param $serviceId
     * @return array
     */
    public function getUserLastOrder(
        $userId,
        $serviceId
    ) {
        $query = $this->createQueryBuilder('so')
            ->where('so.userId = :userId')
            ->andWhere('so.serviceId = :serviceId')
            ->andWhere('so.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('serviceId', $serviceId)
            ->setParameter('status', ServiceOrder::STATUS_PAID);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $serviceId
     * @param $companyId
     * @param $limit
     * @param $offset
     * @return array
     */
    public function findPurchaseOrders(
        $serviceId,
        $companyId,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('so')
            ->where('so.serviceId = :serviceId')
            ->andWhere('so.companyId = :companyId')
            ->andWhere('so.status != :status')
            ->setParameter('companyId', $companyId)
            ->setParameter('serviceId', $serviceId)
            ->setParameter('status', ServiceOrder::STATUS_UNPAID);

        $query->orderBy('so.id','DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $status
     * @param $limit
     * @param $offset
     * @return array
     */
    public function findClientServiceOrders(
        $userId,
        $status,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('so')
            ->where('so.userId = :userId')
            ->setParameter('userId', $userId);

        if (!is_null($status)) {
            $query->andWhere('so.status = :status')
                ->setParameter('status', $status);
        }

        $query->orderBy('so.creationDate', 'DESC');

        $query->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $serviceId
     * @return mixed
     */
    public function getServicePurchaseCount(
        $serviceId
    ) {
        $query = $this->createQueryBuilder('so')
            ->select('count(so.id)')
            ->where('so.serviceId = :serviceId')
            ->andWhere('so.status != :status')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('status', ServiceOrder::STATUS_UNPAID);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $firstDate
     * @param $lastDate
     *
     * @return array
     */
    public function getServiceOrdersByDate(
        $firstDate,
        $lastDate
    ) {
        $query = $this->createQueryBuilder('so')
            ->where('so.status = :paid OR so.status = :completed')
            ->andWhere('so.paymentDate >= :start')
            ->andWhere('so.paymentDate <= :end')
            ->setParameter('start', $firstDate)
            ->setParameter('end', $lastDate)
            ->setParameter('paid', ServiceOrder::STATUS_PAID)
            ->setParameter('completed', ServiceOrder::STATUS_COMPLETED);

        return $query->getQuery()->getResult();
    }
}
