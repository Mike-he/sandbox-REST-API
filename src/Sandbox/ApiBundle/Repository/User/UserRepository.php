<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param int   $myUserId
     * @param array $recordIds
     * @param int   $limit
     *
     * @return array
     */
    public function findRandomMembers(
        $myUserId,
        $recordIds,
        $limit
    ) {
        $queryStr = 'SELECT u.id
                     FROM SandboxApiBundle:User\User u
                     WHERE u.banned = FALSE
                     AND u.id != :myUserId
                     AND u.authorized = TRUE';

        if (!is_null($recordIds) && !empty($recordIds)) {
            $queryStr = $queryStr.' AND u.id NOT IN (:ids)';
        }

        // get available user ids
        $query = $this->getEntityManager()->createQuery($queryStr);

        $query->setParameter('myUserId', $myUserId);
        if (!is_null($recordIds) && !empty($recordIds)) {
            $query->setParameter('ids', $recordIds);
        }

        $availableIds = $query->getScalarResult();
        if (empty($availableIds)) {
            // nothing is found
            return array();
        }

        // set total
        $total = $limit;
        $idsCount = count($availableIds);
        if ($idsCount < $limit) {
            $total = $idsCount;
        }

        // get random ids
        $ids = array();
        $randElements = array_rand($availableIds, $total);
        if (is_array($randElements)) {
            foreach ($randElements as $randElement) {
                array_push($ids, $availableIds[$randElement]);
            }
        } else {
            array_push($ids, $availableIds[$randElements]);
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
     * @param int   $range
     *
     * @return array
     */
    public function findNearbyMembers(
        $myUserId,
        $latitude,
        $longitude,
        $limit,
        $offset,
        $range = 50
    ) {
        // find nearby buildings
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT rb.id,
                  (
                    6371
                    * acos(cos(radians(:latitude)) * cos(radians(rb.lat))
                    * cos(radians(rb.lng) - radians(:longitude))
                    + sin(radians(:latitude)) * sin(radians(rb.lat)))
                    ) as HIDDEN distance
                    FROM SandboxApiBundle:Room\RoomBuilding rb
                    HAVING distance < :range
                    ORDER BY distance
                '
            )
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('range', $range);

        $buildingIds = $query->getResult();

        // find members
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT u,
                  field(up.buildingId, :buildingIds) as HIDDEN field
                  FROM SandboxApiBundle:User\User u
                  LEFT JOIN SandboxApiBundle:User\UserProfile up
                  WITH u.id = up.userId
                  WHERE u.id != :myUserId
                  AND u.banned = FALSE
                  AND up.buildingId IN (:buildingIds)
                  ORDER BY field
                '
            )
            ->setParameter('myUserId', $myUserId)
            ->setParameter('buildingIds', $buildingIds);

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }
}
