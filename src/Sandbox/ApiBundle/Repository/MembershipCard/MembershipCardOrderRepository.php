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
     * @param $id
     * @param null $companyId
     */
    public function getAdminOrderById(
        $id,
        $companyId = null
    ) {
        $query = $this->createQueryBuilder('mo')
            ->where('mo.id = :id')
            ->setParameter('id', $id);

        if (!is_null($companyId)) {
            $query->leftJoin('SandboxApiBundle:MembershipCard\MembershipCard', 'c', 'WITH', 'mo.card = c.id')
                ->andWhere('c.companyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $channel
     * @param $keyword
     * @param $keywordSearch
     * @param null $companyId
     *
     * @return array
     */
    public function getAdminOrders(
        $channel,
        $keyword,
        $keywordSearch,
        $limit,
        $offset,
        $companyId = null
    ) {
        $query = $this->createQueryBuilder('mo')
            ->where('mo.id is not null');

        if (!is_null($channel)) {
            $query->andWhere('mo.payChannel = :channel')
                ->setParameter('channel', $channel);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'number':
                    $query->andWhere('mo.orderNumber LIKE :search');
                    break;
                default:
                    $query->andWhere('mo.orderNumber LIKE :search');
            }

            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        if (!is_null($companyId)) {
            $query->leftJoin('SandboxApiBundle:MembershipCard\MembershipCard', 'c', 'WITH', 'mo.card = c.id')
                ->andWhere('c.companyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        $query->orderBy('mo.creationDate', 'DESC');

        $query->setMaxResults($limit)
            ->setFirstResult($offset);
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
     * @param $channel
     * @param $orderNumber
     * @param null $companyId
     *
     * @return array
     */
    public function countAdminOrders(
        $channel,
        $keyword,
        $keywordSearch,
        $companyId = null
    ) {
        $query = $this->createQueryBuilder('mo')
            ->select('COUNT(mo)')
            ->where('mo.id is not null');

        if (!is_null($channel)) {
            $query->andWhere('mo.payChannel = :channel')
                ->setParameter('channel', $channel);
        }

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'number':
                    $query->andWhere('mo.orderNumber LIKE :search');
                    break;
                default:
                    $query->andWhere('mo.orderNumber LIKE :search');
            }

            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        if (!is_null($companyId)) {
            $query->leftJoin('SandboxApiBundle:MembershipCard\MembershipCard', 'c', 'WITH', 'mo.card = c.id')
                ->andWhere('c.companyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $userId
     *
     * @return array
     */
    public function getMyValidClientMembershipCards(
        $userId
    ) {
        $query = $this->createQueryBuilder('mo')
            ->select('DISTINCT(mo.card)')
            ->where('mo.user = :userId')
            ->andWhere('mo.startDate <= :now')
            ->andWhere('mo.endDate >= :now')
            ->setParameter('userId', $userId)
            ->setParameter('now', new \DateTime('now'));

        $result = $query->getQuery()->getScalarResult();
        $result = array_map('current', $result);

        return $result;
    }

    /**
     * @param $userId
     * @param $card
     *
     * @return array
     */
    public function getMembershipOrderEndDate(
        $userId,
        $card
    ) {
        $query = $this->createQueryBuilder('mo')
            ->where('mo.user = :userId')
            ->andWhere('mo.card = :card')
            ->setParameter('userId', $userId)
            ->setParameter('card', $card)
            ->setMaxResults(1)
            ->orderBy('mo.endDate', 'DESC');

        return $query->getQuery()->getOneOrNullResult();
    }
}
