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
    const ERROR_AMOUNT_OVER_SET_CODE = 400001;
    const ERROR_AMOUNT_OVER_SET_MESSAGE = 'Sorry, only add 8 portfolios in total!';

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
        $portfolios = $this->getRepo('Company\CompanyPortfolio')->findByCompanyId($id);
        $this->throwNotFoundIfNull($portfolios, self::NOT_FOUND_MESSAGE);

        $view = new View($portfolios);
        $view->setSerializationContext(
                    SerializationContext::create()
                        ->setGroups(array('company_portfolio')));

        return $view;
    }

    /**
     * add portfolios.
     *
     * @param Request $request
     * @param int     $id
     *
     * @POST("/companies/{id}/portfolios")
     *
     * @return View
     */
    public function postCompanyPortfolioAction(
        Request $request,
        $id
    ) {
        // check user is allowed to modify
        $company = $this->getRepo('Company\Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);
        $userId = $this->getUserId();
        $this->throwAccessDeniedIfNotCompanyCreator($company, $userId);

        // check the amount of portfolios has added
        $count = $this->getRepo('Company\CompanyPortfolio')
                      ->countCompanyPortfolios($id);
        $portfolios = json_decode($request->getContent(), true);
        $countPort = sizeof($portfolios);
        $amountPortfolios = 8;
        if ($countPort + $count > $amountPortfolios) {
            return $this->customErrorView(
                400,
                self::ERROR_AMOUNT_OVER_SET_CODE,
                self::ERROR_AMOUNT_OVER_SET_MESSAGE
            );
        }

        // add portfolios
        $em = $this->getDoctrine()->getManager();
        $company = $this->getRepo('Company\Company')->find($id);

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
        // check user is allowed to modify
        $company = $this->getRepo('Company\Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);
        $userId = $this->getUserId();
        $this->throwAccessDeniedIfNotCompanyCreator($company, $userId);

        //delete portfolios
        $this->getRepo('Company\CompanyPortfolio')->deleteCompanyPortfolios(
            $paramFetcher->get('id'),
            $id
        );

        return new View();
    }
}
