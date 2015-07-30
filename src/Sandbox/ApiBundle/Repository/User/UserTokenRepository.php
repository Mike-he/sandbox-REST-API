<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserTokenRepository extends EntityRepository
{
    /**
     * @param int $userId
     * @param int $clientId
     */
    public function deleteUserToken(
        $userId,
        $clientId
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:User\UserToken ut
                    WHERE ut.userId = :userId
                    AND ut.clientId = :clientId
                '
            )
            ->setParameter('userId', $userId)
            ->setParameter('clientId', $clientId);

        $query->execute();
    }
}
