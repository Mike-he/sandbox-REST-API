<?php

namespace Sandbox\ClientApiBundle\Controller\Company;

use Sandbox\ApiBundle\Controller\Company\CompanyPortfolioController;
use Sandbox\ApiBundle\Entity\Company\CompanyPortfolio;
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
 * Rest controller for CompanyPortfolio.
 *
 * @category Sandbox
 *
 * @author   Albert Feng
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientCompanyPortfolioController extends CompanyPortfolioController
{
    /**
     * Get Company's portfolios.
     *
     * @param Request $request contains request info
     * @param int     $id      id of the company
     *
     * @Get("/companies/{id}/portfolios")
     *
     * @return array
     */
    public function getPortfoliosAction(
        Request $request,
        $id
    ) {
        $company = $this->getRepo('Company\Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $view = new View($company->getPortfolios());
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('portfolio')));

        return $view;
    }

    /**
     * add portfolios.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @POST("/companies/{id}/portfolios")
     *
     * @return View
     */
    public function postCompanyPortfolioAction(
        Request $request,
        $id
    ) {
        $em = $this->getDoctrine()->getManager();

        $company = $this->getRepo('Company\Company')->find($id);

        $portfolios = json_decode($request->getContent(), true);
        foreach ($portfolios as $portfolio) {
            $companyPortfolio = $this->generateCompanyPortfolio($company, $portfolio);
            $em->persist($companyPortfolio);
        }

        $em->flush();

        return new view();
    }

    /**
     * delete portfolios.
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
     * @Delete("/companies/{id}/portfolios")
     *
     * @return View
     */
    public function deleteCompanyPortfoliosAction(
        $id,
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        //TODO check user‘s auth
        $this->getRepo('Company\CompanyPortfolio')->deleteCompanyPortfolios(
            $paramFetcher->get('id'),
            $id
        );

        return new View();
    }
}
