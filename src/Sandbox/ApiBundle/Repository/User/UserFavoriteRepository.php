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

    /**
     * @param $object
     * @param $id
     *
     * @return int
     */
    public function countFavoritesByObject(
        $object,
        $id
    ) {
        $query = $this->createQueryBuilder('uf')
            ->select('count(uf.id)')
            ->where('1=1');

        if (!is_null($object) && !empty($object)) {
            $query->andWhere('uf.object = :object')
                ->setParameter('object', $object);
        }

        if (!is_null($id) && !empty($id)) {
            $query->andWhere('uf.objectId = :id')
                ->setParameter('id', $id);
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return (int) $result;
    }
}
