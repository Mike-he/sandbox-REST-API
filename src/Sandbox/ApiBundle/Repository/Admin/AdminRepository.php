<?php

namespace Sandbox\ApiBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;

class AdminRepository extends EntityRepository
{
    /**
     * @return mixed
     */
    public function getAllAdmins()
    {
        $query = $this->createQueryBuilder('a');

        return $query->getQuery();
    }
}
