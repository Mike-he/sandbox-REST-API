<?php

namespace Sandbox\ApiBundle\Repository\Message;

use Doctrine\ORM\EntityRepository;

/**
 * MessageRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class JmessageHistoryRepository extends EntityRepository
{
    /**
     * @param $type
     * @param $targetId
     *
     * @return array
     */
    public function getFromIds(
        $type,
        $targetId
    ) {
        $query = $this->createQueryBuilder('m')
            ->select('m.fromId as from_id')
            ->where('m.targetType = :type')
            ->andWhere('m.targetId = :targetId')
            ->setParameter('type', $type)
            ->setParameter('targetId', $targetId);

        $query->orderBy('m.msgCtime', 'DESC');

        return $query->getQuery()->getResult();
    }

    public function getLastMessages(
        $fromID,
        $targetId,
        $type
    ) {
        $query = $this->createQueryBuilder('m')
            ->where('m.targetType = :type')
            ->andWhere('m.fromId = :fromId')
            ->andWhere('m.targetId = :targetId')
            ->setParameter('type', $type)
            ->setParameter('fromId', $fromID)
            ->setParameter('targetId', $targetId)
            ->setMaxResults(1);

        $query->orderBy('m.msgCtime', 'DESC');

        return $query->getQuery()->getOneOrNullResult();
    }

    public function getSingleMessages(
        $fromID,
        $targetId,
        $type,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('m')
            ->where('m.targetType = :type')
            ->andWhere('m.fromId = :fromId or m.targetId = :fromId')
            ->andWhere('m.fromId = :targetId or m.targetId = :targetId')
            ->setParameter('type', $type)
            ->setParameter('fromId', $fromID)
            ->setParameter('targetId', $targetId);

        $query->orderBy('m.msgCtime', 'ASC');

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }

    public function countSingleMessages(
        $fromID,
        $targetId,
        $type
    ) {
        $query = $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->where('m.targetType = :type')
            ->andWhere('m.fromId = :fromId or m.targetId = :fromId')
            ->andWhere('m.fromId = :targetId or m.targetId = :targetId')
            ->setParameter('type', $type)
            ->setParameter('fromId', $fromID)
            ->setParameter('targetId', $targetId);

        return $query->getQuery()->getSingleScalarResult();
    }
}