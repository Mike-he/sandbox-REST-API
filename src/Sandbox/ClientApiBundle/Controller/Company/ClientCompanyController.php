<?php

namespace Sandbox\ClientApiBundle\Controller\Company;

use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Company\CompanyController;
use Sandbox\ApiBundle\Entity\Random\ClientRandomRecord;
use Sandbox\ApiBundle\Entity\Company\Company;
use Sandbox\ApiBundle\Entity\Company\CompanyMember;
use Sandbox\ApiBundle\Form\Company\CompanyType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Rs\Json\Patch;

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
    //const ERROR_NOT_VIP_SET_CODE = 400001;
    //const ERROR_NOT_VIP_SET_MESSAGE = 'You are not VIP!';
    const ERROR_HAVE_COMPANY_SET_CODE = 400002;
    const ERROR_HAVE_COMPANY_SET_MESSAGE = 'You have create a company yet!';

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

        //get companies
        $members = $this->getRepo('Company\CompanyMember')
                       ->findByUserId($userId);
        if (is_null($members)) {
            return new View(array());
        }

        $companies = array();
        foreach ($members as $member) {
            $company = $member->getCompany();
            //set company all info
            $this->setCompanyAllInfo($company);
            $companies[] = $company;
        }

        //set view
        $view = new View($companies);
        $view->setSerializationContext(SerializationContext::create()
             ->setGroups(array('company_info')));

        return $view;
    }

    /**
     * Get nearby companies.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
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
        // check user is auth
        if (is_null($this->getCardNoIfUserAuthorized())) {
            return new View(array());
        }

        $userId = $this->getUserId();

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // get max limit
        $limit = $this->getLoadMoreLimit($limit);

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

        // find nearby companies
        $companies = $this->getRepo('Company\Company')->findNearbyCompanies(
            $myBuilding->getLat(),
            $myBuilding->getLng(),
            $limit,
            $offset,
            $globals['nearby_range_km']
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
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
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
     * @Annotations\QueryParam(
     *    name="industry_id",
     *    array=true,
     *    strict=true,
     *    description=""
     * )
     *
     * @return View
     */
    public function getCompaniesRecommendAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user is auth
        if (is_null($this->getCardNoIfUserAuthorized())) {
            return new View(array());
        }

        $userId = $this->getUserId();
        $clientId = $this->getUser()->getClientId();

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $industryIds = $paramFetcher->get('industry_id');

        // get max limit
        $limit = $this->getLoadMoreLimit($limit);

        $em = $this->getDoctrine()->getManager();

        // get user's retrieved company IDs if any
        $myRecords = $this->getRepo('Random\ClientRandomRecord')
            ->findBy(array(
                'userId' => $userId,
                'clientId' => $clientId,
                'entityName' => 'company',
            ));

        $recordIds = array();

        foreach ($myRecords as $myRecord) {
            // if offset is not provided, means user is trying to reload the page
            // then we should remove user's retrieval records
            // otherwise, we should exclude these records for the next page
            if (is_null($offset) || $offset <= 0) {
                $em->remove($myRecord);
            } else {
                array_push($recordIds, $myRecord->getEntityId());
            }
        }

        // find random companies
        $companies = $this->getRepo('Company\Company')->findRandomCompanies(
            $recordIds,
            $industryIds,
            $limit
        );
        if (is_null($companies) || empty($companies)) {
            return new View(array());
        }

        // save random records
        foreach ($companies as $company) {
            $companyId = $company->getId();

            // add user's retrieval record
            $randomRecord = new ClientRandomRecord();
            $randomRecord->setUserId($userId);
            $randomRecord->setClientId($clientId);
            $randomRecord->setEntityId($companyId);
            $randomRecord->setEntityName('company');
            $em->persist($randomRecord);
        }

        $em->flush();

        // set view
        $view = new View($companies);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('company_limit'))
        );

        return $view;
    }

    /**
     * Search companies.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
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
     * @Annotations\QueryParam(
     *    name="query",
     *    default=null,
     *    description="search query"
     * )
     *
     * @return View
     */
    public function getCompaniesSearchAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user is auth
        if (is_null($this->getCardNoIfUserAuthorized())) {
            return new View(array());
        }

        $query = $paramFetcher->get('query');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get max limit
        $limit = $this->getLoadMoreLimit($limit);

        // find all companies who have the query in any of their mapped fields
        $finder = $this->container->get('fos_elastica.finder.search.company');

        $multiMatchQuery = new \Elastica\Query\MultiMatch();

        $multiMatchQuery->setQuery($query);
        $multiMatchQuery->setType('phrase_prefix');
        $multiMatchQuery->setFields(array('name'));

        $results = $finder->find($multiMatchQuery);
        if (is_null($results) || empty($results)) {
            return new View(array());
        }

        $companies = array_slice($results, $offset, $limit);

        // set view
        $view = new View($companies);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('company_limit'))
        );

        return $view;
    }

    /**
     * Get a given company all info.
     *
     * @param Request $request
     * @param $id
     *
     * @Get("/companies/{id}/all")
     *
     * @return View
     */
    public function getCompanyAllAction(
        Request $request,
        $id
    ) {
        // get a company
        $company = $this->getRepo('Company\Company')->findOneById($id);

        $viewGroup = 'company_info';

//        <--------for the future
//        $userId = $this->getUserId();
//        $creatorId = $company->getCreatorId();
//        $creatorVip = $this->getVipStatusByUserId($creatorId);
//        // check user is VIP
//        if (is_null($creatorVip)) {
//            // check user is company member
//            if (!$this->isCompanyMember($userId, $id)) {
//                $viewGroup = 'company_limit';
//            }
//        };
//        --------------------------->

        // set company all info
        $this->setCompanyAllInfo($company);

        // set view
        $view = new View($company);
        $view->setSerializationContext(SerializationContext::create()
             ->setGroups(array($viewGroup)));

        return   $view;
    }

    /**
     * get a given company basic info.
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
        // get a company
        $company = $this->getRepo('Company\Company')->findOneById($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $viewGroup = 'company_info';

//        <--------for the future
//        $userId = $this->getUserId();
//        $creatorId = $company->getCreatorId();
//        $creatorVip = $this->getVipStatusByUserId($creatorId);
//
//        // check user is VIP
//        if (is_null($creatorVip)) {
//            // check user is company member
//            if (!$this->isCompanyMember($userId, $id)) {
//                $viewGroup = 'company_limit';
//            }
//        };
//        --------------------------->

        // set view
        $view = new View($company);
        $view->setSerializationContext(SerializationContext::create()
             ->setGroups(array($viewGroup)));

        return   $view;
    }

    /**
     * Create a company.
     *
     * @param Request $request
     *
     * @Route("/companies/")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postCompanyAction(
        Request $request
    ) {
        //<--------for the future
//          //check user is VIP
//        if (is_null($this->getExpireDateIfUserVIP())) {
//            return $this->customErrorView(
//                400,
//                self::ERROR_NOT_VIP_SET_CODE,
//                self::ERROR_NOT_VIP_SET_MESSAGE
//            );
//        }
//        --------------------------->

        // check user has created a company
        $userId = $this->getUserId();

        if ($this->hasCreatedCompany($userId)) {
            return $this->customErrorView(
                400,
                self::ERROR_HAVE_COMPANY_SET_CODE,
                self::ERROR_HAVE_COMPANY_SET_MESSAGE
            );
        }

        // create a company
        $em = $this->getDoctrine()->getManager();

        $company = new Company();

        $form = $this->createForm(new CompanyType(), $company);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getRepo('User\User')->find($userId);
            $company->setCreator($user);

            //add member
            $member = new CompanyMember();

            $member->setCompany($company);
            $member->setUser($user);

            // update user profile's company
            $this->setUserProfileCompany($userId, $company);

            // save to db
            $em->persist($company);
            $em->persist($member);
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
        //<--------for the future
//          //check user is VIP
//        if (is_null($this->getExpireDateIfUserVIP())) {
//            return $this->customErrorView(
//                400,
//                self::ERROR_NOT_VIP_SET_CODE,
//                self::ERROR_NOT_VIP_SET_MESSAGE
//            );
//        }
//        --------------------------->

        //get company Entity
        $company = $this->getRepo('Company\Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        // check the user is allowed to modify
        $userId = $this->getUserId();
        $this->throwAccessDeniedIfNotCompanyCreator($company, $userId);

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

    /**
     * delete company.
     *
     * @param int $id
     *
     * @Route("/companies/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteCompanyAction($id)
    {
        // get company Entity
        $company = $this->getRepo('Company\Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        // check the user is allowed to delete
        $userId = $this->getUserId();
        $this->throwAccessDeniedIfNotCompanyCreator($company, $userId);

        // quit my company
        $em = $this->getDoctrine()->getManager();
        $em->remove($company);
        $em->flush();

        return new View();
    }
}
