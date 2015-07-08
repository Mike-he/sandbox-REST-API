<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserHobbyMapRepository extends EntityRepository
{
    /**
     * @param $ids
     * @param $userId
     */
    public function deleteUserHobbies(
        $ids,
        $userId
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:User\UserHobbyMap uhm
                    WHERE uhm.userId = (:userId)
                    AND uhm.hobbyId IN (:ids)
                '
            )
            ->setParameter('userId', $userId)
            ->setParameter('ids', $ids);

        $query->execute();
    }
}
