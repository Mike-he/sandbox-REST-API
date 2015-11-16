<?php

namespace Sandbox\ApiBundle\Repository\Company;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Company\CompanyVerifyRecord;

class CompanyVerifyRecordRepository extends EntityRepository
{
    /**
     * @param $companyId
     *
     * @return array
     */
    public function getCurrentRecord(
        $companyId
    ) {
        $query = $this->createQueryBuilder('r')
            ->where('r.companyId = :companyId')
            ->andWhere('r.status != :accepted')
            ->setParameter('companyId', $companyId)
            ->setParameter('accepted', CompanyVerifyRecord::STATUS_ACCEPTED);

        return $query->getQuery()->getOneOrNullResult();
    }
}
