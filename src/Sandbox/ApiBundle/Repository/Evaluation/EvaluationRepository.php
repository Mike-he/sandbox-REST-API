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
            ->andWhere('e.type != :official')
            ->andWhere('official', Evaluation::TYPE_OFFICIAL);

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

        $query->orderBy('e.creationDate', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userProfileName
     * @param $username
     * @param $buildingId
     * @param $minStar
     * @param $maxStar
     * @param $isWithPic
     * @param $isWithComment
     * @param $type
     * @param $visible
     * @param $sortBy
     * @param $sortDirection
     *
     * @return array
     */
    public function getAdminEvaluations(
        $userProfileName,
        $username,
        $buildingId,
        $minStar,
        $maxStar,
        $isWithPic,
        $isWithComment,
        $type,
        $visible,
        $sortBy,
        $sortDirection
    ) {
        $query = $this->createQueryBuilder('e')
            ->where('e.type != :official')
            ->setParameter('official', Evaluation::TYPE_OFFICIAL);

        if (!is_null($userProfileName)) {
            $query->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = e.userId')
                ->andWhere('up.name LIKE :name')
                ->setParameter('name', $userProfileName.'%');
        }

        if (!is_null($username)) {
            $query->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'u.id = e.userId')
                ->andWhere('
                    u.phone LIKE :username
                    OR u.email LIKE :username
                ')
                ->setParameter('username', $username.'%');
        }

        if (!is_null($buildingId)) {
            $query->andWhere('e.buildingId = :buildingId')
                ->setParameter('buildingId', $buildingId);
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

        if (!is_null($isWithComment) && $isWithComment) {
            $query->andWhere('e.comment IS NOT NULL');
        }

        if (!is_null($type)) {
            $query->andWhere('e.type = :type')
                ->setParameter('type', $type);
        }

        if (!is_null($visible)) {
            $query->andWhere('e.visible = :visible')
                ->setParameter('visible', $visible);
        }

        if (!is_null($sortBy)) {
            $query->orderBy('e.'.$sortBy, $sortDirection);
        } else {
            $query->orderBy('e.creationDate', 'DESC');
        }

        return $query->getQuery();
    }

    /**
     * @param $building
     * @param $type
     *
     * @return mixed
     */
    public function countEvaluation(
        $building,
        $type,
        $visible
    ) {
        $query = $this->createQueryBuilder('e')
            ->select('count(e)')
            ->where('e.type = :type')
            ->andWhere('e.building = :building')
            ->setParameter('type', $type)
            ->setParameter('building', $building);

        if (!is_null($visible)) {
            $query->andWhere('e.visible = :visible')
                ->setParameter('visible', $visible);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $building
     * @param $type
     *
     * @return array
     */
    public function sumEvaluation(
        $building,
        $type,
        $visible
    ) {
        $query = $this->createQueryBuilder('e')
            ->select('sum(e.totalStar) as star')
            ->where('e.type = :type')
            ->andWhere('e.building = :building')
            ->setParameter('type', $type)
            ->setParameter('building', $building);

        if (!is_null($visible)) {
            $query->andWhere('e.visible = :visible')
                ->setParameter('visible', $visible);
        }

        return $query->getQuery()->getSingleScalarResult();
    }
}
