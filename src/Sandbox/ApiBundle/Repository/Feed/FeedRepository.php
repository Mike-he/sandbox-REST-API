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
     *
     * @return array
     */
    public function getFeeds(
        $limit,
        $lastId
    ) {
        $parameters = [];

        $query = $this->createQueryBuilder('f')
            ->select('f')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'f.ownerId = u.id');

        // filter by user banned
        $query->where('u.banned = FALSE');

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
        $userId
    ) {
        $userIds = array($userId);

        // get my buddy ids
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('
                b.buddyId
            ')
            ->from('SandboxApiBundle:Buddy\Buddy', 'b')
            ->where('b.userId = :userId')
            ->setParameter('userId', $userId);
        $buddyIds = $query->getQuery()->getResult();

        if (!is_null($buddyIds) && !empty($buddyIds)) {
            array_push($userIds, $buddyIds);
        }

        // get feeds post by me and my buddies
        $parameters = [];

        $query = $this->createQueryBuilder('f')
            ->select('f')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'f.ownerId = u.id');

        // filter by user banned
        $query->where('u.banned = FALSE');

        // filter by my buddies and my own posts
        $query->andwhere('f.ownerId IN (:userIds)');
        $parameters['userIds'] = $userIds;

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
     * Get list of feeds of people in my building.
     *
     * @param int $limit
     * @param int $lastId
     * @param int $buildingId
     *
     * @return array
     */
    public function getFeedsByBuilding(
        $limit,
        $lastId,
        $buildingId
    ) {
        $parameters = [];

        $query = $this->createQueryBuilder('f')
            ->select('f')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = f.ownerId')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'f.ownerId = u.id');

        // filter by user banned
        $query->where('u.banned = FALSE');

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
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'f.ownerId = u.id');

        // filter by user banned
        $query->where('u.banned = FALSE');

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
}
