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
 * @link     http://www.Sandbox.cn/
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

        // filter by type
        if (!is_null($lastId)) {
            $query->andWhere('c.id > :lastId');
            $parameters['lastId'] = $lastId;
        }

        $query->setMaxResults($limit);

        //set all parameters
        $query->setParameters($parameters);

        // order by creationDate
        $query->orderBy('c.creationDate', 'ASC');

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
