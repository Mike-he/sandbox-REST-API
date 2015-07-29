<?php

namespace Sandbox\ApiBundle\Controller\Company;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Company Controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class CompanyController extends SandboxRestController
{
    /**
     * @param int $company
     * @param int $userId
     */
    public function throwAccessDeniedIfNotCompanyCreator(
        $company,
        $userId
    ) {
        $creatorId = $company->getCreatorId();
        if ($creatorId != $userId) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }
    }

    /**
     * set company all info.
     *
     * @param $company
     *
     * return $this
     */
    public function setCompanyAllInfo(
        $company
    ) {
        // set company industries
        $industries = $this->getRepo('Company\CompanyIndustryMap')
            ->findByCompany($company);
        if (!is_null($industries) && !empty($industries)) {
            $company->setIndustries($industries);
        }

        // set company portfolios
        $portfolios = $this->getRepo('Company\CompanyPortfolio')
            ->findByCompany($company);
        if (!is_null($portfolios) && !empty($portfolios)) {
            $company->setPortfolios($portfolios);
        }

        // set company members
        $members = $this->getRepo('Company\CompanyMember')
            ->findByCompany($company);

        foreach ($members as &$member) {
            $profile = $this->getRepo('User\UserProfile')
                ->findOneByUserId($member->getUserId());
            $member->setProfile($profile);
        }
        if (!is_null($members) && !empty($members)) {
            $company->setMembers($members);
        }
    }
}
