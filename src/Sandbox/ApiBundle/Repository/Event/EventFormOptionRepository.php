<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;

class EventFormOptionRepository extends EntityRepository
{
    /**
     * @param array $ids
     * @param int   $formId
     *
     * @return array
     */
    public function getEventFormOptionCheckbox(
        $ids,
        $formId
    ) {
        $query = $this->createQueryBuilder('efo')
            ->select('efo.content')
            ->where('efo.id IN (:ids)')
            ->andWhere('efo.formId = :formId')
            ->setParameter('ids', $ids)
            ->setParameter('formId', $formId);

        return $query->getQuery()->getResult();
    }
}
