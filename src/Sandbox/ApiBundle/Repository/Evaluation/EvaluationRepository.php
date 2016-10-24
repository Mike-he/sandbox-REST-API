<?php

namespace Sandbox\ApiBundle\Repository\Evaluation;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Evaluation\Evaluation;

class EvaluationRepository extends EntityRepository
{
    public function checkEvaluation(
        $user,
        $type,
        $building,
        $productOrder = null
    ) {
        $query = $this->createQueryBuilder('e')
            ->where('e.userId = :user')
            ->andWhere('e.type = :type')
            ->andWhere('e.buildingId = :building')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->setParameter('building', $building);

        if ($type == Evaluation::TYPE_ORDER) {
            $query->andWhere('e.productOrderId = :productOrder')
                ->setParameter('productOrder', $productOrder);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
