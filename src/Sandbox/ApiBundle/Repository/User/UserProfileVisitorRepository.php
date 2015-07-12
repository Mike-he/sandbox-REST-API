<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserProfileVisitorRepository extends EntityRepository
{
    /**
     * @param int   $myUserId
     * @param int   $limit
     * @param float $offset
     *
     * @return array
     */
    public function findAllMyVisitors(
        $myUserId,
        $limit,
        $offset
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT DISTINCT upv FROM SandboxApiBundle:User\UserProfileVisitor upv
                  WHERE upv.userId = :myUserId
                  AND upv.id IN
                  (
                    SELECT MAX(v.id) FROM SandboxApiBundle:User\UserProfileVisitor v
                    GROUP BY v.userId, v.visitorId
                    )
                    ORDER BY upv.creationDate DESC
                '
            )
            ->setParameter('myUserId', $myUserId);

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }
}
