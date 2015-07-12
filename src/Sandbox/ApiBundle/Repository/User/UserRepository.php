<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param int   $myUserId
     * @param array $recordMemberIds
     * @param int   $limit
     *
     * @return array
     */
    public function findRandomMembers(
        $myUserId,
        $recordMemberIds,
        $limit
    ) {
        $queryStr = 'SELECT u.id FROM SandboxApiBundle:User\User u
                    WHERE u.id != :myUserId
                    AND u.banned = FALSE';

        if (!is_null($recordMemberIds) && !empty($recordMemberIds)) {
            $queryStr = $queryStr.' AND u.id NOT IN (:ids)';
        }

        // get available user ids
        $query = $this->getEntityManager()
            ->createQuery($queryStr)
            ->setParameter('myUserId', $myUserId);

        if (!is_null($recordMemberIds) && !empty($recordMemberIds)) {
            $query->setParameter('ids', $recordMemberIds);
        }

        $availableUserIds = $query->getScalarResult();
        if (empty($availableUserIds)) {
            // nothing is found
            return array();
        }

        // get random ids
        $ids = array();
        $count = 0;
        $total = $limit;

        $idsCount = count($availableUserIds);
        if ($idsCount < $limit) {
            $total = $idsCount;
        }

        while ($count < $total) {
            ++$count;

            $randElement = array_rand($availableUserIds);
            $randNum = $availableUserIds[$randElement];
            unset($availableUserIds[$randElement]);

            array_push($ids, $randNum);
        }

        if (empty($ids)) {
            // nothing is found
            return array();
        }

        // get users
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT u FROM SandboxApiBundle:User\User u
                  WHERE u.id IN (:ids)
                  ORDER BY u.modificationDate DESC
                '
            )
            ->setParameter('ids', $ids);

        $query->setMaxResults($limit);

        return $query->getResult();
    }

    /**
     * @param int   $myUserId
     * @param float $latitude
     * @param float $longitude
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     */
    public function findNearbyMembers(
        $myUserId,
        $latitude,
        $longitude,
        $limit,
        $offset
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT u.id,
                  (
                    6371
                    * acos(cos(radians(:latitude)) * cos(radians(rb.lat))
                    * cos(radians(rb.lng) - radians(:longitude))
                    + sin(radians(:latitude)) * sin(radians(rb.lat)))
                    ) distance
                    FROM SandboxApiBundle:User\User u
                    LEFT JOIN SandboxApiBundle:User\UserProfile up
                    WITH u.id = up.userId
                    LEFT JOIN SandboxApiBundle:Room\RoomBuilding rb
                    WITH up.buildingId = rb.id
                    WHERE u.id != :myUserId
                    AND u.banned = FALSE
                    HAVING distance < :range
                    ORDER BY distance
                '
            )
            ->setParameter('myUserId', $myUserId)
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('range', 100);

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }
}
