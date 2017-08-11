<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyWithdrawals;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesCompanyWithdrawalPostType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Controller\Annotations;

/**
 * Admin Finance Withdrawal Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminFinanceWithdrawalController extends PaymentController
{
    /**
     * Get Wallet.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/finance/withdrawals/wallet")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getFinanceWithdrawalWalletAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminWithdrawPermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $wallet = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSalesWallet')
            ->findOneBy(['companyId' => $salesCompanyId]);
        $this->throwNotFoundIfNull($wallet, self::NOT_FOUND_MESSAGE);

        return new View($wallet);
    }

    /**
     * @param Request $request
     *
     * @Method({"POST"})
     * @Route("/finance/withdrawals")
     *
     * @return View
     */
    public function postAdminFinanceWithdrawalAction(
        Request $request
    ) {
        // check user permission
        $adminId = $this->getAdminId();
        $this->checkAdminWithdrawPermission($adminId, AdminPermission::OP_LEVEL_EDIT);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $withdrawal = new SalesCompanyWithdrawals();
        $form = $this->createForm(new SalesCompanyWithdrawalPostType(), $withdrawal);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        //check withdrawal limit
        $amount = $withdrawal->getAmount();
        $wallet = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSalesWallet')
            ->findOneBy(['companyId' => $salesCompanyId]);
        $this->throwNotFoundIfNull($wallet, self::NOT_FOUND_MESSAGE);

        $current = $wallet->getWithdrawableAmount();

        if ($amount > $current) {
            return $this->customErrorView(
                400,
                self::INSUFFICIENT_FUNDS_CODE,
                self::INSUFFICIENT_FUNDS_MESSAGE
            );
        }

        $total = $wallet->getTotalAmount();

        $wallet->setWithdrawableAmount($current - $amount);
        $wallet->setTotalAmount($total - $amount);

        $error = $this->handleWithdrawalPost(
            $company,
            $withdrawal,
            $adminId
        );

        if (!empty($error) && !is_null($error)) {
            return $this->customErrorView(
                400,
                $error['code'],
                $error['message']
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($withdrawal);
        $em->flush();

        // add log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_FINANCE,
            'logAction' => Log::ACTION_CREATE,
            'logObjectKey' => Log::OBJECT_WITHDRAWAL,
            'logObjectId' => $withdrawal->getId(),
        ));

        // set view
        $view = new View();
        $view->setStatusCode(201);
        $view->setData(array(
            'id' => $withdrawal->getId(),
        ));

        return $view;
    }

    /**
     * Get Withdrawals.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
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
     *    description="Withdrawal Status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="success_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="success_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
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
     * @Route("/finance/withdrawals")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getFinanceWithdrawalsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminWithdrawPermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        //filters
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $status = $paramFetcher->get('status');
        $successStart = $paramFetcher->get('success_start');
        $successEnd = $paramFetcher->get('success_end');
        $amountStart = $paramFetcher->get('amount_start');
        $amountEnd = $paramFetcher->get('amount_end');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $offset = ($pageIndex - 1) * $pageLimit;

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyWithdrawals')
            ->countSalesCompanyWithdrawals(
                $salesCompanyId,
                $createStart,
                $createEnd,
                $successStart,
                $successEnd,
                $amountStart,
                $amountEnd,
                $status
            );

        $withdrawals = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyWithdrawals')
            ->getSalesCompanyWithdrawals(
                $salesCompanyId,
                $createStart,
                $createEnd,
                $successStart,
                $successEnd,
                $amountStart,
                $amountEnd,
                $status,
                $pageLimit,
                $offset
            );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['sales_list']));
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $withdrawals,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * Get Withdrawal by Id.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/finance/withdrawals/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getFinanceWithdrawalByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminWithdrawPermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $withdrawal = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyWithdrawals')
            ->findOneBy([
                'id' => $id,
                'salesCompanyId' => $salesCompanyId,
            ]);

        $view = new View($withdrawal);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_detail']));

        return $view;
    }

    /**
     * Check Withdrawal Company Info.
     *
     * @param Request $request the request object
     *
     * @Route("/finance/withdrawals/check/company/profile/exist")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function checkSalesCompanyInfos(
        Request $request
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $check = true;

        $account = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileAccount')
            ->findOneBy(array('salesCompany' => $salesCompanyId));

        if (!$account) {
            $check = false;
        }

        $express = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileExpress')
            ->findOneBy(array('salesCompany' => $salesCompanyId));

        if (!$express) {
            $check = false;
        }

        $invoice = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileInvoice')
            ->findOneBy(array('salesCompany' => $salesCompanyId));

        if (!$invoice) {
            $check = false;
        }

        return new View(array('exist' => $check));
    }

    /**
     * @param SalesCompany            $company
     * @param SalesCompanyWithdrawals $withdrawal
     * @param int                     $adminId
     *
     * @return array
     */
    private function handleWithdrawalPost(
        $company,
        $withdrawal,
        $adminId
    ) {
        $financeProfileId = $withdrawal->getFinanceProfileId();
        $financeProfile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfiles')
            ->find($financeProfileId);
        if (is_null($financeProfile)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // get bank info
        $account = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileAccount')
            ->findOneBy(['profileId' => $financeProfileId]);
        if (is_null($account)) {
            return $this->setErrorArray(
                self::COMPANY_PROFILE_ACCOUNT_INCOMPLETE_CODE,
                self::COMPANY_PROFILE_ACCOUNT_INCOMPLETE_MESSAGE
            );
        }

        $companyName = $account->getSalesCompanyName();
        $bankName = $account->getBankAccountName();
        $accountNumber = $account->getBankAccountNumber();

        if (empty($companyName) || empty($bankName) || empty($accountNumber)) {
            return $this->setErrorArray(
                self::COMPANY_PROFILE_ACCOUNT_INCOMPLETE_CODE,
                self::COMPANY_PROFILE_ACCOUNT_INCOMPLETE_MESSAGE
            );
        }

        $withdrawal->setSalesCompany($company);
        $withdrawal->setSalesCompanyName($companyName);
        $withdrawal->setBankAccountName($bankName);
        $withdrawal->setBankAccountNumber($accountNumber);
        $withdrawal->setSalesAdminId($adminId);

        return [];
    }

    /**
     * @param $adminId
     * @param $level
     */
    private function checkAdminWithdrawPermission(
        $adminId,
        $level
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_WITHDRAWAL,
                ),
            ),
            $level
        );
    }
}
