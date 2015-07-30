<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;
use JMS\Serializer\Tests\Serializer\Doctrine;
use Sandbox\ApiBundle\Entity\User\UserView;

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
        if(!is_null($banned)){
            $query->andwhere('u.banned = :banned');
            $query->setParameter('banned',$banned);
        }
        if(!is_null($sortBy)){
            $query->orderBy('u.'.$sortBy,$direction);
        }
        $result = $query->getQuery()->getResult();

        return $result;
    }

    public function getUsersByIds(
        $ids
    ){
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
}
