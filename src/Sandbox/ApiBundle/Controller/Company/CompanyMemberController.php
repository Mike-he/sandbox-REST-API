<?php

namespace Sandbox\ApiBundle\Controller\Company;

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
class CompanyMemberController extends CompanyController
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

        $companyMember = new CompanyMember();

        $companyMember->setUser($member);
        $companyMember->setCompany($company);

        return $companyMember;
    }
}
