<?php

namespace Sandbox\ApiBundle\Repository\Company;

use Doctrine\ORM\EntityRepository;

class CompanyInvitationRepository extends EntityRepository
{
    /**
     * @param $myUser
     *
     * @return array
     */
    public function getCompanyInvitations(
        $myUser
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                    SELECT ci
                    FROM SandboxApiBundle:Company\CompanyInvitation ci
                    LEFT JOIN SandboxApiBundle:Company\Company c
                    WITH ci.companyId = c.id
                    LEFT JOIN SandboxApiBundle:User\User u
                    WITH ci.askUserId = u.id
                    WHERE
                     ci.recvUserId = :myUser
                     AND u.banned = FALSE
                     AND u.authorized = TRUE
                '
            )
            ->setParameter('myUser', $myUser);

        return $query->getResult();
    }
}
