<?php

namespace Sandbox\ApiBundle\Controller\Company;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Company\CompanyIndustryMap;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

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
    /**
     * List all company industries.
     *
     * @param Request $request the request object
     *
     *  @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Method({"GET"})
     * @Route("/industries")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getCompanyIndustriesAction(
        Request $request
    ) {
        $industries = $this->getRepo('Company\CompanyIndustry')->findAll();

        return new View($industries);
    }

    /**
     * List definite id of company industry.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Method({"GET"})
     * @Route("/industries/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getCompanyIndustryAction(
        Request $request,
        $id
    ) {
        $industry = $this->getRepo('Company\CompanyIndustry')->find($id);

        return new View($industry);
    }

    /**
     * @param $company
     * @param $industry
     *
     * @return CompanyIndustryMap
     */
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
