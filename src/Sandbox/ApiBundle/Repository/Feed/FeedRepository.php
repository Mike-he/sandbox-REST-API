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
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('f')
            ->select('
                f
            ')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'f.ownerId = u.id');

        // filter by user banned
        $query->where('u.banned = FALSE');

        // filter by type
        if (!is_null($lastId)) {
            $query->andWhere('f.id < :lastId');
            $parameters['lastId'] = $lastId;
            $notFirst = true;
        }

        $query->orderBy('f.creationDate', 'DESC');

        $query->setMaxResults($limit);

        //set all parameters
        if ($notFirst) {
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
        $parameters = [];

        $query = $this->createQueryBuilder('f')
            ->leftJoin('SandboxApiBundle:Buddy\Buddy', 'b', 'WITH', 'b.buddyId = f.ownerId')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'f.ownerId = u.id');

        // filter by user banned
        $query->where('u.banned = FALSE');

        // filter by my buddies and my own posts
        $query->andwhere('b.userId = :userId');
        $query->orWhere('f.ownerId = :userId');
        $parameters['userId'] = $userId;

        // last id
        if (!is_null($lastId)) {
            $query->andWhere('f.id < :lastId');
            $parameters['lastId'] = $lastId;
        }

        $query->orderBy('f.creationDate', 'DESC');

        $query->setMaxResults($limit);

        //set all parameters
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
            ->select('
                f
            ')
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

        //set all parameters
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

        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('
                cm.companyId
            ')
            ->from('SandboxApiBundle:Company\CompanyMember', 'cm')
            ->where('cm.userId = :userId')
            ->setParameter('userId', $userId);
        $companyIds = $query->getQuery()->getResult();

        $query = $this->createQueryBuilder('f')
            ->select('
                f
            ')
            ->leftJoin('SandboxApiBundle:Company\CompanyMember', 'cm', 'WITH', 'cm.userId = f.ownerId')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'f.ownerId = u.id');

        // filter by user banned
        $query->where('u.banned = FALSE');

        // filter by my company
        $query->andwhere('cm.companyId IN (:companyIds)');
        $parameters['companyIds'] = $companyIds;

        // last id
        if (!is_null($lastId)) {
            $query->andWhere('f.id < :lastId');
            $parameters['lastId'] = $lastId;
        }

        $query->orderBy('f.creationDate', 'DESC');

        $query->setMaxResults($limit);

        //set all parameters
        $query->setParameters($parameters);

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
