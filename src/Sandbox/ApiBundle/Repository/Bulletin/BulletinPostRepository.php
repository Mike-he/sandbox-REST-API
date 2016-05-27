<?php

namespace Sandbox\ApiBundle\Repository\Bulletin;

use Doctrine\ORM\EntityRepository;
use Sandbox\AdminApiBundle\Data\Position\Position;
use Sandbox\ApiBundle\Entity\Bulletin\BulletinPost;

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
            ->orderBy('bp.sortTime', 'DESC')
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

    /**
     * @param $post
     * @param $action
     *
     * @return BulletinPost
     */
    public function findSwapBulletinPost(
        $post,
        $action
    ) {
        $query = $this->createQueryBuilder('bp')
            ->where('bp.deleted = :deleted')
            ->setParameter('deleted', false);

        // operator
        $operator = '>';
        $orderBy = 'ASC';
        if ($action == Position::ACTION_DOWN) {
            $operator = '<';
            $orderBy = 'DESC';
        }

        $query = $query->andWhere('bp.sortTime '.$operator.' :sortTime')
            ->setParameter('sortTime', $post->getSortTime())
            ->orderBy('bp.sortTime', $orderBy)
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
