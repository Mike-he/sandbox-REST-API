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
            ');

        // filter by type
        if (!is_null($lastId)) {
            $query->where('f.id < :lastId');
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
     * @return array
     */
    public function getFeedsByBuddies(
        $limit,
        $lastId,
        $userId
    ) {
        $parameters = [];

        $query = $this->createQueryBuilder('f')
            ->select('
                f
            ')
            ->leftJoin('SandboxApiBundle:Buddy\Buddy', 'b', 'WITH', 'b.buddyId = f.ownerId');

        // filter by buddies
        $query->where('b.userId = :userId');
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
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = f.ownerId');

        // filter by my building
        $query->where('up.buildingId = :buildingId');
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
     * @param int $companyId
     *
     * @return array
     */
    public function getFeedsByColleagues(
        $limit,
        $lastId,
        $companyId
    ) {
        $parameters = [];

        $query = $this->createQueryBuilder('f')
            ->select('
                f
            ')
            ->leftJoin('SandboxApiBundle:Company\CompanyMember', 'cm', 'WITH', 'cm.userId = f.ownerId');

        // filter by my company
        $query->where('cm.companyId = :companyId');
        $parameters['companyId'] = $companyId;

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
