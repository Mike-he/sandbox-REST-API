<?php

namespace Sandbox\ApiBundle\Repository\Buddy;

use Doctrine\ORM\EntityRepository;

class BuddyRequestRepository extends EntityRepository
{
    /**
     * @param $myUser
     *
     * @return array
     */
    public function getRequestBuddies(
        $myUser
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    SELECT bq
                    FROM SandboxApiBundle:Buddy\BuddyRequest bq
                    LEFT JOIN SandboxApiBundle:User\User u
                    WITH bq.askUserId = u.id
                    WHERE
                     bq.recvUserId = :myUserId
                     AND u.banned = FALSE
                    ORDER BY bq.creationDate DESC
                '
            )
            ->setParameter('myUserId', $myUser->getId());

        return $query->getResult();
    }
}
