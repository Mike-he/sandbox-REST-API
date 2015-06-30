<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserHobbyMapRepository extends EntityRepository
{
    public function deleteUserHobbyMapsByIds(
        $ids
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:User\UserHobbyMap uhm
                    WHERE
                    uhm.id IN (:ids)
                ')
            ->setParameter('ids', $ids);

        $query->execute();
    }
}
