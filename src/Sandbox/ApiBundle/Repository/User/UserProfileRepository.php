<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Company\Company;
use Sandbox\ApiBundle\Entity\User\User;

class UserProfileRepository extends EntityRepository
{
    /**
     * @param Company $company
     * @param User    $user
     */
    public function resetUserProfileCompany(
        $company,
        $user
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  UPDATE SandboxApiBundle:User\UserProfile up
                  SET up.company = NULL
                  WHERE up.company = :company
                  AND up.user = :user
                '
            )
            ->setParameter('company', $company)
            ->setParameter('user', $user);

        $query->execute();
    }

    /**
     * @param $userId
     *
     * @return array
     */
    public function findByUserId($userId)
    {
        $query = $this->createQueryBuilder('up')
            ->where('up.userId = :userId')
            ->setParameter('userId', $userId);

        return $query->getQuery()->getSingleResult();
    }

    public function findUsersByBuilding(
        $building
    ){
        $query = $this->createQueryBuilder('up')
            ->select('up.userId')
            ->where('up.building = :building')
            ->setParameter('building', $building);

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
