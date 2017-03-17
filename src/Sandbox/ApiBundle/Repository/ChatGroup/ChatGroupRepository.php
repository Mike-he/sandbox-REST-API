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
     *
     * @return array
     */
    public function getAdminChatGroups(
        $companyId,
        $userId,
        $search
    ) {
        $query = $this->createQueryBuilder('g')
            ->select('
                g.id,
                g.name,
                g.tag,
                g.buildingId,
                g.creatorId,
                up.name as creator_name
            ')
            ->leftJoin('SandboxApiBundle:Room\RoomBuildingServiceMember', 'm', 'WITH', 'm.buildingId = g.buildingId')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'u.id = g.creatorId')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'u.id = up.userId')
            ->where('g.companyId = :companyId')
            ->andWhere('m.userId = :userId')
            ->andWhere('u.email LIKE :search OR u.phone LIKE :search OR up.name LIKE :search')
            ->setParameter('companyId', $companyId)
            ->setParameter('search', "%$search%")
            ->setParameter('userId', $userId)
            ->orderBy('g.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $id
     * @param $companyId
     * @param $userId
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAdminChatGroupById(
        $id,
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
                up.name as creator_name
            ')
            ->leftJoin('SandboxApiBundle:Room\RoomBuildingServiceMember', 'm', 'WITH', 'm.buildingId = g.buildingId')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'u.id = g.creatorId')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'u.id = up.userId')
            ->where('g.id = :id')
            ->andWhere('g.companyId = :companyId')
            ->andWhere('m.userId = :userId')
            ->setParameter('companyId', $companyId)
            ->setParameter('id', $id)
            ->setParameter('userId', $userId);

        return $query->getQuery()->getOneOrNullResult();
    }
}
