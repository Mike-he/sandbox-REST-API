<?php

namespace Sandbox\ApiBundle\Repository\News;

use Doctrine\ORM\EntityRepository;

class NewsRepository extends EntityRepository
{
    /**
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getAllClientNews(
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('n')
            ->where('n.visible = true');

        $query->orderBy('n.creationDate', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }
}
