<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserPortfolioRepository extends EntityRepository
{
    public function deleteUserPortfoliosByIds(
        $ids
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:User\UserPortfolio up
                    WHERE
                    up.id IN (:ids)
                ')
            ->setParameter('ids', $ids);

        $query->execute();
    }
}
