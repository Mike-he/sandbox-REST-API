<?php

namespace Sandbox\ApiBundle\Repository\Expert;

use Doctrine\ORM\EntityRepository;

class ExpertFieldRepository extends EntityRepository
{
    public function getFields()
    {
        $query = $this->createQueryBuilder('f')
            ->select('
                        f.id,  
                        f.name                 
                ');
        $result = $query->getQuery()->getResult();

        return $result;
    }
}
