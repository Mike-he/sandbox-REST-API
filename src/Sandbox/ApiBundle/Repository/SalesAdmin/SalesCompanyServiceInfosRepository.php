<?php

namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;

class SalesCompanyServiceInfosRepository extends EntityRepository
{
    /**
     * @param $company
     * @param $type
     *
     * @return null
     */
    public function getCollectionMethod(
        $company,
        $type
    ) {
        $companyService = $this->createQueryBuilder('scs')
            ->where('scs.company = :company')
            ->andWhere('scs.roomTypes = :type')
            ->setParameter('company', $company)
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();

        $result = $companyService ? $companyService->getCollectionMethod() : null;

        return $result;
    }
}
