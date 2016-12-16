<?php

namespace Sandbox\ApiBundle\Repository\Lease;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;

class LeaseBillRepository extends EntityRepository
{
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
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function findMyBills(
        $user,
        $lease,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('lb')
            ->leftJoin('lb.lease', 'l')
            ->where('lb.status != :status')
            ->andWhere('
                        (l.supervisor = :user OR
                        l.drawee = :user OR 
                        lb.drawee = :user)
                    ')
            ->setParameter('status', LeaseBill::STATUS_PENDING)
            ->setParameter('user', $user);

        if (!is_null($lease)) {
            $query->andWhere('lb.lease = :lease')
                ->setParameter('lease', $lease);
        }

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        $query->orderBy('lb.id', 'DESC');

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

        if (!is_null($status)) {
            $query->andWhere('lb.status = :status')
                ->setParameter('status', $status);
        }

        $query = $query->getQuery();

        return (int) $query->getSingleScalarResult();
    }
}
