<?php

namespace Sandbox\ClientApiBundle\Controller\Company;

use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Company\CompanyController;
use Sandbox\ApiBundle\Entity\Company\Company;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Rs\Json\Patch;

/**
 * Rest controller for Companies.
 *
 * @category Sandbox
 *
 * @author   Allan SIMON <simona@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientCompanyController extends CompanyController
{
    //    const INTERNAL_SERVER_ERROR = 'Internal server error';
//
//    const MEMBER_IS_NOT_DELETE = 0;

     /**
      * Get companies.
      *
      * @param Request $request the request object
      *
      * @ApiDoc(
      *   resource = true,
      *   statusCodes = {
      *     200 = "Returned when successful"
      *   }
      * )
      *
      * @Annotations\View()
      *
      * @return array
      */
     public function getCompaniesAction(
        Request $request
    ) {
         $userId = $this->getUserId();

        //get companies
        $companies = $this->getRepo('Company\Company')->findByCreatorId($userId);

        //set view
        $view = new View($companies);
         $view->setSerializationContext(SerializationContext::create()->setGroups(array('info')));

         return   $view;
     }

    /*
     * Get nearby companies
     *
     *
     *
     * */
    public function getCompaniesNearbyAction(
        Request $request
    ) {
    }

    /*
     * Get recommend companies
     *
     * */
    public function getCompaniesRecommendAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
    }

    /*
     * Search companies
     *
     * */
    public function SearchCompanies(

    ) {
    }

    /*
     * Get a given company
     *
     * */
    public function getCompaniesIdAction(
        Request $request,
        $id
    ) {
    }

    /*
     * Create a company
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Route("/companies/")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postCompaniesAciton(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
    }

    /*
     * Edit company info
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/companies/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchCompaniesAction(
        Request $request,
        $id
    ) {
    }
}
