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
        $payDate,
        $payStart,
        $payEnd,
        $limit,
        $offset,
        $companyId = null
    ) {
        $query = $this->createQueryBuilder('mo')
            ->where('mo.id is not null');

        // filter by payment channel
        if (!is_null($channel) && !empty($channel)) {
            $query->andWhere('mo.payChannel in (:channel)')
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

        //filter by payDate
        if (!is_null($payDate) && !empty($payDate)) {
            $payDateStart = new \DateTime($payDate);
            $payDateEnd = new \DateTime($payDate);
            $payDateEnd->setTime(23, 59, 59);

            $query->andWhere('mo.paymentDate >= :payStart')
                ->andWhere('mo.paymentDate <= :payEnd')
                ->setParameter('payStart', $payDateStart)
                ->setParameter('payEnd', $payDateEnd);
        } else {
            //filter by payStart
            if (!is_null($payStart)) {
                $payStart = new \DateTime($payStart);
                $query->andWhere('mo.paymentDate >= :payStart')
                    ->setParameter('payStart', $payStart);
            }

            //filter by payEnd
            if (!is_null($payEnd)) {
                $payEnd = new \DateTime($payEnd);
                $payEnd->setTime(23, 59, 59);
                $query->andWhere('mo.paymentDate <= :payEnd')
                    ->setParameter('payEnd', $payEnd);
            }
        }

        $query->orderBy('mo.creationDate', 'DESC');

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

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
        $payDate,
        $payStart,
        $payEnd,
        $companyId = null
    ) {
        $query = $this->createQueryBuilder('mo')
            ->select('COUNT(mo)')
            ->where('mo.id is not null');

        // filter by payment channel
        if (!is_null($channel) && !empty($channel)) {
            $query->andWhere('mo.payChannel in (:channel)')
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

        //filter by payDate
        if (!is_null($payDate) && !empty($payDate)) {
            $payDateStart = new \DateTime($payDate);
            $payDateEnd = new \DateTime($payDate);
            $payDateEnd->setTime(23, 59, 59);

            $query->andWhere('mo.paymentDate >= :payStart')
                ->andWhere('mo.paymentDate <= :payEnd')
                ->setParameter('payStart', $payDateStart)
                ->setParameter('payEnd', $payDateEnd);
        } else {
            //filter by payStart
            if (!is_null($payStart)) {
                $payStart = new \DateTime($payStart);
                $query->andWhere('mo.paymentDate >= :payStart')
                    ->setParameter('payStart', $payStart);
            }

            //filter by payEnd
            if (!is_null($payEnd)) {
                $payEnd = new \DateTime($payEnd);
                $payEnd->setTime(23, 59, 59);
                $query->andWhere('mo.paymentDate <= :payEnd')
                    ->setParameter('payEnd', $payEnd);
            }
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

    /**
     * @param $userId
     * @param $card
     *
     * @return array
     */
    public function getMembershipOrdersByDate(
        $start,
        $end,
        $salesCompanyId = null
    ) {
        $query = $this->createQueryBuilder('mo')
            ->where('mo.paymentDate >= :start')
            ->andWhere('mo.paymentDate <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if (!is_null($salesCompanyId)) {
            $query->leftJoin('SandboxApiBundle:MembershipCard\MembershipCard', 'c', 'WITH', 'mo.card = c.id')
                ->andWhere('c.companyId = :companyId')
                ->setParameter('companyId', $salesCompanyId);
        }

        return $query->getQuery()->getResult();
    }
}
