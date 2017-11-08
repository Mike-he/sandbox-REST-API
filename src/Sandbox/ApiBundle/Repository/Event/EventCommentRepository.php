<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for feed comments.
 *
 * @category Sandbox
 *
 * @author   Mike He
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class EventCommentRepository extends EntityRepository
{
    /**
     * Get list of comments.
     *
     * @param $id
     * @param $limit
     * @param $lastId
     *
     * @return array
     */
    public function getEventComments(
        $id,
        $limit,
        $lastId
    ) {
        $parameters = [];

        $query = $this->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'c.authorId = u.id');

        // filter by feed id
        $query->where('c.eventId = :eventId');
        $parameters['eventId'] = $id;

        // filter by user banned
        $query->andWhere('u.banned = FALSE');

        // filter by last id
        if (!is_null($lastId)) {
            $query->andWhere('c.id < :lastId');
            $parameters['lastId'] = $lastId;
        }

        $query->setMaxResults($limit);

        //set all parameters
        $query->setParameters($parameters);

        // order by creationDate
        $query->orderBy('c.creationDate', 'DESC');

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $eventId
     *
     * @return mixed
     */
    public function getCommentsCount(
        $eventId
    ) {
        $query = $this->createQueryBuilder('ec')
            ->select('COUNT(ec.id)')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'u.id = ec.authorId')
            ->where('ec.eventId = :eventId')
            ->andWhere('u.banned = FALSE')
            ->setParameter('eventId', $eventId);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $eventId
     *
     * @return array
     */
    public function getAdminEventComments(
        $eventId
    ) {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'c.authorId = u.id')
            ->where('c.eventId = :eventId')
            ->andWhere('u.banned = FALSE')
            ->orderBy('c.creationDate', 'DESC')
            ->setParameter('eventId', $eventId);

        return $query->getQuery()->getResult();
    }

    /*********************************** sales api *********************************/

    /**
     * @param $eventId
     *
     * @return array
     */
    public function getSalesAdminEventComments(
        $eventId
    ) {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'c.authorId = u.id')
            ->where('c.eventId = :eventId')
            ->andWhere('u.banned = FALSE')
            ->orderBy('c.creationDate', 'DESC')
            ->setParameter('eventId', $eventId);

        return $query->getQuery()->getResult();
    }
}
