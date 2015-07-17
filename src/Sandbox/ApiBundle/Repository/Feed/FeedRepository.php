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
        $offset
    ) {
        $query = $this->createQueryBuilder('f')
            ->select('
                f
            ');

        $query->orderBy('f.creationDate', 'DESC');
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
