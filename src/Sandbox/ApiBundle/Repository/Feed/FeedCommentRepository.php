<?php

namespace Sandbox\ApiBundle\Repository\Feed;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for feed comments.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class FeedCommentRepository extends EntityRepository
{
    /**
     * Get list of comments.
     *
     * @return array
     */
    public function getComments(
        $id,
        $limit,
        $lastId
    ) {
        $parameters = [];

        $query = $this->createQueryBuilder('c')
            ->select('
                c
            ')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'c.authorId = u.id');

        // filter by feed id
        $query->where('c.feedId = :feedId');
        $parameters['feedId'] = $id;

        // filter by user banned and authorized
        $query->andWhere('u.banned = FALSE');
        $query->andWhere('u.authorized = TRUE');

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
