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

    /**
     * @param $card
     *
     * @return array
     */
    public function getCardSpecifications(
        $card
    ) {
        $query = $this->createQueryBuilder('s')
            ->where('s.card = :card')
            ->setParameter('card', $card)
            ->orderBy('s.validPeriod', 'ASC');

        return $query->getQuery()->getResult();
    }
}
