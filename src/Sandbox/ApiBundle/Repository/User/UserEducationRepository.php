<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserEducationRepository extends EntityRepository
{
    public function deleteUserEducationsByIds(
        $ids
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:User\UserEducation ue
                    WHERE
                    ue.id IN (:ids)
                ')
            ->setParameter('ids', $ids);

        $query->execute();
    }
}
