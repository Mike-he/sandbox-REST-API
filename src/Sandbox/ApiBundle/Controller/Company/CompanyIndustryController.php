<?php

namespace Sandbox\ApiBundle\Controller\Company;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Company\CompanyIndustryMap;

/**
 * Company Industry Controller.
 *
 * @category Sandbox
 *
 * @author   Albert Feng <albert.feng@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class CompanyIndustryController extends SandboxRestController
{
    protected function generateCompanyIndustryMap(
        $company,
        $industry
    ) {
        $CompanyIndustryMap = new CompanyIndustryMap();

        $CompanyIndustryMap->setCompany($company);
        $CompanyIndustryMap->setIndustry($industry);
        $CompanyIndustryMap->setCreationDate(new \DateTime('now'));

        return $CompanyIndustryMap;
    }
}
