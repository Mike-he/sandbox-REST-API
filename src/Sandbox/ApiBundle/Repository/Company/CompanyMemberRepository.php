<?php

namespace Sandbox\ApiBundle\Repository\Company;

use Doctrine\ORM\EntityRepository;

class CompanyMemberRepository extends EntityRepository
{
    /**
     * @param array $ids
     * @param int   $companyId
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

    /**
     * @param $company
     *
     * @return array
     */
    public function getCompanyMembers(
        $company
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    SELECT cm
                    FROM SandboxApiBundle:Company\CompanyMember cm
                    LEFT JOIN SandboxApiBundle:User\User u
                    WITH cm.userId = u.id
                    WHERE
                      cm.company = :company
                      AND u.banned = FALSE
                      AND u.authorized = TRUE
                '
            )
            ->setParameter('company', $company);

        return $query->getResult();
    }
}
