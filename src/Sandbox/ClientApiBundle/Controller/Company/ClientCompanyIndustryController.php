<?php

namespace Sandbox\ClientApiBundle\Controller\Company;

use Sandbox\ApiBundle\Controller\Company\CompanyIndustryController;
use Sandbox\ApiBundle\Entity\Company\CompanyIndustryMap;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\Serializer\SerializationContext;

/**
 * Rest controller for CompanyIndustryMap.
 *
 * @category Sandbox
 *
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 * @Route("/profile")
 */
class ClientCompanyIndustryController extends CompanyIndustryController
{
    /**
     * Get Company's industries.
     *
     * @param Request $request the request object
     * @param int     $id      id of the company
     *
     * @Annotations\QueryParam(
     *    name="company_id",
     *    default=null,
     *    description="companyId"
     * )
     *
     *
     * @Get("/companies/{id}/industries")
     *
     * @return array
     */
    public function getIndustriesAction(
        Request $request,
        $id
    ) {
        die('HolyShit!!!');
        $companyId = $paramFetcher->get('company_id');
        if (is_null($companyId)) {
            $companyId = $this->getCompanyId();
        }

        $company = $this->getRepo('Company\Company')->find($companyId);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $view = new View($company->getIndustries());
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('profile')));

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Route("/industries")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postCompanyIndustryAction(
        Request $request,
        ParamFetcherInterface $paramFetcher

    ) {
        $companyId = $this->getCompanyId();
        $company = $this->getRepo('Company\Company')->find($companyId);

        $em = $this->getDoctrine()->getManager();

        $industryIds = json_decode($request->getContent(), true);
        foreach ($industryIds as $industryId) {
            $industry = $this->getRepo('Company\CompanyIndustry')->find($industryId);
            if (is_null($industry)) {
                continue;
            }

            $industryMap = $this->getRepo('Company\CompanyIndustryMap')->findOneBy(array(
                'company' => $company,
                'industry' => $industry,
            ));
            if (!is_null($industryMap)) {
                continue;
            }

            $companyIndustryMap = $this->generateCompanyIndustryMap($company, $industry);
            $em->persist($companyIndustryMap);
        }

        $em->flush();

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    strict=true,
     *    description=""
     * )
     *
     * @Route("/industries")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteCompanyIndustryAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->getRepo('Company\CompanyIndustryMap')->deleteCompanyIndustries(
            $paramFetcher->get('id'),
            $this->getCompanyId()
        );

        return new View();
    }
}
