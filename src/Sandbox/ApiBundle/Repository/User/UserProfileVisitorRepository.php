<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserProfileVisitorRepository extends EntityRepository
{
    /**
     * @param $userId
     *
     * @return array
     */
    public function findAllMyVisitors(
        $userId
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT DISTINCT upv FROM SandboxApiBundle:User\UserProfileVisitor upv
                  WHERE upv.userId = :userId
                  AND upv.id IN
                  (
                    SELECT MAX(v.id) FROM SandboxApiBundle:User\UserProfileVisitor v
                    GROUP BY v.userId, v.visitorId
                    )
                    ORDER BY upv.creationDate DESC
                '
            )
            ->setParameter('userId', $userId);

        // TODO limit and offset

        return $query->getResult();
    }
}
