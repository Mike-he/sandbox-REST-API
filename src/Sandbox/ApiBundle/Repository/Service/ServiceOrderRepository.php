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
     * @param $keyword
     * @param $keywordSearch
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getServiceOrders(
        $companyId,
        $keyword,
        $keywordSearch,
        $limit = null,
        $offset = null
    ) {
        $query = $this->createQueryBuilder('so')
            ->where('so.companyId = :companyId')
            ->setParameter('companyId', $companyId);

        if (!is_null($keyword) && !empty($keyword) &&
            !is_null($keywordSearch) && !empty($keywordSearch)
        ) {
            switch ($keyword) {
                case 'order_number':
                    $query->andWhere('so.orderNumber LIKE :keywordSearch');
                    break;
                case 'service_name':
                    $query->leftJoin('so.service', 's')
                        ->andWhere('s.name LIKE :keywordSearch');
                    break;
                default:
                    $query->andWhere('so.orderNumber LIKE :keywordSearch');
            }

            $query->setParameter('keywordSearch', "%$keywordSearch%");
        }

        $query->orderBy('so.id', 'DESC');

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $companyId
     * @param $keyword
     * @param $keywordSearch
     *
     * @return int
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function countServiceOrders(
        $companyId,
        $keyword,
        $keywordSearch
    ) {
        $query = $this->createQueryBuilder('so')
            ->select('count(so.id)')
            ->where('so.companyId = :companyId')
            ->setParameter('companyId', $companyId);

        if (!is_null($keyword) && !empty($keyword) &&
            !is_null($keywordSearch) && !empty($keywordSearch)
        ) {
            switch ($keyword) {
                case 'order_number':
                    $query->andWhere('so.orderNumber LIKE :keywordSearch');
                    break;
                case 'service_name':
                    $query->leftJoin('so.service', 's')
                        ->andWhere('s.name LIKE :keywordSearch');
                    break;
                default:
                    $query->andWhere('so.orderNumber LIKE :keywordSearch');
            }

            $query->setParameter('keywordSearch', "%$keywordSearch%");
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return (int) $result;
    }

    /**
     * @param $userId
     * @param $serviceId
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUserLastOrder(
        $userId,
        $serviceId
    ) {
        $query = $this->createQueryBuilder('so')
            ->where('so.userId = :userId')
            ->andWhere('so.serviceId = :serviceId')
            ->andWhere('so.status != :status')
            ->setParameter('userId', $userId)
            ->setParameter('serviceId', $serviceId)
            ->setParameter('status', ServiceOrder::STATUS_COMPLETED);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $serviceId
     * @param $companyId
     * @param $limit
     * @param $offset
     *
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

        $query->orderBy('so.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $status
     * @param $limit
     * @param $offset
     *
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
     * @param null $client
     * @return int
     */
    public function getServicePurchaseCount(
        $serviceId,
        $client = null
    ) {
        $query = $this->createQueryBuilder('so')
            ->select('count(so.id)')
            ->where('so.serviceId = :serviceId')
            ->setParameter('serviceId', $serviceId);

        if(is_null($client)) {
            $query->andWhere('so.status != :status')
                ->setParameter('status', ServiceOrder::STATUS_UNPAID);
        }

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $firstDate
     * @param $lastDate
     * @param $salesCompanyId
     *
     * @return array
     */
    public function getServiceOrdersByDate(
        $firstDate,
        $lastDate,
        $salesCompanyId = null
    ) {
        $query = $this->createQueryBuilder('so')
            ->where('so.status = :paid OR so.status = :completed')
            ->andWhere('so.paymentDate >= :start')
            ->andWhere('so.paymentDate <= :end')
            ->setParameter('start', $firstDate)
            ->setParameter('end', $lastDate)
            ->setParameter('paid', ServiceOrder::STATUS_PAID)
            ->setParameter('completed', ServiceOrder::STATUS_COMPLETED);

        if (!is_null($salesCompanyId)) {
            $query->andWhere('so.companyId = :companyId')
                ->setParameter('companyId', $salesCompanyId);
        }

        return $query->getQuery()->getResult();
    }
}
