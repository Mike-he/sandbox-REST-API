<?php

namespace Sandbox\ApiBundle\Controller\Company;

use Sandbox\ApiBundle\Entity\Company\CompanyPortfolio;
use Sandbox\ApiBundle\Form\Company\CompanyPortfolioType;

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
class CompanyPortfolioController extends CompanyController
{
    /**
     * @param $company
     * @param $portfolio
     */
    protected function generateCompanyPortfolio(
        $company,
        $portfolio
    ) {
        $companyPortfolio = new CompanyPortfolio();

        $form = $this->createForm(new CompanyPortfolioType(), $companyPortfolio);
        $form->submit($portfolio);

        $time = new \DateTime('now');
        $companyPortfolio->setCreationDate($time);
        $companyPortfolio->setModificationDate($time);
        $companyPortfolio->setCompany($company);

        return $companyPortfolio;
    }
}
