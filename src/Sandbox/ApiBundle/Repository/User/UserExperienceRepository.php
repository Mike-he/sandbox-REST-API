<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserExperienceRepository extends EntityRepository
{
    public function deleteUserExperiencesByIds(
        $ids
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:User\UserExperience ue
                    WHERE
                    ue.id IN (:ids)
                ')
            ->setParameter('ids', $ids);

        $query->execute();
    }
}
