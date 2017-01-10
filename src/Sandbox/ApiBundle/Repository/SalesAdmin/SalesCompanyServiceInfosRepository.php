<?php

namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;

class SalesCompanyServiceInfosRepository extends EntityRepository
{
    /**
     * @param $company
     * @param $type
     *
     * @return mixed
     */
    public function getCollectionMethod(
        $company,
        $type
    ) {
        $companyService = $this->createQueryBuilder('scs')
            ->where('scs.company = :company')
            ->andWhere('scs.roomTypes = :type')
            ->andWhere('scs.status = :status')
            ->setParameter('company', $company)
            ->setParameter('type', $type)
            ->setParameter('status', true)
            ->getQuery()
            ->getOneOrNullResult();

        $result = $companyService ? $companyService->getCollectionMethod() : null;

        return $result;
    }
}
