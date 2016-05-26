<?php

namespace Sandbox\ApiBundle\Repository\Bulletin;

use Doctrine\ORM\EntityRepository;

class BulletinPostRepository extends EntityRepository
{
    /**
     * @param $type
     * @param $search
     *
     * @return array
     */
    public function getAdminBulletinPosts(
        $type,
        $search = null
    ) {
        $query = $this->createQueryBuilder('bp')
            ->where('bp.deleted = :deleted')
            ->orderBy('bp.modificationDate', 'DESC')
            ->setParameter('deleted', false);

        if (!is_null($type) && !empty($type)) {
            $query->andWhere('bp.typeId = :type')
                ->setParameter('type', $type);
        }

        if (!is_null($search) && !empty($search)) {
            $query->andWhere('bp.title LIKE :search')
                ->setParameter('search', "%$search%");
        }

        return $query->getQuery()->getResult();
    }
}
