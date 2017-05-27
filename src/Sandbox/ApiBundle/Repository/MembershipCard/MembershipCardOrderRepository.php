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
     * @param $buildingId
     * @param $createDateRange
     * @param $createStart
     * @param $createEnd
     * @param $limit
     * @param $offset
     * @param $companyId
     * @param null $cardId
     * @param null $userId
     *
     * @return array
     */
    public function getAdminOrders(
        $channel,
        $keyword,
        $keywordSearch,
        $buildingId,
        $createDateRange,
        $createStart,
        $createEnd,
        $limit,
        $offset,
        $companyId,
        $cardId = null,
        $userId = null
    ) {
        $query = $this->createQueryBuilder('mo')
            ->select('DISTINCT mo')
            ->innerJoin('SandboxApiBundle:MembershipCard\MembershipCard', 'c', 'WITH', 'mo.card = c.id')
            ->leftJoin('SandboxApiBundle:User\UserGroupDoors', 'd', 'WITH', 'd.card = c.id')
            ->leftJoin('SandboxApiBundle:User\UserView', 'u', 'WITH', 'u.id = mo.user')
            ->where('mo.id is not null');

        if (!is_null($cardId)) {
            $query->andWhere('mo.card = :cardId')
                ->setParameter('cardId', $cardId);
        }

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
                case 'card_name':
                    $query->andWhere('c.name LIKE :search');
                    break;
                case 'user':
                    $query->andWhere('u.name LIKE :search');
                    break;
                case 'phone':
                    $query->andWhere('u.phone LIKE :search');
                    break;
                case 'email':
                    $query->andWhere('u.email LIKE :search');
                    break;
                default:
                    $query->andWhere('mo.orderNumber LIKE :search');
            }

            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        if (!is_null($companyId)) {
            $query->andWhere('c.companyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('d.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!is_null($createDateRange)) {
            $now = new \DateTime();
            switch ($createDateRange) {
                case 'last_week':
                    $lastDate = $now->sub(new \DateInterval('P7D'));
                    break;
                case 'last_month':
                    $lastDate = $now->sub(new \DateInterval('P1M'));
                    break;
                default:
                    $lastDate = new \DateTime();
            }
            $query->andWhere('mo.creationDate >= :createStart')
                ->setParameter('createStart', $lastDate);
        } else {
            // filter by order start point
            if (!is_null($createStart)) {
                $createStart = new \DateTime($createStart);
                $createStart->setTime(00, 00, 00);
                $query->andWhere('mo.creationDate >= :createStart')
                    ->setParameter('createStart', $createStart);
            }

            // filter by order end point
            if (!is_null($createEnd)) {
                $createEnd = new \DateTime($createEnd);
                $createEnd->setTime(23, 59, 59);
                $query->andWhere('mo.creationDate <= :createEnd')
                    ->setParameter('createEnd', $createEnd);
            }
        }

        // filter by userId
        if (!is_null($userId)) {
            $query->andWhere('mo.user = :userId')
                ->setParameter('userId', $userId);
        }

        $query->orderBy('mo.creationDate', 'DESC');

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

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
     * @param $keyword
     * @param $keywordSearch
     * @param $buildingId
     * @param $createDateRange
     * @param $createStart
     * @param $createEnd
     * @param $companyId
     * @param null $cardId
     *
     * @return mixed
     */
    public function countAdminOrders(
        $channel,
        $keyword,
        $keywordSearch,
        $buildingId,
        $createDateRange,
        $createStart,
        $createEnd,
        $companyId,
        $cardId = null,
        $userId = null
    ) {
        $query = $this->createQueryBuilder('mo')
            ->innerJoin('SandboxApiBundle:MembershipCard\MembershipCard', 'c', 'WITH', 'mo.card = c.id')
            ->leftJoin('SandboxApiBundle:User\UserGroupDoors', 'd', 'WITH', 'd.card = c.id')
            ->leftJoin('SandboxApiBundle:User\UserView', 'u', 'WITH', 'u.id = mo.user')
            ->select('COUNT(DISTINCT mo)')
            ->where('mo.id is not null');

        if (!is_null($cardId)) {
            $query->andWhere('mo.card = :cardId')
                ->setParameter('cardId', $cardId);
        }

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
                case 'card_name':
                    $query->andWhere('c.name LIKE :search');
                    break;
                case 'user':
                    $query->andWhere('u.name LIKE :search');
                    break;
                case 'phone':
                    $query->andWhere('u.phone LIKE :search');
                    break;
                case 'email':
                    $query->andWhere('u.email LIKE :search');
                    break;
                default:
                    $query->andWhere('mo.orderNumber LIKE :search');
            }

            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        if (!is_null($companyId)) {
            $query->andWhere('c.companyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('d.building = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!is_null($createDateRange)) {
            $now = new \DateTime();
            switch ($createDateRange) {
                case 'last_week':
                    $lastDate = $now->sub(new \DateInterval('P7D'));
                    break;
                case 'last_month':
                    $lastDate = $now->sub(new \DateInterval('P1M'));
                    break;
                default:
                    $lastDate = new \DateTime();
            }
            $query->andWhere('mo.creationDate >= :createStart')
                ->setParameter('createStart', $lastDate);
        } else {
            // filter by order start point
            if (!is_null($createStart)) {
                $createStart = new \DateTime($createStart);
                $createStart->setTime(00, 00, 00);
                $query->andWhere('mo.creationDate >= :createStart')
                    ->setParameter('createStart', $createStart);
            }

            // filter by order end point
            if (!is_null($createEnd)) {
                $createEnd = new \DateTime($createEnd);
                $createEnd->setTime(23, 59, 59);
                $query->andWhere('mo.creationDate <= :createEnd')
                    ->setParameter('createEnd', $createEnd);
            }
        }

        // filter by userId
        if (!is_null($userId)) {
            $query->andWhere('mo.user = :userId')
                ->setParameter('userId', $userId);
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
        $card,
        $date
    ) {
        $query = $this->createQueryBuilder('mo')
            ->where('mo.user = :userId')
            ->andWhere('mo.card = :card')
            ->andWhere('mo.endDate >= :date')
            ->setParameter('userId', $userId)
            ->setParameter('card', $card)
            ->setParameter('date', $date)
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

    /**
     * @param $card
     * @param $start
     * @param $end
     *
     * @return int
     */
    public function countMembershipOrdersByDate(
        $card,
        $start,
        $end
    ) {
        $query = $this->createQueryBuilder('mo')
            ->select('count(mo.id)')
            ->where('mo.card = :card')
            ->andWhere('mo.paymentDate >= :start')
            ->andWhere('mo.paymentDate <= :end')
            ->setParameter('card', $card)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        $result = $query->getQuery()->getSingleScalarResult();

        return (int) $result;
    }
}
