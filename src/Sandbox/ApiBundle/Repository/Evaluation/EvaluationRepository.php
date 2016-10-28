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
        // find by user first
        if (!is_null($userProfileName)) {
            $userQuery = $this->createQueryBuilder('e')
                ->select('DISTINCT up.userId')
                ->from('SandboxApiBundle:User\UserProfile', 'up')
                ->where('up.name LIKE :name')
                ->setParameter('name', $userProfileName.'%');

            $users = $userQuery->getQuery()->getResult();
            $userIds = array_map('current', $users);
        }

        if (!is_null($username)) {
            $userQuery = $this->createQueryBuilder('e')
                ->select('DISTINCT u.id')
                ->from('SandboxApiBundle:User\User', 'u')
                ->where('
                    u.phone LIKE :username
                    OR u.email LIKE :username
                ')
                ->setParameter('username', $username.'%');

            $users = $userQuery->getQuery()->getResult();
            $userIds = array_map('current', $users);
        }

        if (empty($userIds)) {
            return array();
        }

        // get evaluations
        $query = $this->createQueryBuilder('e')
            ->where('e.type != :official')
            ->setParameter('official', Evaluation::TYPE_OFFICIAL);

        if (!is_null($userProfileName) || !is_null($username)) {
            $query->andWhere('e.userId IN (:userIds)')
                ->setParameter('userIds', $userIds);
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
            $query->leftJoin('e.evaluationAttachments', 'et')
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
