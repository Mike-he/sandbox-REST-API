<?php

namespace Sandbox\ApiBundle\Repository\Feed;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for feed.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class FeedRepository extends EntityRepository
{
    /**
     * Get list of feeds.
     *
     * @param int $limit
     * @param int $lastId
     * @param $platform
     *
     * @return array
     */
    public function getFeeds(
        $limit,
        $lastId,
        $platform
    ) {
        $parameters = [];

        $query = $this->createQueryBuilder('f')
            ->select('f')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'f.ownerId = u.id')
            ->where('f.isDeleted = FALSE');

        // filter by user banned
        $query->andWhere('u.banned = FALSE');

        // filter by type
        if (!is_null($lastId)) {
            $query->andWhere('f.id < :lastId');
            $parameters['lastId'] = $lastId;
        }

        $query->orderBy('f.creationDate', 'DESC');

        $query->setMaxResults($limit);

        // set all parameters
        if (!empty($parameters)) {
            $query->setParameters($parameters);
        }

        if (!is_null($platform)) {
            $query->andWhere('f.platform = :platform')
                ->setParameter('platform', $platform);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * Get list of feeds of my buddies.
     *
     * @param int $limit
     * @param int $lastId
     * @param int $userId
     *
     * @return array
     */
    public function getFeedsByBuddies(
        $limit,
        $lastId,
        $userId,
        $platform
    ) {
        $userIds = array($userId);

        // get my buddy ids
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('
                b.buddyId
            ')
            ->from('SandboxApiBundle:Buddy\Buddy', 'b')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'b.buddyId = u.id')
            ->where('b.userId = :userId')
            ->andWhere('u.banned = FALSE')
            ->setParameter('userId', $userId);
        $buddyIds = $query->getQuery()->getResult();

        if (!is_null($buddyIds) && !empty($buddyIds)) {
            foreach ($buddyIds as $buddyId) {
                array_push($userIds, $buddyId['buddyId']);
            }
        }

        // get feeds post by me and my buddies
        $parameters = [];

        $query = $this->createQueryBuilder('f')
            ->select('f');

        // filter by my buddies and my own posts
        $query->where('f.ownerId IN (:userIds)');
        $parameters['userIds'] = $userIds;

        // filter by feed delete
        $query->andWhere('f.isDeleted = FALSE');

        // last id
        if (!is_null($lastId)) {
            $query->andWhere('f.id < :lastId');
            $parameters['lastId'] = $lastId;
        }

        $query->orderBy('f.creationDate', 'DESC');

        $query->setMaxResults($limit);

        // set all parameters
        $query->setParameters($parameters);

        if (!is_null($platform)) {
            $query->andWhere('f.platform = :platform')
                ->setParameter('platform', $platform);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * Get list of feeds of people in my building.
     *
     * @param int $limit
     * @param int $lastId
     * @param int $buildingId
     * @param $platform
     *
     * @return array
     */
    public function getFeedsByBuilding(
        $limit,
        $lastId,
        $buildingId,
        $platform
    ) {
        $parameters = [];

        $query = $this->createQueryBuilder('f')
            ->select('f')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = f.ownerId')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'f.ownerId = u.id')
            ->where('f.isDeleted = FALSE');

        // filter by user banned
        $query->andWhere('u.banned = FALSE');

        // filter by my building
        $query->andwhere('up.buildingId = :buildingId');
        $parameters['buildingId'] = $buildingId;

        // last id
        if (!is_null($lastId)) {
            $query->andWhere('f.id < :lastId');
            $parameters['lastId'] = $lastId;
        }

        $query->orderBy('f.creationDate', 'DESC');

        $query->setMaxResults($limit);

        // set all parameters
        $query->setParameters($parameters);

        if (!is_null($platform)) {
            $query->andWhere('f.platform = :platform')
                ->setParameter('platform', $platform);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * Get list of feeds of people in my company.
     *
     * @param int $limit
     * @param int $lastId
     * @param int $userId
     *
     * @return array
     */
    public function getFeedsByColleagues(
        $limit,
        $lastId,
        $userId
    ) {
        $parameters = [];

        // get my company ids
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('
                cm.companyId
            ')
            ->from('SandboxApiBundle:Company\CompanyMember', 'cm')
            ->where('cm.userId = :userId')
            ->setParameter('userId', $userId);
        $companyIds = $query->getQuery()->getResult();

        if (is_null($companyIds) || empty($companyIds)) {
            return array();
        }

        // get feeds post by company members
        $query = $this->createQueryBuilder('f')
            ->select('f')
            ->leftJoin('SandboxApiBundle:Company\CompanyMember', 'cm', 'WITH', 'cm.userId = f.ownerId')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'f.ownerId = u.id')
            ->where('f.isDeleted = FALSE');

        // filter by user banned
        $query->andWhere('u.banned = FALSE');

        // filter by my companies
        $query->andwhere('cm.companyId IN (:companyIds)');
        $parameters['companyIds'] = $companyIds;

        // last id
        if (!is_null($lastId)) {
            $query->andWhere('f.id < :lastId');
            $parameters['lastId'] = $lastId;
        }

        $query->orderBy('f.creationDate', 'DESC');

        $query->setMaxResults($limit);

        // set all parameters
        $query->setParameters($parameters);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param int $userId
     * @param int $limit
     * @param int $lastId
     * @param $platform
     *
     * @return array
     */
    public function getMyFeeds(
        $userId,
        $limit,
        $lastId,
        $platform
    ) {
        $query = $this->createQueryBuilder('f')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'f.ownerId = u.id')
            ->where('f.ownerId = :myUserId')
            ->andWhere('f.isDeleted = FALSE')
            ->setParameter('myUserId', $userId);

        // filter by user banned
        $query->andWhere('u.banned = FALSE');

        // filter by type
        if (!is_null($lastId)) {
            $query->andWhere('f.id < :lastId')
                ->setParameter('lastId', $lastId);
        }

        if (!is_null($platform)) {
            $query->andWhere('f.platform = :platform')
                ->setParameter('platform', $platform);
        }

        $query->orderBy('f.creationDate', 'DESC');

        $query->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    /**
     * @param string $query
     *
     * @return array
     */
    public function getVerifyFeeds(
        $query
    ) {
        $queryBuilder = $this->createQueryBuilder('f')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'f.ownerId = up.userId')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'f.ownerId = u.id')
            ->where('up.name LIKE :query')
            ->orWhere('u.phone LIKE :query')
            ->orWhere('u.email LIKE :query')
            ->andWhere('f.isDeleted = FALSE')
            ->setParameter('query', $query.'%')
            ->orderBy('f.creationDate', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }
}
