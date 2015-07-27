<?php

namespace Sandbox\ApiBundle\Repository\Company;

use Doctrine\ORM\EntityRepository;

class CompanyMemberRepository extends EntityRepository
{
    /**
     * @param $ids
     * @param $companyId
     */
    public function deleteCompanyMembers(
        $ids,
        $companyId
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    DELETE FROM SandboxApiBundle:Company\CompanyMember cm
                    WHERE cm.companyId = :companyId
                    AND cm.id IN (:ids)
                '
            )
            ->setParameter('ids', $ids)
            ->setParameter('companyId', $companyId);

        $query->execute();
    }
}
