<?php

namespace Sandbox\ApiBundle\Repository\Shop;

use Doctrine\ORM\EntityRepository;

class ShopAttachmentRepository extends EntityRepository
{
    /**
     * @param $company
     *
     * @return array
     */
    public function findAttachmentByCompany(
        $company
    ) {
        $query = $this->createQueryBuilder('sa')
            ->select('sa.content')
            ->leftJoin('sa.shop', 's')
            ->leftJoin('s.building', 'b')
            ->where('b.company = :company')
            ->setParameter('company', $company)
            ->orderBy('s.id', 'ASC');

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
