<?php

namespace Sandbox\AdminApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Finance\FinanceSalesWalletFlow;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyWithdrawals;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesCompanyWithdrawalPatchType;
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
     * @Annotations\QueryParam(
     *    name="company_id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
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
        $salesCompanyId = $paramFetcher->get('company_id');

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
        $view->setSerializationContext(SerializationContext::create()->setGroups(['official_list']));
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

        $withdrawal = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyWithdrawals')
            ->find($id);

        $view = new View($withdrawal);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_detail']));

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/finance/withdrawals/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchFinanceWithdrawalAction(
        Request $request,
        $id
    ) {
        // check user permission
        $adminId = $this->getAdminId();
        $this->checkAdminWithdrawPermission($adminId, AdminPermission::OP_LEVEL_EDIT);

        $withdrawal = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyWithdrawals')
            ->find($id);
        $this->throwNotFoundIfNull($withdrawal, self::NOT_FOUND_MESSAGE);

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $withdrawal->getSalesCompanyId(),
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $oldStatus = $withdrawal->getStatus();
        if ($oldStatus != SalesCompanyWithdrawals::STATUS_PENDING) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $withdrawalJson = $this->container->get('serializer')->serialize($withdrawal, 'json');
        $patch = new Patch($withdrawalJson, $request->getContent());
        $withdrawalJson = $patch->apply();

        $form = $this->createForm(new SalesCompanyWithdrawalPatchType(), $withdrawal);
        $form->submit(json_decode($withdrawalJson, true));

        $status = $withdrawal->getStatus();
        $now = new \DateTime();
        if ($status == SalesCompanyWithdrawals::STATUS_SUCCESSFUL) {
            $withdrawal->setSuccessTime($now);
        } elseif ($status == SalesCompanyWithdrawals::STATUS_FAILED) {
            $wallet = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Finance\FinanceSalesWallet')
                ->findOneBy([
                    'companyId' => $company,
                ]);
            $this->throwNotFoundIfNull($wallet, self::NOT_FOUND_MESSAGE);

            $current = $wallet->getWithdrawableAmount();
            $total = $wallet->getTotalAmount();
            $amount = $withdrawal->getAmount();

            $wallet->setWithdrawableAmount($current + $amount);
            $wallet->setTotalAmount($total + $amount);

            $withdrawal->setFailureTime($now);

            $this->get('sandbox_api.sales_wallet')->generateSalesWalletFlows(
                FinanceSalesWalletFlow::WITHDRAW_FAILED_AMOUNT,
                "+$amount",
                $company->getId()
            );
        }

        $withdrawal->setOfficialAdminId($adminId);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
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
                    'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_WITHDRAWAL,
                ),
            ),
            $level
        );
    }
}
