<?php

namespace Sandbox\AdminApiBundle\Controller\Verify;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Controller\Company\CompanyController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Company\Company;
use Sandbox\ApiBundle\Entity\Company\CompanyVerifyRecord;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;

/**
 * Class AdminVerifyCompanyController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminVerifyCompanyController extends CompanyController
{
    /**
     * Get admin verify companies.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number"
     * )
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="query key word"
     * )
     *
     * @Route("/companies")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getVerifyCompaniesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminVerifyPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $query = $paramFetcher->get('query');

        $companies = $this->getRepo('Company\Company')->getVerifyCompanies($query);
        foreach ($companies as $company) {
            $profile = $this->getRepo('User\UserProfile')->findOneByUserId($company->getCreatorId());
            $company->setCreatorProfile($profile);
        }

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $companies,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get definite id of company.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/companies/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getVerifyCompanyAction(
        Request $request,
        $id
    ) {
        // get a company
        $company = $this->getRepo('Company\Company')->findOneById($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        // set company all info
        $this->setCompanyAllInfo($company);

        // set user profile
        $profile = $this->getRepo('User\UserProfile')->findOneByUserId($company->getCreatorId());
        $company->setCreatorProfile($profile);

        // set company verify record
        $record = $this->getRepo('Company\CompanyVerifyRecord')->getCurrentRecord($company->getId());
        $company->setCompanyVerifyRecord($record);

        // set view
        $view = new View($company);
        $view->setSerializationContext(SerializationContext::create()
            ->setGroups(array('company_info')));

        return $view;
    }

    /**
     * Banned a company.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/companies/{id}/banned")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function bannedVerifyCompanyAction(
        Request $request,
        $id
    ) {
        // get a company
        $company = $this->getRepo('Company\Company')->findOneById($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        // check company is not banned
        if ($company->getBanned()) {
            return new View();
        }

        // banned company
        $company->setBanned(true);

        $companyInfo = $this->storeCompanyInfo(
            $company
        );

        $now = new \DateTime('now');
        $record = new CompanyVerifyRecord();

        $record->setCompany($company);
        $record->setCompanyInfo($companyInfo);
        $record->setCreationDate($now);
        $record->setModificationDate($now);

        $em = $this->getDoctrine()->getManager();
        $em->persist($record);
        $em->flush();

        return new View();
    }

    /**
     * Unbanned a company.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/companies/{id}/unbanned")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function unbannedVerifyCompanyAction(
        Request $request,
        $id
    ) {
        // get a company
        $company = $this->getRepo('Company\Company')->findOneById($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        // check company is banned
        if (!$company->getBanned()) {
            return new View();
        }

        // unbanned company
        $company->setBanned(false);

        $record = $this->getRepo('Company\CompanyVerifyRecord')->getCurrentRecord($company->getId());
        $record->setStatus(CompanyVerifyRecord::STATUS_ACCEPTED);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Reject a company modification request.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/companies/{id}/rejected")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function rejectVerifyCompanyAction(
        Request $request,
        $id
    ) {
        // get a company
        $company = $this->getRepo('Company\Company')->findOneById($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        // check company is banned
        if (!$company->getBanned()) {
            return new View();
        }

        $record = $this->getRepo('Company\CompanyVerifyRecord')->getCurrentRecord($company->getId());

        // check if is updated
        if ($record->getStatus() != CompanyVerifyRecord::STATUS_UPDATED) {
            return new View();
        }

        $record->setStatus(CompanyVerifyRecord::STATUS_REJECTED);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Company $company
     *
     * @return string
     */
    private function storeCompanyInfo(
        $company
    ) {
        // get user profile
        $creatorProfile = $this->getRepo('User\UserProfile')->findOneByUserId($company->getCreatorId());
        $creatorProfileArray = array(
            'user_id' => $creatorProfile->getUserId(),
            'name' => $creatorProfile->getName(),
        );

        // get industries
        $industriesArray = array();
        $industries = $this->getRepo('Company\CompanyIndustryMap')->findByCompany($company);
        if (!empty($industries)) {
            foreach ($industries as $industry) {
                $industry = $this->getRepo('Company\CompanyIndustry')->find($industry->getIndustryId());
                $industryArray = array(
                    'id' => $industry->getId(),
                    'name' => $industry->getName(),
                );
                array_push($industriesArray, $industryArray);
            }
        }

        // get portfolios
        $portfoliosArray = array();
        $portfolios = $this->getRepo('Company\CompanyPortfolio')->findByCompany($company);
        if (!empty($portfolios)) {
            foreach ($portfolios as $portfolio) {
                $portfolioArray = array(
                    'id' => $portfolio->getId(),
                    'content' => $portfolio->getContent(),
                    'attachment_type' => $portfolio->getAttachmentType(),
                    'file_name' => $portfolio->getFileName(),
                    'preview' => $portfolio->getPreview(),
                    'size' => $portfolio->getSize(),
                );
                array_push($portfoliosArray, $portfolioArray);
            }
        }

        // get members
        $membersArray = array();
        $members = $this->getRepo('Company\CompanyMember')->getCompanyMembers($company);
        foreach ($members as &$member) {
            $memberProfile = $this->getRepo('User\UserProfile')->findOneByUserId($member->getUserId());
            $memberProfileArray = array(
                'user_id' => $memberProfile->getUserId(),
                'name' => $memberProfile->getName(),
            );
            $memberArray = array(
                'id' => $member->getId(),
                'company_id' => $member->getCompanyId(),
                'profile' => $memberProfileArray,
            );
            array_push($membersArray, $memberArray);
        }

        $companyInfo = array(
            'id' => $company->getId(),
            'name' => $company->getName(),
            'creator_profile' => $creatorProfileArray,
            'description' => $company->getDescription(),
            'address' => $company->getAddress(),
            'phone' => $company->getPhone(),
            'fax' => $company->getFax(),
            'email' => $company->getEmail(),
            'website' => $company->getWebsite(),
            'industries' => $industriesArray,
            'portfolios' => $portfoliosArray,
            'members' => $membersArray,
        );

        return json_encode($companyInfo);
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminVerifyPermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_VERIFY,
            $opLevel
        );
    }
}
