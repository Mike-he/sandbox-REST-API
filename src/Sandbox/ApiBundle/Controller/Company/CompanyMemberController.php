<?php

namespace Sandbox\ApiBundle\Controller\Company;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Company\CompanyMember;

/**
 * Company Member Controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class CompanyMemberController extends SandboxRestController
{
    /**
     * @param $company
     * @param $memberId
     *
     * @return CompanyMember
     */
    protected function generateCompanyMember(
        $companyId,
        $memberId
    ) {
        $company = $this->getRepo('Company\Company')->find($companyId);
        $member = $this->getRepo('User\User')->find($memberId);

        $now = new \DateTime('now');

        $companyMember = new CompanyMember();

        $companyMember->setCreationDate($now);
        $companyMember->setModificationDate($now);
        $companyMember->setUser($member);
        $companyMember->setCompany($company);

        return $companyMember;
    }
}
