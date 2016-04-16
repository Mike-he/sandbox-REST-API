<?php

namespace Sandbox\ApiBundle\Repository\Shop;

use Doctrine\ORM\EntityRepository;

/**
 * ShopSpecRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ShopSpecRepository extends EntityRepository
{
    /**
     * @param $companyId
     * @param $search
     *
     * @return array
     */
    public function getSpecsByCompany(
        $companyId,
        $search
    ) {
        $query = $this->createQueryBuilder('ss')
            ->where('ss.companyId = :companyId')
            ->andWhere('ss.invisible = :invisible')
            ->andWhere('ss.auto = :auto')
            ->orderBy('ss.id', 'ASC')
            ->setParameter('companyId', $companyId)
            ->setParameter('invisible', false)
            ->setParameter('auto', false);

        if (!is_null($search)) {
            $query->andWhere('ss.name LIKE :search')
                ->setParameter('search', "%$search%");
        }

        return $query->getQuery()->getResult();
    }
}
