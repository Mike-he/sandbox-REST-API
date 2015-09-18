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
     * @param int $companyId
     *
     * @return bool
     */
    public function isCompanyMember(
        $userId,
        $companyId
    ) {
        $member = $this->getRepo('Company\CompanyMember')
            ->findOneBy(array(
                'companyId' => $companyId,
                'userId' => $userId,
            ));
        if (is_null($member)) {
            return false;
        }

        return true;
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
     * @param $companyId
     *
     * @return bool
     */
    public function isCompanyCreator($userId, $companyId)
    {
        $company = $this->getRepo('Company\Company')->findOneBy(array(
            'id' => $companyId,
            'creatorId' => $userId,
        ));
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
}
