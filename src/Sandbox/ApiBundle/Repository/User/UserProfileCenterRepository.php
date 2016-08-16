<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;

class UserProfileCenterRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findCenter() {
        $query = $this->createQueryBuilder('u')
            ->getQuery();

        return $query->getResult();
    }
}
