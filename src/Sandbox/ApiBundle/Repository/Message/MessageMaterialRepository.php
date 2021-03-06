<?php

namespace Sandbox\ApiBundle\Repository\Message;

use Doctrine\ORM\EntityRepository;

/**
 * MessageRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MessageMaterialRepository extends EntityRepository
{
    /**
     * Get message list.
     *
     * @param $search
     *
     * @return array
     */
    public function getMessageMaterialList(
        $search
    ) {
        $query = $this->createQueryBuilder('m')
            ->where('m.visible = TRUE')
            ->orderBy('m.creationDate', 'DESC');

        if ($search) {
            $query->andWhere('m.title LIKE :search')
                ->setParameter('search', $search.'%');
        }

        return $query->getQuery()->getResult();
    }
}
