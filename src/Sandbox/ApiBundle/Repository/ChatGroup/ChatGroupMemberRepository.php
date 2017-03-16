<?php

namespace Sandbox\ApiBundle\Repository\ChatGroup;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup;

class ChatGroupMemberRepository extends EntityRepository
{
    /**
     * @param ChatGroup $chatGroup
     *
     * @return array
     */
    public function getChatGroupMembers(
        $chatGroup
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    SELECT cgm
                    FROM SandboxApiBundle:ChatGroup\ChatGroupMember cgm
                    LEFT JOIN SandboxApiBundle:User\User u
                    WITH cgm.user = u
                    WHERE
                      cgm.chatGroup = :chatGroup
                      AND u.banned = FALSE
                '
            )
            ->setParameter('chatGroup', $chatGroup);

        return $query->getResult();
    }

    /**
     * @param ChatGroup $chatGroup
     *
     * @return array
     */
    public function getChatGroupMembersByGroup(
        $chatGroup
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    SELECT u.id, u.xmppUsername, up.name
                    FROM SandboxApiBundle:ChatGroup\ChatGroupMember cgm
                    LEFT JOIN SandboxApiBundle:User\User u
                    WITH cgm.user = u
                    LEFT JOIN SandboxApiBundle:User\UserProfile up
                    WITH u.id = up.userId
                    WHERE
                      cgm.chatGroup = :chatGroup
                      AND u.banned = FALSE
                '
            )
            ->setParameter('chatGroup', $chatGroup['id']);

        return $query->getResult();
    }
}
