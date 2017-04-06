<?php

namespace Sandbox\ApiBundle\Repository\MembershipCard;

use Doctrine\ORM\EntityRepository;

class MembershipCardSpecificationRepository extends EntityRepository
{
    /**
     * @param $card
     *
     * @return array
     */
    public function getMinCardSpecification(
        $card
    ) {
        $query = $this->createQueryBuilder('s')
            ->where('s.card = :card')
            ->setParameter('card', $card)
            ->orderBy('s.validPeriod', 'ASC')
            ->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }
}