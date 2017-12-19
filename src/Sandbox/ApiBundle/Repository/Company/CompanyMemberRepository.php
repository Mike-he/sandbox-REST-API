<?php

namespace Sandbox\ApiBundle\Repository\Company;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Company\Company;

class CompanyMemberRepository extends EntityRepository
{
    /**
     * @param Company $company
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
                '
            )
            ->setParameter('company', $company);

        return $query->getResult();
    }

    public function getCompanyMembersByUser(
        $userId
    ) {
        $sql = 'SELECT cm.userId FROM company_member as cm where companyId in ((SELECT companyId from company_member where userId = '.$userId.'))';
        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        $query->execute();
        $result = $query->fetchAll();

        return $result;
    }
}
