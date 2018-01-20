<?php

namespace Sandbox\AdminApiBundle\Controller\Company;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Company\CompanyController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Company\Company;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;

class AdminCompanyController extends CompanyController
{
    /**
     * Get admin verify companies.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *  @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword_search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
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
//        $this->checkAdminCompanyPermission(AdminPermission::OP_LEVEL_VIEW);

        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $companies = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Company\Company')
            ->getCompanies(
                $keyword,
                $keywordSearch,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Company\Company')
            ->countCompanies(
                $keyword,
                $keywordSearch
            );

        $companyData = array();
        foreach ($companies as $company) {
            $companyData[] = $this->getCompanyInfo($company);
        }

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $companyData,
                'total_count' => (int) $count,
            )
        );

        return $view;
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
        // check user permission
//        $this->checkAdminCompanyPermission(AdminPermission::OP_LEVEL_VIEW);

        // get a company
        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Company\Company')
            ->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $companyData = $this->getCompanyInfo($company);

        $members = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Company\CompanyMember')
            ->findBy(array('companyId' => $company->getId()));

        $memberData = array();
        foreach ($members as $member) {
            $memberData = array(
                'id' => $member->getUserId(),
                'phone' => $member->getUser()->getPhone(),
                'name' => $member->getUser()->getUserProfile()->getName(),
            );
        }

        $companyData['members'] = $memberData;

        $view = new View($companyData);
        return $view;
    }

    /**
     * @param Company $company
     *
     * @return array
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    private function getCompanyInfo(
        $company
    ) {
        $buildingData = null;
        if ($company->getBuildingId()) {
            $buildingData = array(
                'id' => $company->getBuilding()->getId(),
                'name' => $company->getBuilding()->getName(),
            );
        }

        $industries = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Company\CompanyIndustryMap')
            ->findBy(array(
                'companyId' => $company->getId(),
            ));

        $industryData = array();
        foreach ($industries as $industry) {
            $industryData[] = array(
                'id' => $industry->getIndustry()->getId(),
                'name' => $industry->getIndustry()->getName(),
            );
        }

        $portfolios = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Company\CompanyPortfolio')
            ->findBy(array(
                'companyId' => $company->getId(),
            ));

        $portfolioData = array();
        foreach ($portfolios as $portfolio) {
            $portfolioData[] = array(
                'content' => $portfolio->getContent(),
                'preview' => $portfolio->getPreview(),
            );
        }

        $creator = array(
            'id' => $company->getCreatorId(),
            'name' => $company->getCreator()->getUserProfile()->getName(),
            'phone' => $company->getCreator()->getPhone(),
        );

        $memberCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Company\CompanyMember')
            ->countCompanyMembers($company->getId());

        $result = array(
            'id' => $company->getId(),
            'name' => $company->getName(),
            'description' => $company->getDescription(),
            'address' => $company->getAddress(),
            'phone' => $company->getPhone(),
            'fax' => $company->getFax(),
            'email' => $company->getEmail(),
            'website' => $company->getWebsite(),
            'banned' => $company->isBanned(),
            'creation_date' => $company->getCreationDate(),
            'creator' => $creator,
            'portfolios' => $portfolioData,
            'industries' => $industryData,
            'building' => $buildingData,
            'member_count' => $memberCount,
        );

        return $result;
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminCompanyPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_VERIFY],
            ],
            $opLevel
        );
    }
}
