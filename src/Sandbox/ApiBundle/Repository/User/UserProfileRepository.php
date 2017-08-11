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
     * @param $name
     * @return array
     */
    public function findByName($name){
        $query = $this->createQueryBuilder('u')
            ->where('u.name = :name')
            ->setParameter('name',$name);

        return $query->getQuery()->getResult();
    }
}
