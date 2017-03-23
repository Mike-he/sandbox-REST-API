<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Sandbox\ApiBundle\Entity\User\UserView;

class UserViewRepository extends EntityRepository
{
    /**
     * @param $banned
     * @param $sortBy
     * @param $direction
     *
     * @return array
     */
    public function getUsers(
        $banned,
        $sortBy,
        $direction
    ) {
        $query = $this->createQueryBuilder('u')
            ->select('
            u.id,
            u.phone,
            u.email,
            u.banned,
            u.name,
            u.gender,
            u.authorizedPlatform,
            u.authorizedAdminUsername
            ');
        $query->where('u.id > \'0\'');
        if (!is_null($banned)) {
            $query->andwhere('u.banned = :banned');
            $query->setParameter('banned', $banned);
        }
        if (!is_null($sortBy)) {
            $query->orderBy('u.'.$sortBy, $direction);
        }
        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $ids
     *
     * @return array
     */
    public function getUsersByIds(
        $ids
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    SELECT
                    u.id,
                    u.phone,
                    u.email,
                    u.banned,
                    u.name,
                    u.gender,
                    u.cardNo,
                    u.authorizedPlatform,
                    u.authorizedAdminUsername
                    FROM SandboxApiBundle:User\UserView u
                    WHERE u.id IN (:ids)
                '
            )
            ->setParameter('ids', $ids);

        return $query->getResult();
    }

    /**
     * @param $banned
     * @param $authorized
     * @param $sortBy
     * @param $direction
     * @param $offset
     * @param $limit
     * @param $userIds
     * @param $bindCard
     * @param $dateType
     * @param $startDate
     * @param $endDate
     * @param $name
     * @param $phone
     * @param $email
     * @param $id
     * @param $search
     *
     * @return array
     */
    public function searchUser(
        $banned,
        $authorized,
        $sortBy,
        $direction,
        $offset,
        $limit,
        $userIds,
        $bindCard,
        $dateType,
        $startDate,
        $endDate,
        $name,
        $phone,
        $email,
        $id,
        $search
    ) {
        $queryResults = $this->createQueryBuilder('u')
            ->where('u.id IS NOT NULL');

        $queryResults = $this->searchUsers(
            $queryResults,
            $banned,
            $authorized,
            $sortBy,
            $direction,
            $userIds,
            $bindCard,
            $dateType,
            $startDate,
            $endDate,
            $name,
            $phone,
            $email,
            $id,
            $search
        );

        if (!is_null($offset) && !is_null($limit)) {
            $queryResults->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $queryResults->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $queryResults
     * @param $banned
     * @param $authorized
     * @param $sortBy
     * @param $direction
     * @param $userIds
     * @param $bindCard
     * @param $dateType
     * @param $startDate
     * @param $endDate
     * @param $name
     * @param $phone
     * @param $email
     * @param $id
     */
    private function searchUsers(
        $queryResults,
        $banned,
        $authorized,
        $sortBy,
        $direction,
        $userIds,
        $bindCard,
        $dateType,
        $startDate,
        $endDate,
        $name,
        $phone,
        $email,
        $id,
        $search
    ) {
        if (!is_null($search)) {
            $queryResults->andWhere('(
                    u.id LIKE :search
                    OR u.name LIKE :search
                    OR u.phone LIKE :search
                    OR u.email LIKE :search
                    OR u.cardNo LIKE :search
                    OR u.credentialNo LIKE :search
                )')
                ->setParameter('search', $search.'%');
        }

        if (!is_null($name)) {
            $queryResults->andWhere('u.name LIKE :name')
                ->setParameter('name', $name.'%');
        }

        if (!is_null($phone)) {
            $queryResults->andWhere('u.phone LIKE :phone')
                ->setParameter('phone', $phone.'%');
        }

        if (!is_null($email)) {
            $queryResults->andWhere('u.email LIKE :email')
                ->setParameter('email', $email.'%');
        }

        if (!is_null($id)) {
            $queryResults->andWhere('u.id LIKE :id')
                ->setParameter('id', $id.'%');
        }

        if (!is_null($banned)) {
            $queryResults->andWhere('u.banned = :banned')
                ->setParameter('banned', $banned);
        }

        if (!is_null($authorized)) {
            $queryResults->andWhere('u.authorized = :authorized')
                ->setParameter('authorized', $authorized);
        }

        if (!is_null($sortBy)) {
            $queryResults->orderBy('u.'.$sortBy, $direction);
        }

        if (!is_null($userIds)) {
            $queryResults->andWhere('u.id IN (:userIds)')
                ->setParameter('userIds', $userIds);
        }

        if (!is_null($bindCard)) {
            $bindCard = (bool) $bindCard;

            if ($bindCard) {
                $queryResults->andWhere('u.cardNo IS NOT NULL');
            } else {
                $queryResults->andWhere('u.cardNo IS NULL');
            }
        }

        // filter by user registration date
        if (!is_null($dateType) && $dateType == UserView::DATE_TYPE_REGISTRATION) {
            if (!is_null($startDate)) {
                $queryResults->andWhere('u.userRegistrationDate >= :startDate')
                    ->setParameter('startDate', $startDate);
            }

            if (!is_null($endDate)) {
                $queryResults->andWhere('u.userRegistrationDate <= :endDate')
                    ->setParameter('endDate', $endDate);
            }
        }

        return $queryResults;
    }

    /**
     * @param $query
     *
     * @return array
     */
    public function searchUserByPhone(
        $query
    ) {
        $queryResults = $this->createQueryBuilder('u')
            ->where('u.phone LIKE :query')
            ->setParameter('query', $query);

        return $queryResults->getQuery()->getResult();
    }

    /**
     * @param $banned
     * @param $authorized
     * @param $sortBy
     * @param $direction
     * @param $offset
     * @param $limit
     * @param $userIds
     * @param $bindCard
     * @param $dateType
     * @param $startDate
     * @param $endDate
     * @param $name
     * @param $phone
     * @param $email
     * @param $id
     *
     * @return int
     */
    public function countUsers(
        $banned,
        $authorized,
        $sortBy,
        $direction,
        $offset,
        $limit,
        $userIds,
        $bindCard,
        $dateType,
        $startDate,
        $endDate,
        $name,
        $phone,
        $email,
        $id,
        $search
    ) {
        $queryResults = $this->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->where('u.id IS NOT NULL');

        $queryResults = $this->searchUsers(
            $queryResults,
            $banned,
            $authorized,
            $sortBy,
            $direction,
            $userIds,
            $bindCard,
            $dateType,
            $startDate,
            $endDate,
            $name,
            $phone,
            $email,
            $id,
            $search
        );

        return (int) $queryResults->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $query
     * @param int    $limit
     * @param int    $offset
     *
     * @return array
     */
    public function searchMember(
        $query,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('u')
            ->select('up')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = u.id')
            ->where('u.name LIKE :query')
            ->orWhere('u.id LIKE :query')
            ->orWhere('u.email LIKE :query')
            ->orWhere('u.phone LIKE :query')
            ->andWhere('u.banned = false')
            ->setParameter('query', $query.'%');

        $query->orderBy('up.userId', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    //-------------------- sales repository --------------------//

    /**
     * @param $banned
     * @param $sortBy
     * @param $direction
     * @param $userIds
     *
     * @return array
     */
    public function getSalesUsers(
        $banned,
        $sortBy,
        $direction,
        $userIds
    ) {
        $query = $this->createQueryBuilder('u')
            ->select('
            u.id,
            u.phone,
            u.email,
            u.banned,
            u.name,
            u.gender
            ');

        // filter by user ids
        $query->where('u.id IN (:ids)');
        $query->setParameter('ids', $userIds);

        if (!is_null($banned)) {
            $query->andwhere('u.banned = :banned');
            $query->setParameter('banned', $banned);
        }
        if (!is_null($sortBy)) {
            $query->orderBy('u.'.$sortBy, $direction);
        }
        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $banned
     * @param $authorized
     * @param $name
     * @param $sortBy
     * @param $direction
     * @param $userIds
     * @param $offset
     * @param $pageLimit
     * @param $bindCard
     *
     * @return array
     */
    public function searchSalesUser(
        $banned,
        $authorized,
        $name,
        $sortBy,
        $direction,
        $userIds,
        $offset,
        $pageLimit,
        $bindCard,
        $query
    ) {
        $queryResults = $this->createQueryBuilder('u')
            ->where('u.id IS NOT NULL')
            ->setFirstResult($offset)
            ->setMaxResults($pageLimit);

        $queryResults = $this->searchUsersForSales(
            $queryResults,
            $banned,
            $authorized,
            $name,
            $sortBy,
            $direction,
            $userIds,
            $bindCard,
            $query
        );

        return $queryResults->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $queryResults
     * @param $banned
     * @param $authorized
     * @param $name
     * @param $sortBy
     * @param $direction
     * @param $userIds
     * @param $bindCard
     */
    private function searchUsersForSales(
        $queryResults,
        $banned,
        $authorized,
        $name,
        $sortBy,
        $direction,
        $userIds,
        $bindCard,
        $search
    ) {
        if (!is_null($search)) {
            $queryResults->andWhere('(
                    u.id LIKE :search
                    OR u.name LIKE :search
                    OR u.phone LIKE :search
                    OR u.email LIKE :search
                    OR u.cardNo LIKE :search
                    OR u.credentialNo LIKE :search
                )')
                ->setParameter('search', $search.'%');
        }

        // filters by query
        if (is_null($name)) {
            $queryResults->andWhere('u.id IN (:ids)');
            $queryResults->setParameter('ids', $userIds);
        } else {
            $queryResults->andWhere('u.name = :name')
                ->setParameter('name', $name);
        }

        if (!is_null($banned)) {
            $queryResults->andWhere('u.banned = :banned')
                ->setParameter('banned', $banned);
        }

        if (!is_null($authorized)) {
            $queryResults->andWhere('u.authorized = :authorized')
                ->setParameter('authorized', $authorized);
        }

        if (!is_null($sortBy)) {
            $queryResults->orderBy('u.'.$sortBy, $direction);
        }

        if (!is_null($bindCard)) {
            $bindCard = (bool) $bindCard;

            if ($bindCard) {
                $queryResults->andWhere('u.cardNo IS NOT NULL');
            } else {
                $queryResults->andWhere('u.cardNo IS NULL');
            }
        }

        return $queryResults;
    }

    /**
     * @param $banned
     * @param $authorized
     * @param $name
     * @param $sortBy
     * @param $direction
     * @param $userIds
     * @param $offset
     * @param $pageLimit
     * @param $bindCard
     *
     * @return int
     */
    public function countSalesUsers(
        $banned,
        $authorized,
        $name,
        $sortBy,
        $direction,
        $userIds,
        $offset,
        $pageLimit,
        $bindCard,
        $query
    ) {
        $queryResults = $this->createQueryBuilder('u')
            ->select('COUNT(u)');

        $queryResults = $this->searchUsersForSales(
            $queryResults,
            $banned,
            $authorized,
            $name,
            $sortBy,
            $direction,
            $userIds,
            $bindCard,
            $query
        );

        return (int) $queryResults->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     */
    public function countTotalUsers()
    {
        $queryResults = $this->createQueryBuilder('u')
            ->select('COUNT(u)');

        return (int) $queryResults->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return int
     */
    public function countRegUsers(
        $startDate,
        $endDate
    ) {
        $queryResults = $this->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->where('u.userRegistrationDate >= :start')
            ->andWhere('u.userRegistrationDate <= :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        return (int) $queryResults->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $query
     *
     * @return array
     */
    public function searchUserIds(
        $query
    ) {
        $queryResults = $this->createQueryBuilder('u')
            ->select('u.id')
            ->where('u.name LIKE :query')
            ->orWhere('u.email LIKE :query')
            ->orWhere('u.phone LIKE :query')
            ->setParameter('query', '%'.$query.'%');

        return $queryResults->getQuery()->getResult();
    }

    /**
     * @param $ids
     * @param $search
     *
     * @return array
     */
    public function searchUserInfo(
        $ids,
        $search
    ) {
        $query = $this->createQueryBuilder('u')
            ->where('u.id in (:ids)')
            ->setParameter('ids', $ids);

        if (!is_null($search)) {
            $query->andWhere('u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search')
                ->setParameter('search', $search.'%');
        }
        $query = $query->getQuery();

        return $query->getResult();
    }

    /**
     * @param $name
     * @param $account
     *
     * @return array
     */
    public function getUserIds(
        $name,
        $account
    ) {
        $query = $this->createQueryBuilder('u')
            ->select('u.id')
            ->where('1=1');

        if (!is_null($name)) {
            $query->andWhere('u.name LIKE :name')
                ->setParameter('name', '%'.$name.'%');
        }

        if (!is_null($account)) {
            $query->andWhere('(u.email LIKE :account OR u.phone LIKE :account)')
                ->setParameter('account', '%'.$account.'%');
        }

        return $query->getQuery()->getResult();
    }
}
