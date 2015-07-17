<?php

namespace Sandbox\ApiBundle\Repository\Company;

use Doctrine\ORM\EntityRepository;

class CompanyPortfolioRepository extends EntityRepository
{
    /**
     * @param $ids
     * @param $companyId
     */
    public function deleteCompanyPortfolios(
        $ids,
        $companyId
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:Company\CompanyPortfolio cp
                    WHERE cp.companyId = :companyId
                    AND cp.id IN (:ids)
                '
            )
            ->setParameter('ids', $ids)
            ->setParameter('companyId', $companyId);

        $query->execute();
    }
}
