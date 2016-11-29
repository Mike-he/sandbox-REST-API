<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserFavoriteRepository extends EntityRepository
{
    /**
     * @param $userId
     * @param $object
     * @param $ids
     *
     * @return array
     */
    public function getUserFavoriteList(
        $userId,
        $object,
        $ids
    ) {
        $query = $this->createQueryBuilder('uf')
            ->where('uf.userId = :userId')
            ->setParameter('userId', $userId);

        if (!is_null($object) && !empty($object)) {
            $query->andWhere('uf.object = :object')
                ->setParameter('object', $object);
        }

        if (!is_null($ids) && !empty($ids)) {
            $query->andWhere('uf.objectId IN (:ids)')
                ->setParameter('ids', $ids);
        }

        return $query->getQuery()->getResult();
    }
}
