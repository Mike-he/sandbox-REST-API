<?php

namespace Sandbox\ClientApiBundle\Controller\Company;

use Sandbox\ApiBundle\Controller\Company\CompanyIndustryController;
use Sandbox\ApiBundle\Entity\Company\CompanyIndustryMap;
use Sandbox\ApiBundle\Entity\Company\Company;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
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
 */
class ClientCompanyIndustryController extends CompanyIndustryController
{
    /**
     * Get Company's industries.
     *
     * @param Request $request contains request info
     * @param int     $id      id of the company
     *
     * @Get("/companies/{id}/industries")
     *
     * @return array
     */
    public function getIndustriesAction(
        Request $request,
        $id
    ) {
        $industries = $this->getRepo('Company\CompanyIndustryMap')->findByCompanyId($id);
        $this->throwNotFoundIfNull($industries, self::NOT_FOUND_MESSAGE);

        $view = new View($industries);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('company_industry')));

        return $view;
    }

    /**
     * add industries.
     *
     * @param Request $request
     * @param int     $id
     *
     *
     * @POST("/companies/{id}/industries")
     *
     * @return View
     */
    public function postCompanyIndustryAction(
        Request $request,
        $id
    ) {
        $company = $this->getRepo('Company\Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        // check user is allowed to modify
        $userId = $this->getUserId();
        $this->throwAccessDeniedIfNotCompanyCreator($company, $userId);

        //add industries
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

        return new view();
    }

    /**
     * delete industries.
     *
     * @param $id
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
     * @Delete("/companies/{id}/industries")
     *
     * @return View
     */
    public function deleteCompanyIndustriesAction(
        $id,
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user is allowed to modify
        $company = $this->getRepo('Company\Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);
        $userId = $this->getUserId();
        $this->throwAccessDeniedIfNotCompanyCreator($company, $userId);

        //delete industry
        $this->getRepo('Company\CompanyIndustryMap')->deleteCompanyIndustries(
            $paramFetcher->get('id'),
            $id
        );

        return new View();
    }
}
