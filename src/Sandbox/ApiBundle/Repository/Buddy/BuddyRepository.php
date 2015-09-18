<?php

namespace Sandbox\ApiBundle\Repository\Buddy;

use Doctrine\ORM\EntityRepository;

class BuddyRepository extends EntityRepository
{
    /**
     * @param $myUser
     *
     * @return array
     */
    public function getBuddies(
        $myUser
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                SELECT b
                FROM SandboxApiBundle:Buddy\Buddy b
                LEFT JOIN SandboxApiBundle:User\User u
                WITH b.buddyId = u.id
                WHERE u.banned = FALSE
                  AND u.authorized = TRUE
                  AND b.user = :myUser
                '
            )
            ->setParameter('myUser', $myUser);

        return $query->getResult();
    }
}
