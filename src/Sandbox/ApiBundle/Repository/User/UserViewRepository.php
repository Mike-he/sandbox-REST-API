<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

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
     * @param $query
     * @param $sortBy
     * @param $direction
     * @param $offset
     * @param $limit
     *
     * @return array
     */
    public function searchUser(
        $banned,
        $authorized,
        $query,
        $sortBy,
        $direction,
        $offset,
        $limit
    ) {
        $queryResults = $this->createQueryBuilder('u')
            ->where('u.id LIKE :query')
            ->orWhere('u.name LIKE :query')
            ->orWhere('u.email LIKE :query')
            ->orWhere('u.phone LIKE :query')
            ->orWhere('u.cardNo LIKE :query')
            ->orWhere('u.credentialNo LIKE :query')
            ->setParameter('query', $query.'%');

        if (!is_null($offset) && !is_null($limit)) {
            $queryResults->setFirstResult($offset)
                ->setMaxResults($limit);
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

        return $queryResults->getQuery()->getResult();
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
     * @param $query
     *
     * @return int
     */
    public function countUsers(
        $banned,
        $authorized,
        $query
    ) {
        $queryResults = $this->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->where('u.id LIKE :query')
            ->orWhere('u.name LIKE :query')
            ->orWhere('u.email LIKE :query')
            ->orWhere('u.phone LIKE :query')
            ->orWhere('u.cardNo LIKE :query')
            ->orWhere('u.credentialNo LIKE :query')
            ->setParameter('query', $query.'%');

        if (!is_null($banned)) {
            $queryResults->andWhere('u.banned = :banned')
                ->setParameter('banned', $banned);
        }

        if (!is_null($authorized)) {
            $queryResults->andWhere('u.authorized = :authorized')
                ->setParameter('authorized', $authorized);
        }

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
     * @param $query
     * @param $sortBy
     * @param $direction
     * @param $userIds
     * @param $offset
     * @param $pageLimit
     *
     * @return array
     */
    public function searchSalesUser(
        $banned,
        $authorized,
        $query,
        $sortBy,
        $direction,
        $userIds,
        $offset,
        $pageLimit
    ) {
        $queryResults = $this->createQueryBuilder('u')
            ->setFirstResult($offset)
            ->setMaxResults($pageLimit);

        // filters by query
        if (is_null($query)) {
            $queryResults->where('u.id IN (:ids)');
            $queryResults->setParameter('ids', $userIds);
        } else {
            $queryResults->where('u.name = :query')
                ->orWhere('u.email = :query')
                ->orWhere('u.phone = :query')
                ->orWhere('u.cardNo = :query')
                ->orWhere('u.credentialNo = :query')
                ->setParameter('query', $query);
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

        return $queryResults->getQuery()->getResult();
    }

    /**
     * @param $banned
     * @param $authorized
     * @param $query
     * @param $userIds
     *
     * @return int
     */
    public function countSalesUsers(
        $banned,
        $authorized,
        $query,
        $userIds
    ) {
        $queryResults = $this->createQueryBuilder('u')
            ->select('COUNT(u)');

        // filters by query
        if (is_null($query)) {
            $queryResults->where('u.id IN (:ids)');
            $queryResults->setParameter('ids', $userIds);
        } else {
            $queryResults->where('u.name = :query')
                ->orWhere('u.email = :query')
                ->orWhere('u.phone = :query')
                ->orWhere('u.cardNo = :query')
                ->orWhere('u.credentialNo = :query')
                ->setParameter('query', $query);
        }

        if (!is_null($banned)) {
            $queryResults->andWhere('u.banned = :banned')
                ->setParameter('banned', $banned);
        }

        if (!is_null($authorized)) {
            $queryResults->andWhere('u.authorized = :authorized')
                ->setParameter('authorized', $authorized);
        }

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
}
