<?php

namespace Sandbox\ApiBundle\Repository\Feed;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for feed.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class FeedRepository extends EntityRepository
{
    /**
     * Get list of feeds.
     *
     * @return array
     */
    public function getFeeds(
        $limit,
        $lastId
    ) {
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('f')
            ->select('
                f
            ');

        // filter by type
        if (!is_null($lastId)) {
            $query->where('f.id < :lastId');
            $parameters['lastId'] = $lastId;
            $notFirst = true;
        }

        $query->orderBy('f.creationDate', 'DESC');

        $query->setMaxResults($limit);

        //set all parameters
        if ($notFirst) {
            $query->setParameters($parameters);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
