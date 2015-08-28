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
     *
     * @return array
     */
    public function searchUser(
        $banned,
        $authorized,
        $query
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

        return $queryResults->getQuery()->getResult();
    }
}
