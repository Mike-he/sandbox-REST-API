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
        $cardIds,
        $visible,
        $search
    ) {
        $query = $this->createQueryBuilder('c')
            ->where('c.companyId = :company')
            ->setParameter('company', $company);

        if (!is_null($cardIds) || !empty($cardIds)) {
            $query->andWhere('c.id IN (:ids)')
                ->setParameter('ids', $cardIds);
        }

        if (!is_null($visible)) {
            $query->andWhere('c.visible = :visible')
                ->setParameter('visible', $visible);
        }

        if (!is_null($search)) {
            $query->andWhere('c.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $query->getQuery()->getResult();
    }
}
