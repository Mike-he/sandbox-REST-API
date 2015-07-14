<?php

namespace Sandbox\ClientApiBundle\Controller\Company;

use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Company\CompanyController;
use Sandbox\ApiBundle\Entity\Company\Company;
use Sandbox\ApiBundle\Form\Company\CompanyType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Rs\Json\Patch;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Rest controller for Companies.
 *
 * @category Sandbox
 *
 * @author   Albert Feng <albert.feng@easylinks.com.cn>
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
    public function postCompanyAction(
        Request $request
    ) {
        $userId = $this->getUserId();

        $company = new Company();

        $form = $this->createForm(new CompanyType(), $company);
        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->handlePostCompany($company);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
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
        //TODO check user is vip


        $userId = $this->getUserId();

        //get company Entity
        $company = $this->getRepo('Company\Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);
        // get company creator ID
        $creatorId = $company->getCreatorId();
        // check user is allowed to modify
//        if ($creatorId != $userId) {
//            // if user is not the creator of this company
//            // return error
//            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
//        }

//        die('holyshit!again!');

        // bind data
        $companyJson = $this->container->get('serializer')->serialize($company, 'json');

        $patch = new Patch($companyJson, $request->getContent());

        $companyPatchJson = $patch->apply();

        $form = $this->createForm(new CompanyType(), $company);
        $form->submit(json_decode($companyPatchJson, true));

        // update company modification date
        $company->setModificationDate(new \DateTime('now'));

        // update to db
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    private function handlePostCompany(
        $company
    ) {
        $em = $this->getDoctrine()->getManager();

        // set company
        $company->setCompany($company);

        // industry
        $industryIds = $company->getIndustryIds();
        if (!is_null($industryIds) && !empty($industryIds)) {
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
        }

        // portfolio
        $portfolios = $companyProfile->getPortfolios();
        if (!is_null($portfolios) && !empty($portfolios)) {
            foreach ($portfolios as $portfolio) {
                $companyPortfolio = $this->generateCompanyPortfolio($company, $portfolio);
                $em->persist($companyPortfolio);
            }
        }

        // save to db
        $em->persist($companyProfile);
        $em->flush();

        // set view
        $view = new View();
        $view->setData(
            array('id' => $companyProfile->getId())
        );

        return $view;
    }
}
