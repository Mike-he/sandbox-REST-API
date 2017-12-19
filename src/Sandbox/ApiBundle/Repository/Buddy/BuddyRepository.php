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
                SELECT b.buddyId
                FROM SandboxApiBundle:Buddy\Buddy b
                LEFT JOIN SandboxApiBundle:User\User u
                WITH b.buddyId = u.id
                WHERE u.banned = FALSE
                  AND b.user = :myUser
                '
            )
            ->setParameter('myUser', $myUser);

        return $query->getResult();
    }
}
