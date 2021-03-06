<?php

namespace Sandbox\ApiBundle\Repository\Bulletin;

use Doctrine\ORM\EntityRepository;
use Sandbox\AdminApiBundle\Data\Position\Position;

/**
 * BulletinTypeRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BulletinTypeRepository extends EntityRepository
{
    /**
     * @param $type
     * @param $action
     *
     * @return array
     */
    public function findSwapBulletinType(
        $type,
        $action
    ) {
        $query = $this->createQueryBuilder('bt')
            ->where('bt.deleted = :deleted')
            ->setParameter('deleted', false);

        // operator
        $operator = '>';
        $orderBy = 'ASC';
        if ($action == Position::ACTION_DOWN) {
            $operator = '<';
            $orderBy = 'DESC';
        }

        $query = $query->andWhere('bt.sortTime '.$operator.' :sortTime')
            ->setParameter('sortTime', $type->getSortTime())
            ->orderBy('bt.sortTime', $orderBy)
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
