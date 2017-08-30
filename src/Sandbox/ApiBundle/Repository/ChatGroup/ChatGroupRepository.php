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
                  cg.id,
                  cg.name,
                  cg.creatorId AS creator_id,
                  cgm.mute,
                  cg.tag
                FROM SandboxApiBundle:ChatGroup\ChatGroup cg
                LEFT JOIN SandboxApiBundle:ChatGroup\ChatGroupMember cgm
                WITH cg = cgm.chatGroup
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
                  cg.id,
                  cg.name,
                  cg.creatorId AS creator_id,
                  cgm.mute
                FROM SandboxApiBundle:ChatGroup\ChatGroup cg
                LEFT JOIN SandboxApiBundle:ChatGroup\ChatGroupMember cgm
                WITH cg = cgm.chatGroup
                WHERE cg.id = :id
                AND cgm.user = :myUser
                '
            )
            ->setParameter('id', $id)
            ->setParameter('myUser', $myUser);

        return $query->getSingleResult();
    }

    /**
     * @param int    $companyId
     * @param int    $userId
     * @param string $search
     * @param string $tag
     *
     * @return array
     */
    public function getAdminChatGroups(
        $companyId,
        $userId,
        $search,
        $tag
    ) {
        $query = $this->createQueryBuilder('g')
            ->select('
                g.id,
                g.name,
                g.tag,
                g.buildingId,
                g.creatorId,
                up.name as creator_name,
                u.xmppUsername as creator_xmppUsername,
                g.gid
            ')
            ->leftJoin('SandboxApiBundle:ChatGroup\ChatGroupMember', 'm', 'WITH', 'g.id = m.chatGroup')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'u.id = g.creatorId')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'u.id = up.userId')
            ->where('g.companyId = :companyId')
            ->andWhere('m.user = :userId')
            ->andWhere('u.email LIKE :search OR u.phone LIKE :search OR up.name LIKE :search')
            ->setParameter('companyId', $companyId)
            ->setParameter('search', "%$search%")
            ->setParameter('userId', $userId);

        if ($tag) {
            $query = $query->andWhere('g.tag = :tag')
                ->setParameter('tag', $tag);
        }

        $query->orderBy('g.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $gid
     * @param $companyId
     * @param $userId
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAdminChatGroupById(
        $gid,
        $companyId,
        $userId
    ) {
        $query = $this->createQueryBuilder('g')
            ->select('
                g.id,
                g.name,
                g.tag,
                g.buildingId,
                g.creatorId,
                g.gid,
                up.name as creator_name
            ')
            ->leftJoin('SandboxApiBundle:ChatGroup\ChatGroupMember', 'm', 'WITH', 'g.id = m.chatGroup')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'u.id = g.creatorId')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'u.id = up.userId')
            ->where('g.gid = :gid')
            ->andWhere('g.companyId = :companyId')
            ->andWhere('m.user = :userId')
            ->setParameter('companyId', $companyId)
            ->setParameter('gid', $gid)
            ->setParameter('userId', $userId);

        return $query->getQuery()->getOneOrNullResult();
    }
}
