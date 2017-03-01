<?php

namespace Sandbox\ApiBundle\Repository\Lease;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseBillOfflineTransfer;
use Sandbox\ApiBundle\Entity\Order\OrderOfflineTransfer;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;

class LeaseBillRepository extends EntityRepository
{
    /**
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    public function getOfficialAdminBills(
        $startDate,
        $endDate
    ) {
        $leaseBillQuery = $this->createQueryBuilder('b')
            ->where('b.paymentDate >= :start')
            ->andWhere('b.paymentDate <= :end')
            ->andWhere('b.payChannel != :account')
            ->andWhere('b.payChannel != :salesOffline')
            ->setParameter('account', ProductOrder::CHANNEL_ACCOUNT)
            ->setParameter('salesOffline', LeaseBill::CHANNEL_SALES_OFFLINE)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        return $leaseBillQuery->getQuery()->getResult();
    }

    /**
     * @param $start
     * @param $end
     * @param $salesCompanyId
     *
     * @return array
     */
    public function findBillsByDates(
        $start,
        $end,
        $salesCompanyId = null
    ) {
        $query = $this->createQueryBuilder('lb')
            ->where('lb.paymentDate >= :start')
            ->andWhere('lb.paymentDate <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if (!is_null($salesCompanyId)) {
            $query->leftJoin('lb.lease', 'l')
                ->leftJoin('l.product', 'p')
                ->leftJoin('p.room', 'r')
                ->leftJoin('r.building', 'b')
                ->andWhere('b.company = :company')
                ->setParameter('company', $salesCompanyId);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $ids
     *
     * @return array
     */
    public function getBillsNumbers(
        $ids
    ) {
        $query = $this->createQueryBuilder('b')
            ->where('b.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $lease
     * @param $status
     *
     * @return mixed
     */
    public function sumBillsFees(
        $lease,
        $status
    ) {
        $query = $this->createQueryBuilder('b')
            ->select('SUM(b.revisedAmount)')
            ->where('b.lease = :lease')
            ->andWhere('b.status != :status')
            ->setParameter('lease', $lease)
            ->setParameter('status', $status);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $lease
     * @param $status
     *
     * @return array
     */
    public function findBills(
        $lease,
        $status
    ) {
        $query = $this->createQueryBuilder('lb')
            ->where('lb.status in (:status)')
            ->andWhere('lb.lease = :lease')
            ->setParameter('status', $status)
            ->setParameter('lease', $lease);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $user
     * @param $lease
     * @param $type
     * @param $status
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function findMyBills(
        $user,
        $lease,
        $type,
        $status,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('lb')
            ->leftJoin('lb.lease', 'l')
            ->where('
                        (l.supervisor = :user OR
                        l.drawee = :user OR 
                        lb.drawee = :user)
                    ')
            ->setParameter('user', $user);

        if ($type == 'all') {
            $query->andWhere('lb.status != :status')
                ->setParameter('status', LeaseBill::STATUS_PENDING);
        } else {
            $query->andWhere('lb.type = :type')
                ->setParameter('type', $type);
        }

        if (!is_null($status)) {
            $query->andWhere('lb.status = :sta')
                ->setParameter('sta', $status);
        }

        if (!is_null($lease)) {
            $query->andWhere('lb.lease = :lease')
                ->setParameter('lease', $lease);
        }

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        $query->orderBy('lb.sendDate', 'DESC');

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $id
     * @param $user
     *
     * @return array
     */
    public function findOneBill(
        $id,
        $user
    ) {
        $query = $this->createQueryBuilder('lb')
            ->leftJoin('lb.lease', 'l')
            ->where('lb.id = :id')
            ->andWhere('
                        (l.supervisor = :user OR
                        l.drawee = :user OR 
                        lb.drawee = :user)
                    ')
            ->setParameter('id', $id)
            ->setParameter('user', $user);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $lease
     * @param null $type
     * @param null $status
     *
     * @return mixed
     */
    public function countBills(
        $lease,
        $type = null,
        $status = null
    ) {
        $query = $this->createQueryBuilder('lb')
            ->select('count(lb)')
            ->where('lb.lease = :lease')
            ->setParameter('lease', $lease);

        if (!is_null($type)) {
            $query->andWhere('lb.type = :type')
                ->setParameter('type', $type);
        }

        if (!is_null($status) && !empty($status)) {
            $query->andWhere('lb.status in (:status)')
                ->setParameter('status', $status);
        }

        $query = $query->getQuery();

        return (int) $query->getSingleScalarResult();
    }

    /**
     * @param $ids
     * @param $status
     * @param $type
     * @param $lease
     *
     * @return array
     */
    public function findBillsByIds(
        $ids,
        $status,
        $type,
        $lease
    ) {
        $query = $this->createQueryBuilder('lb')
            ->where('lb.lease = :lease')
            ->andWhere('lb.status = :status')
            ->andWhere('lb.id in (:ids)')
            ->andWhere('lb.type = :type')
            ->setParameter('status', $status)
            ->setParameter('type', $type)
            ->setParameter('ids', $ids)
            ->setParameter('lease', $lease);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $company
     * @param $channel
     * @param $status
     * @param $keyword
     * @param $keywordSearch
     * @param $sendStart
     * @param $sendEnd
     * @param $amountStart
     * @param $amountEnd
     *
     * @return array
     */
    public function findBillsByCompany(
        $company,
        $channel,
        $status,
        $keyword,
        $keywordSearch,
        $sendStart,
        $sendEnd,
        $amountStart,
        $amountEnd
    ) {
        $query = $this->createQueryBuilder('lb')
            ->leftJoin('lb.lease', 'l')
            ->leftJoin('SandboxApiBundle:Lease\LeaseBillOfflineTransfer', 't', 'with', 't.bill = lb.id')
            ->leftJoin('SandboxApiBundle:User\UserView', 'u', 'WITH', 'u.id = lb.drawee')
            ->where('1 = 1');

        if (!is_null($company)) {
            $query->leftJoin('l.product', 'p')
                ->leftJoin('p.room', 'r')
                ->leftJoin('r.building', 'b')
                ->andWhere('b.company = :company')
                ->setParameter('company', $company);
        }

        if (!is_null($channel)) {
            $query->andWhere('lb.payChannel = :channel')
                ->setParameter('channel', $channel);
        }

        if (!is_null($status)) {
            if ($status == LeaseBillOfflineTransfer::STATUS_RETURNED || $status == LeaseBillOfflineTransfer::STATUS_PENDING) {
                $query->andWhere('t.transferStatus = :status');
            } else {
                $query->andWhere('lb.status in (:status)');
            }
            $query->setParameter('status', $status);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'lease':
                    $query->andWhere('l.serialNumber LIKE :search');
                    break;
                case 'bill':
                    $query->andWhere('lb.serialNumber LIKE :search');
                    break;
                case 'user':
                    $query->andWhere('u.name LIKE :search');
                    break;
                case 'account':
                    $query->andWhere('
                            (u.phone LIKE :search OR u.email LIKE :search)
                        ');
                    break;
                default:
                    $query->andWhere('l.serialNumber LIKE :search');
            }

            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        if (!is_null($sendStart)) {
            $sendStart = new \DateTime($sendStart);
            $sendStart->setTime(00, 00, 00);
            $query->andWhere('lb.sendDate >= :sendStart')
                ->setParameter('sendStart', $sendStart);
        }

        if (!is_null($sendEnd)) {
            $sendEnd = new \DateTime($sendEnd);
            $sendEnd->setTime(23, 59, 59);
            $query->andWhere('lb.sendDate <= :sendEnd')
                ->setParameter('sendEnd', $sendEnd);
        }

        if (!is_null($amountStart)) {
            $query->andWhere('lb.revisedAmount >= :amountStart')
                ->setParameter('amountStart', $amountStart);
        }

        if (!is_null($amountEnd)) {
            $query->andWhere('lb.revisedAmount <= :amountEnd')
                ->setParameter('amountEnd', $amountEnd);
        }

        $query->orderBy('lb.sendDate', 'DESC');

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $userId
     * @param $ids
     *
     * @return array
     */
    public function getLeaseBillsByIds(
        $userId,
        $ids
    ) {
        $query = $this->createQueryBuilder('b')
            ->where('b.drawee = :userId')
            ->setParameter('userId', $userId);

        if (!is_null($ids) && !empty($ids)) {
            $query->andWhere('b.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @return int
     */
    public function countTransferComfirm()
    {
        $leaseBillComfirmCount = $this->createQueryBuilder('lb')
            ->leftJoin('SandboxApiBundle:Lease\LeaseBillOfflineTransfer', 't', 'with', 't.bill = lb.id')
            ->select('count(lb.id)')
            ->where('t.transferStatus = :status')
            ->andWhere('lb.payChannel = :channel')
            ->setParameter('channel', LeaseBill::CHANNEL_OFFLINE)
            ->setParameter('status', LeaseBillOfflineTransfer::STATUS_PENDING);

        $leaseBillComfirmCount = $leaseBillComfirmCount->getQuery()
            ->getSingleScalarResult();
        $leaseBillComfirmCount = (int) $leaseBillComfirmCount;

        $orderComfirmCount = $this->getEntityManager()->createQueryBuilder()
            ->from('SandboxApiBundle:Order\ProductOrder', 'o')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:Order\OrderOfflineTransfer', 't', 'with', 't.orderId = o.id')
            ->select('COUNT(o.id)')
            ->where('o.payChannel = :channel')
            ->andWhere('
                            (t.transferStatus = :pending) OR
                            (t.transferStatus = :verify)
                        ')
            ->setParameter('pending', OrderOfflineTransfer::STATUS_PENDING)
            ->setParameter('verify', OrderOfflineTransfer::STATUS_VERIFY)
            ->setParameter('channel', ProductOrder::CHANNEL_OFFLINE);

        $orderComfirmCount = $orderComfirmCount->getQuery()
            ->getSingleScalarResult();
        $orderComfirmCount = (int) $orderComfirmCount;

        $totalComfirmCount = $leaseBillComfirmCount + $orderComfirmCount;

        return $totalComfirmCount;
    }

    /**
     * @param $status
     * @param $companyId
     *
     * @return mixed
     */
    public function countBillByCompany(
        $status,
        $companyId
    ) {
        $query = $this->createQueryBuilder('lb')
            ->select('COUNT(lb)')
            ->leftJoin('lb.lease', 'l')
            ->leftJoin('l.product', 'p')
            ->leftJoin('p.room', 'r')
            ->leftJoin('r.building', 'b')
            ->where('b.companyId = :companyId')
            ->andWhere('lb.status = :status')
            ->setParameter('companyId', $companyId)
            ->setParameter('status', $status);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $userId
     *
     * @return mixed
     */
    public function sumInvoiceBillsFees(
        $userId
    ) {
        $query = $this->createQueryBuilder('b')
            ->select('SUM(b.revisedAmount)')
            ->where('b.salesInvoice = TRUE')
            ->andWhere('b.invoiced = FALSE')
            ->andWhere('b.drawee = :userId')
            ->andWhere('b.status = :paid')
            ->setParameter('userId', $userId)
            ->setParameter('paid', LeaseBill::STATUS_PAID);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $company
     * @param $status
     *
     * @return array
     */
    public function findNumbersForSalesInvoice(
        $company,
        $status
    ) {
        $query = $this->createQueryBuilder('lb')
            ->select('lb.serialNumber')
            ->leftJoin('lb.lease', 'l')
            ->leftJoin('l.product', 'p')
            ->leftJoin('p.room', 'r')
            ->leftJoin('r.building', 'b')
            ->where('b.company = :company')
            ->andWhere('lb.salesInvoice = TRUE')
            ->setParameter('company', $company);

        if (!is_null($status)) {
            $query->andWhere('lb.status = :status')
                ->setParameter('status', $status);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
