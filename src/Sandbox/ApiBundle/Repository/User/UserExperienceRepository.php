<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserExperienceRepository extends EntityRepository
{
    /**
     * @param $ids
     * @param $userId
     */
    public function deleteUserExperiences(
        $ids,
        $userId
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:User\UserExperience ue
                    WHERE ue.userId = :userId
                    AND ue.id IN (:ids)
                '
            )
            ->setParameter('ids', $ids)
            ->setParameter('userId', $userId);

        $query->execute();
    }
}
