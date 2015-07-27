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
      * @return View
      */
     public function getCompaniesAction(
        Request $request
    ) {
         $userId = $this->getUserId();
        //TODO 改成所有companyMember都能获取公司
        //get companies
        $companies = $this->getRepo('Company\Company')
                          ->findByCreatorId($userId);

        //set view
        $view = new View($companies);
         $view->setSerializationContext(SerializationContext::create()
             ->setGroups(array('company_basic')));

         return $view;
     }

    /**
     * Get nearby companies.
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Offset of page"
     * )
     *
     * @return View
     */
    public function getCompaniesNearbyAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // set max limit
        if ($limit > $globals['load_more_limit']) {
            $limit = $globals['load_more_limit'];
        }

        // get my profile
        $myProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $this->throwNotFoundIfNull($myProfile, self::NOT_FOUND_MESSAGE);

        // get my building
        $buildingId = $myProfile->getBuildingId();
        if (is_null($buildingId)) {
            return $this->customErrorView(
                400,
                self::ERROR_BUILDING_NOT_SET_CODE,
                self::ERROR_BUILDING_NOT_SET_MESSAGE
            );
        }

        // get my profile
        $myBuilding = $this->getRepo('Room\RoomBuilding')->findOneById($buildingId);
        $this->throwNotFoundIfNull($myBuilding, self::NOT_FOUND_MESSAGE);

        // find nearby members
        $companies = $this->getRepo('Company\Company')->findNearbyCompanies(
            $myBuilding->getLat(),
            $myBuilding->getLng(),
            $limit,
            $offset
        );

        // set view
        $view = new View($companies);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('company_info'))
        );

        return $view;
    }

    /**
     * Get recommend companies.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     */
    public function getCompaniesRecommendAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
    }

    /**
     * Search companies.
     */
    public function SearchCompanies(

    ) {
    }

    /**
     * Get a given company.
     *
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function getCompanyAction(
        Request $request,
        $id
    ) {
        //get a company
        $company = $this->getRepo('Company\Company')->findById($id);

        //set view
        $view = new View($company);
        $view->setSerializationContext(SerializationContext::create()
             ->setGroups(array('company_info')));

        return   $view;
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
        $em = $this->getDoctrine()->getManager();
        $userId = $this->getUserId();

        $company = new Company();

        $form = $this->createForm(new CompanyType(), $company);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getRepo('User\User')->find($userId);
            $company->setCreator($user);
            $time = new \DateTime('now');
            $company->setCreationDate($time);
            $company->setModificationDate($time);

            // save to db
            $em->persist($company);
            $em->flush();

            // set view
            $view = new View();
            $view->setData(
                array('id' => $company->getId())
            );

            return $view;
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Edit company info.
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

        //get company Entity
        $company = $this->getRepo('Company\Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        //TODO check user is allowed to modify
//    if ($creatorId != $userId) {
//        // if user is not the creator of this company
//        // return error
//        throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
//    }

        // bind data
        $companyJson = $this->container
                                ->get('serializer')
                                ->serialize($company, 'json');
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
}
