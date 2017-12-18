<?php

namespace Sandbox\ApiBundle\Repository\Commnue;

use Doctrine\ORM\EntityRepository;

class CommueUserRepository extends EntityRepository
{
    /**
     * @param $banned
     * @param $authenticated
     *
     * @return array
     */
    public function getAdminCommnueUserIds(
        $banned,
        $authenticated
    ) {
        $query = $this->createQueryBuilder('cu')
            ->select('cu.userId')
            ->where('cu.id IS NOT NULL');

        if (!is_null($banned)) {
            $query->andWhere('cu.isBanned = :banned')
                ->setParameter('banned', $banned);
        }

        if (!is_null($authenticated)) {
            if ($authenticated) {
                $query->andWhere('cu.authTagId IS NOT NULL');
            } else {
                $query->andWhere('cu.authTagId IS NULL');
            }
        }

        $ids = $query->getQuery()->getScalarResult();
        $ids = array_map('current', $ids);

        return $ids;
    }
}
