<?php

namespace Sandbox\ApiBundle\Repository\Feed;

use Doctrine\ORM\EntityRepository;

class FeedLikeRepository extends EntityRepository
{
    public function getLikes(
        $id
    ) {
        $query = $this->createQueryBuilder('l')
            ->select('l.authorId')
            ->where('l.feedId = :feedId')
            ->setParameter('feedId', $id);

        $query->orderBy('l.creationDate', 'ASC');

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
