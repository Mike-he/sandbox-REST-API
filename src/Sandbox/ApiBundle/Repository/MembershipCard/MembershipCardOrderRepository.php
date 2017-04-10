<?php

namespace Sandbox\ApiBundle\Repository\MembershipCard;

use Doctrine\ORM\EntityRepository;

class MembershipCardOrderRepository extends EntityRepository
{
    /**
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    public function getOfficialCardOrders(
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('mo')
            ->where('mo.paymentDate >= :start')
            ->andWhere('mo.paymentDate <= :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     *
     * @return array
     */
    public function getClientMembershipOrder(
        $userId,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('mo')
            ->where('mo.user = :userId')
            ->setParameter('userId', $userId);

        $query->orderBy('mo.creationDate', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     *
     * @return array
     */
    public function getMyValidClientMembershipOrder(
        $userId
    ) {
        $query = $this->createQueryBuilder('mo')
            ->where('mo.user = :userId')
            ->andWhere('mo.startDate <= :now')
            ->andWhere('mo.endDate >= :now')
            ->setParameter('userId', $userId)
            ->setParameter('now', new \DateTime('now'))
            ->orderBy('mo.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }
}
