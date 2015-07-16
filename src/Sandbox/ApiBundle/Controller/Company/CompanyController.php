<?php

namespace Sandbox\ApiBundle\Controller\Company;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Company\CompanyPortfolio;
use Sandbox\ApiBundle\Form\Company\CompanyPortfolioType;

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
    protected function generateCompanyPortfolio(
        $company,
        $portfolio
    ) {
        $companyPortfolio = new CompanyPortfolio();

        $form = $this->createForm(new CompanyPortfolioType(), $companyPortfolio);
        $form->submit($portfolio);

        $companyPortfolio->setCompany($company);

        return $companyPortfolio;
    }
}
