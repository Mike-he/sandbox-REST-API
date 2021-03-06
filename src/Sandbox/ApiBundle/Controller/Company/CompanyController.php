<?php

namespace Sandbox\ApiBundle\Controller\Company;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sandbox\ApiBundle\Entity\Company\Company;
use Sandbox\ApiBundle\Entity\User\User;

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
    const COMPANY_INDUSTRY_PREFIX = 'company.industry.';

    /**
     * @param Company $company
     * @param int     $userId
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
            // translate industry keys
            $industries = $this->generateCompanyIndustriesArray($industries);
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
            ->getCompanyMembers($company);

        foreach ($members as &$member) {
            $profile = $this->getRepo('User\UserProfile')
                ->findOneByUserId($member->getUserId());
            $member->setProfile($profile);
        }
        if (!is_null($members) && !empty($members)) {
            $company->setMembers($members);
        }
    }

    /**
     * @param int $userId
     *
     * @return bool
     */
    public function hasCompany($userId)
    {
        $member = $this->getRepo('Company\CompanyMember')
                       ->findOneByUserId($userId);
        if (is_null($member)) {
            return false;
        }

        return true;
    }

    /**
     * @param int $creatorId
     *
     * @return bool
     */
    public function hasCreatedCompany($creatorId)
    {
        $company = $this->getRepo('Company\Company')
            ->findOneByCreatorId($creatorId);
        if (is_null($company)) {
            return false;
        }

        return true;
    }

    /**
     * @param $userId
     * @param $company
     */
    public function setUserProfileCompany($userId, $company)
    {
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        if (is_null($userProfile)) {
            return;
        }

        $userCompany = $userProfile->getCompany();
        if (is_null($userCompany)) {
            $userProfile->setCompany($company);
        }
    }

    /**
     * @param $industries
     *
     * @return array
     */
    protected function generateCompanyIndustriesArray(
        $industries
    ) {
        if (empty($industries)) {
            return;
        }

        foreach ($industries as $industryMap) {
            if (is_null($industryMap)) {
                continue;
            }

            $industry = $industryMap->getIndustry();
            $industryKey = $industry->getKey();
            $industryTrans = $this->get('translator')->trans(self::COMPANY_INDUSTRY_PREFIX.$industryKey);
            $industry->setName($industryTrans);
        }

        return $industries;
    }
}
