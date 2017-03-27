<?php

namespace Sandbox\AdminApiBundle\Controller\Verify;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\Company\CompanyController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Company\Company;
use Sandbox\ApiBundle\Entity\Company\CompanyVerifyRecord;
use Sandbox\ApiBundle\Form\Verify\VerifyCompanyRecordType;
use Sandbox\ApiBundle\Form\Verify\VerifyCompanyType;
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
        $this->checkAdminVerifyPermission(AdminPermission::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $query = $paramFetcher->get('query');

        $companies = $this->getRepo('Company\Company')->getVerifyCompanies($query);
        foreach ($companies as $company) {
            $profile = $this->getRepo('User\UserProfile')->findOneByUserId($company->getCreatorId());
            $company->setCreatorProfile($profile);

            // set company verify record
            $record = $this->getRepo('Company\CompanyVerifyRecord')->getCurrentRecord($company->getId());
            if (is_null($record)) {
                continue;
            }
            $recordStatusArray = array(
                'status' => $record->getStatus(),
            );
            $company->setCompanyVerifyRecord($recordStatusArray);
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
        // check user permission
        $this->checkAdminVerifyPermission(AdminPermission::OP_LEVEL_VIEW);

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
            ->setGroups(array('verify')));

        return $view;
    }

    /**
     * Modify a company status.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/companies/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throw \Exception
     */
    public function patchVerifyCompanyStatus(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminVerifyPermission(AdminPermission::OP_LEVEL_EDIT);

        // get company
        $company = $this->getRepo('Company\Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        //bind data
        $companyJson = $this->container->get('serializer')->serialize($company, 'json');
        $patch = new Patch($companyJson, $request->getContent());
        $companyJson = $patch->apply();

        $form = $this->createForm(new VerifyCompanyType(), $company);
        $form->submit(json_decode($companyJson, true));

        // update to db
        $em = $this->getDoctrine()->getManager();

        if ($company->isBanned()) {
            $this->handleVerifyCompanyBanned($company, $em);
        } else {
            $this->handleVerifyCompanyUnbanned($company);
        }

        $em->flush();

        return new View();
    }

    /**
     * Modify a company verify record status.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/companies/{id}/record")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throw \Exception
     */
    public function patchVerifyCompanyRecordStatus(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminVerifyPermission(AdminPermission::OP_LEVEL_EDIT);

        // get company
        $company = $this->getRepo('Company\Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        // check banned
        if (!$company->isBanned()) {
            return new View();
        }

        // get company record
        $record = $this->getRepo('Company\CompanyVerifyRecord')->getCurrentRecord($company->getId());
        if (is_null($record)) {
            return new View();
        }

        //bind data
        $recordJson = $this->container->get('serializer')->serialize($record, 'json');
        $patch = new Patch($recordJson, $request->getContent());
        $recordJson = $patch->apply();

        $form = $this->createForm(new VerifyCompanyRecordType(), $record);
        $form->submit(json_decode($recordJson, true));

        // update to db
        $em = $this->getDoctrine()->getManager();

        if ($record->getStatus() == CompanyVerifyRecord::STATUS_ACCEPTED) {
            $company->setBanned(false);
        } elseif ($record->getStatus() == CompanyVerifyRecord::STATUS_REJECTED) {
            $this->handleVerifyCompanyRecordReject($record);
        }

        $em->flush();

        return new View();
    }

    /**
     * Banned a company.
     *
     * @param Company $company
     * @param         $em
     */
    private function handleVerifyCompanyBanned(
        $company,
        $em
    ) {
        $companyInfo = $this->storeCompanyInfo(
            $company
        );

        $now = new \DateTime('now');
        $record = new CompanyVerifyRecord();

        $record->setCompany($company);
        $record->setCompanyInfo($companyInfo);
        $record->setCreationDate($now);
        $record->setModificationDate($now);

        $em->persist($record);
    }

    /**
     * Unbanned a company.
     *
     * @param Company $company
     */
    private function handleVerifyCompanyUnbanned(
        $company
    ) {
        $record = $this->getRepo('Company\CompanyVerifyRecord')->getCurrentRecord($company->getId());
        $record->setStatus(CompanyVerifyRecord::STATUS_ACCEPTED);
    }

    /**
     * Reject a company modification request.
     *
     * @param CompanyVerifyRecord $record
     */
    private function handleVerifyCompanyRecordReject(
        $record
    ) {
        // check if is updated
        if ($record->getStatus() != CompanyVerifyRecord::STATUS_UPDATED) {
            return;
        }

        $record->setStatus(CompanyVerifyRecord::STATUS_REJECTED);
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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_VERIFY],
            ],
            $opLevel
        );
    }
}
