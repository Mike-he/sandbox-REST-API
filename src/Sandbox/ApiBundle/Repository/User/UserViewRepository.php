<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserViewRepository extends EntityRepository
{
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
            u.gender
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
                    u.gender
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
     *
     * @return array
     */
    public function searchUser(
        $banned,
        $authorized,
        $query,
        $sortBy,
        $direction
    ) {
        $queryResults = $this->createQueryBuilder('u')
            ->where('u.name LIKE :query')
            ->orWhere('u.id LIKE :query')
            ->orWhere('u.email LIKE :query')
            ->orWhere('u.phone LIKE :query')
            ->setParameter('query', $query.'%');

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
            ->andWhere('u.authorized = true')
            ->setParameter('query', $query.'%');

        $query->orderBy('up.userId', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }
}
