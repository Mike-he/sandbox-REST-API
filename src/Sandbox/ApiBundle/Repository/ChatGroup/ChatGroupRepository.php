<?php

namespace Sandbox\ApiBundle\Repository\ChatGroup;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\User\User;

class ChatGroupRepository extends EntityRepository
{
    /**
     * @param User $myUser
     *
     * @return array
     */
    public function getMyChatGroups(
        $myUser
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                SELECT
                  cg.id, cg.name, cgm.mute
                FROM SandboxApiBundle:ChatGroup\ChatGroup cg
                LEFT JOIN SandboxApiBundle:ChatGroup\ChatGroupMember cgm
                WITH cg.id = cgm.chatGroupId
                WHERE cgm.user = :myUser
                '
            )
            ->setParameter('myUser', $myUser);

        return $query->getResult();
    }

    /**
     * @param int  $id
     * @param User $myUser
     *
     * @return array
     */
    public function getChatGroup(
        $id,
        $myUser
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                SELECT
                  cg.id, cg.name, cgm.mute
                FROM SandboxApiBundle:ChatGroup\ChatGroup cg
                LEFT JOIN SandboxApiBundle:ChatGroup\ChatGroupMember cgm
                WITH cg.id = cgm.chatGroupId
                WHERE cg.id = :id
                AND cgm.user = :myUser
                '
            )
            ->setParameter('id', $id)
            ->setParameter('myUser', $myUser);

        return $query->getSingleResult();
    }
}
