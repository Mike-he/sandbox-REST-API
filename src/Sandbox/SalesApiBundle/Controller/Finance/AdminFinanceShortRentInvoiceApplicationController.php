<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Finance\FinanceShortRentInvoice;
use Sandbox\ApiBundle\Entity\Finance\FinanceShortRentInvoiceApplication;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Form\Finance\FinanceShortRentInvoiceApplicationPatchType;
use Sandbox\ApiBundle\Form\Finance\FinanceShortRentInvoiceApplicationPostType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Sales Admin Finance Short Rent Invoice Application Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminFinanceShortRentInvoiceApplicationController extends PaymentController
{
    /**
     * @param Request $request
     *
     * @Method({"POST"})
     * @Route("/applications")
     *
     * @return View
     */
    public function postShortRentInvoiceApplicationAction(
        Request $request
    ) {
        $this->checkAdminSalesInvoicePermission($this->getAdminId(), AdminPermission::OP_LEVEL_EDIT);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $application = new FinanceShortRentInvoiceApplication();
        $form = $this->createForm(new FinanceShortRentInvoiceApplicationPostType(), $application);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $error = $this->handleApplicationPost($company, $application);

        if (!empty($error) && !is_null($error)) {
            return $this->customErrorView(
                400,
                $error['code'],
                $error['message']
            );
        }

        // add log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_FINANCE,
            'logAction' => Log::ACTION_CREATE,
            'logObjectKey' => Log::OBJECT_SHORT_RENT_INVOICE_APPLICATION,
            'logObjectId' => $application->getId(),
        ));

        // set view
        $view = new View();
        $view->setStatusCode(201);
        $view->setData(array(
            'id' => $application->getId(),
        ));

        return $view;
    }

    /**
     * @param $company
     * @param FinanceShortRentInvoiceApplication $application
     */
    private function handleApplicationPost(
        $company,
        $application
    ) {
        $invoiceIds = $application->getInvoiceIds();

        if (is_null($invoiceIds) || empty($invoiceIds)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $profileId = $application->getOfficialProfileId();
        if (!is_null($profileId) && !empty($profileId)) {
            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Finance\FinanceOfficialInvoiceProfile')
                ->findOneBy([
                    'id' => $profileId,
                    'current' => true,
                ]);

            if (is_null($profile)) {
                return $this->setErrorArray(
                    self::OFFICIAL_INVOICE_PROFILE_CHANGED_CODE,
                    self::OFFICIAL_INVOICE_PROFILE_CHANGED_MESSAGE
                );
            } else {
                $application->setOfficialProfile($profile);
            }
        }

        $ids = preg_replace('/[^0-9,]/', '', $invoiceIds);
        $idArray = explode(',', $ids);

        $total = 0;
        foreach ($idArray as $id) {
            $invoice = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoice')
                ->findOneBy([
                    'companyId' => $company->getId(),
                    'id' => $id,
                    'status' => FinanceShortRentInvoice::STATUS_INCOMPLETE,
                ]);
            if (is_null($invoice)) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $total += $invoice->getAmount();
            $invoice->setStatus(FinanceShortRentInvoice::STATUS_PENDING);
        }

        $application->setAmount($total);
        $application->setInvoiceIds($ids);
        $application->setCompany($company);

        $em = $this->getDoctrine()->getManager();
        $em->persist($application);
        $em->flush();
    }

    /**
     * @param Request $request
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Annotations\QueryParam(
     *    name="amount_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="amount start"
     * )
     *
     * @Annotations\QueryParam(
     *    name="amount_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="amount end"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="create_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Status"
     * )
     *
     * @Method({"GET"})
     * @Route("/applications")
     *
     * @return View
     */
    public function getShortRentInvoiceApplicationsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->checkAdminSalesInvoicePermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $status = $paramFetcher->get('status');
        $amountStart = $paramFetcher->get('amount_start');
        $amountEnd = $paramFetcher->get('amount_end');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $offset = ($pageIndex - 1) * $pageLimit;

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoiceApplication')
            ->countShortRentInvoiceApplications(
                $createStart,
                $createEnd,
                $amountStart,
                $amountEnd,
                $status,
                $salesCompanyId
            );

        $applications = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoiceApplication')
            ->getShortRentInvoiceApplications(
                $createStart,
                $createEnd,
                $amountStart,
                $amountEnd,
                $status,
                $pageLimit,
                $offset,
                $salesCompanyId
            );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['sales_admin_list']));
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $applications,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"GET"})
     * @Route("/applications/{id}")
     *
     * @return View
     */
    public function getShortRentInvoiceApplicationByIdAction(
        Request $request,
        $id
    ) {
        $this->checkAdminSalesInvoicePermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $application = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoiceApplication')
            ->findOneBy([
                'company' => $company,
                'id' => $id,
            ]);

        $ids = $application->getInvoiceIds();
        if (is_null($ids) || empty($ids)) {
            $this->throwNotFoundIfNull($ids, self::NOT_FOUND_MESSAGE);
        }
        $idArray = explode(',', $ids);

        $invoices = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoice')
            ->getShortRentInvoicesByIds(
                $idArray,
                $salesCompanyId
            );

        $application->setInvoices($invoices);

        $view = new View($application);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['sales_admin_detail']));

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/applications/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchShortRentInvoiceApplicationAction(
        Request $request,
        $id
    ) {
        $this->checkAdminSalesInvoicePermission($this->getAdminId(), AdminPermission::OP_LEVEL_EDIT);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $application = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoiceApplication')
            ->findOneBy([
                'company' => $company,
                'id' => $id,
            ]);

        $currentStatus = $application->getStatus();
        if ($currentStatus != FinanceShortRentInvoiceApplication::STATUS_REVOKED) {
            return $this->customErrorView(
                400,
                self::SHORT_RENT_INVOICE_APPLICATION_WRONG_STATUS_CODE,
                self::SHORT_RENT_INVOICE_APPLICATION_WRONG_STATUS_MESSAGE
            );
        }

        $applicationJson = $this->container->get('serializer')->serialize($application, 'json');
        $patch = new Patch($applicationJson, $request->getContent());
        $applicationJson = $patch->apply();

        $form = $this->createForm(new FinanceShortRentInvoiceApplicationPatchType(), $application);
        $form->submit(json_decode($applicationJson, true));

        $application->setStatus(FinanceShortRentInvoiceApplication::STATUS_PENDING);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // add log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_FINANCE,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_SHORT_RENT_INVOICE_APPLICATION,
            'logObjectId' => $application->getId(),
        ));

        return new View();
    }

    /**
     * @param $adminId
     * @param $level
     */
    private function checkAdminSalesInvoicePermission(
        $adminId,
        $level
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_MONTHLY_BILLS,
                ),
            ),
            $level
        );
    }
}
