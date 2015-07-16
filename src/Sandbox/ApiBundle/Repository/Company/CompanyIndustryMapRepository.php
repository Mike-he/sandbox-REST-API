<?php

namespace Sandbox\ApiBundle\Repository\Company;

use Doctrine\ORM\EntityRepository;

class CompanyIndustryMapRepository extends EntityRepository
{
    /**
     * @param $ids
     * @param $companyId
     */
    public function deleteCompanyIndustries(
        $ids,
        $companyId
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:Company\CompanyIndustryMap cim
                    WHERE cim.companyId = :companyId
                    AND cim.industryId IN (:ids)
                '
            )
            ->setParameter('companyId', $companyId)
            ->setParameter('ids', $ids);

        $query->execute();
    }
}
