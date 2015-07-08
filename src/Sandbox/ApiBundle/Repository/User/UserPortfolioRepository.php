<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserPortfolioRepository extends EntityRepository
{
    /**
     * @param $ids
     * @param $userId
     */
    public function deleteUserPortfolios(
        $ids,
        $userId
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:User\UserPortfolio up
                    WHERE up.userId = (:userId)
                    AND up.id IN (:ids)
                '
            )
            ->setParameter('ids', $ids)
            ->setParameter('userId', $userId);

        $query->execute();
    }
}
