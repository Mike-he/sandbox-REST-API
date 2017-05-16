<?php

namespace Sandbox\ApiBundle\Repository\MembershipCard;

use Doctrine\ORM\EntityRepository;

class MembershipCardRepository extends EntityRepository
{
    /**
     * @param $ids
     *
     * @return array
     */
    public function getClientCardsByIds(
        $ids
    ) {
        $query = $this->createQueryBuilder('c')
            ->where('c.id IN (:ids)')
            ->andWhere('c.visible = TRUE')
            ->setParameter('ids', $ids);

        $query->orderBy('c.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }

    public function getCards(
        $company,
        $visible
    ) {
        $query = $this->createQueryBuilder('c')
            ->where('c.companyId = :company')
            ->setParameter('company', $company);

        if (!is_null($visible)) {
            $query->andWhere('p.visible = :visible')
                ->setParameter('visible', $visible);
        }

        return $query->getQuery()->getResult();
    }
}
