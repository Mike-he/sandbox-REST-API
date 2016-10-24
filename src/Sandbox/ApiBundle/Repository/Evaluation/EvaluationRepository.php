<?php

namespace Sandbox\ApiBundle\Repository\Evaluation;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Evaluation\Evaluation;

class EvaluationRepository extends EntityRepository
{
    /**
     * @param $user
     * @param $type
     * @param $building
     * @param null $productOrder
     *
     * @return array
     */
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

    /**
     * @param $buildingId
     * @param $limit
     * @param $offset
     * @param $userId
     * @param $minStar
     * @param $maxStar
     * @param $isWithPic
     *
     * @return array
     */
    public function getClientEvaluations(
        $limit,
        $offset,
        $buildingId = null,
        $userId = null,
        $minStar = null,
        $maxStar = null,
        $isWithPic = null
    ) {
        $query = $this->createQueryBuilder('e')
            ->where('e.visible = TRUE')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if (!is_null($buildingId)) {
            $query->andWhere('e.buildingId = :buildingId')
                ->setParameter('buildingId', $buildingId);
        }

        if (!is_null($userId)) {
            $query->andWhere('e.userId = :userId')
                ->setParameter('userId', $userId);
        }

        if (!is_null($minStar)) {
            $query->andWhere('e.totalStar >= :minStar')
                ->setParameter('minStar', $minStar);
        }

        if (!is_null($maxStar)) {
            $query->andWhere('e.totalStar <= :maxStar')
                ->setParameter('maxStar', $maxStar);
        }

        if (!is_null($isWithPic) && $isWithPic) {
            $query->leftJoin('SandboxApiBundle:Evaluation\EvaluationAttachment', 'et', 'WITH', 'et.evaluationId = e.id')
                ->andWhere('et.id > 0');
        }

        $query->orderBy('e.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }
}
