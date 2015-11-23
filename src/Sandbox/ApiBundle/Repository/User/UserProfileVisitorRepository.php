<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserProfileVisitorRepository extends EntityRepository
{
    /**
     * @param int   $myUserId
     * @param int   $limit
     * @param float $offset
     * @param int   $lastId
     *
     * @return array
     */
    public function findAllMyVisitors(
        $myUserId,
        $limit,
        $offset,
        $lastId
    ) {
        $queryStr =
                '
                  SELECT DISTINCT upv FROM SandboxApiBundle:User\UserProfileVisitor upv
                  LEFT JOIN SandboxApiBundle:User\User u
                  WITH u.id = upv.visitorId
                  WHERE upv.userId = :myUserId
                  AND u.banned = FALSE
                  AND upv.id IN
                  (
                    SELECT MAX(v.id) FROM SandboxApiBundle:User\UserProfileVisitor v
                    GROUP BY v.userId, v.visitorId
                    )
                ';

        if ($lastId > 0) {
            $queryStr = $queryStr.' AND upv.id < :lastId';
        }

        $queryStr = $queryStr.' ORDER BY upv.creationDate DESC';

        $query = $this->getEntityManager()
            ->createQuery($queryStr);

        $query->setParameter('myUserId', $myUserId);

        if ($lastId > 0) {
            $query->setParameter('lastId', $lastId);
        }

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }
}
