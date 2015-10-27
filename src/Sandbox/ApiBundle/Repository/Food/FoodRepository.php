<?php

namespace Sandbox\ApiBundle\Repository\Food;

use Doctrine\ORM\EntityRepository;

/**
 * FoodRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FoodRepository extends EntityRepository
{
    /**
     * Get food list.
     *
     * @param string $category
     * @param int    $buildingId
     * @param string $direction
     * @param string $search
     *
     * @return array
     */
    public function getFoodList(
        $category,
        $buildingId,
        $direction,
        $search
    ) {
        $query = $this->createQueryBuilder('f')
            ->where('f.buildingId = :buildingId')
            ->setParameter('buildingId', $buildingId);

        if (!is_null($category) && !empty($category)) {
            $query = $query->andWhere('f.category = :category')
                ->setParameter('category', $category);
        }

        //search by
        if (!is_null($search)) {
            $query = $query->andWhere('f.name LIKE :search OR f.id LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        $query = $query->orderBy('f.creationDate', $direction)
            ->getQuery();

        return $query->getResult();
    }
}
