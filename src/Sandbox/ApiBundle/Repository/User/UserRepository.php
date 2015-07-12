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
                    AND u.banned = false';

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

        // set limit
        // TODO make default limit global and configurable
        if (is_null($limit) || $limit <= 0 || $limit > 10) {
            $limit = 10;
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
}
