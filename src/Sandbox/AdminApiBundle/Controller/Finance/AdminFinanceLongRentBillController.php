<?php

namespace Sandbox\AdminApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentBill;
use Sandbox\ApiBundle\Form\Finance\FinanceBillPatchType;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;

/**
 * Admin Finance Long Rent Bill Controller.
 */
class AdminFinanceLongRentBillController extends SandboxRestController
{
    /**
     * Get Long Rent Bills.
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
     * @Annotations\QueryParam(
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
     *    name="status",
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="company_id",
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by company id"
     * )
     *
     *
     * @Route("/finance/long/rent/bills")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getFinanceBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminFinanceLongRentBillPermission(AdminPermission::OP_LEVEL_VIEW);

        //filters
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $status = $paramFetcher->get('status');
        $company = $paramFetcher->get('company_id');

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceLongRentBill')
            ->getBillLists(
                $company,
                $status,
                $createStart,
                $createEnd
            );

        $bills = $this->get('serializer')->serialize(
            $bills,
            'json',
            SerializationContext::create()->setGroups(['main'])
        );
        $bills = json_decode($bills, true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $bills,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get bill info.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/finance/long/rent/bills/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getFinanceBillByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminFinanceLongRentBillPermission(AdminPermission::OP_LEVEL_VIEW);

        $bill = $this->getDoctrine()->getRepository('SandboxApiBundle:Finance\FinanceLongRentBill')->find($id);
        $this->throwNotFoundIfNull($bill, self::NOT_FOUND_MESSAGE);

        $billInvoice = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceBillInvoiceInfo')
            ->findOneBy(array('bill' => $bill));

        $bill->setBillInvoice($billInvoice);

        $view = new View();
        $view->setData($bill);

        return $view;
    }

    /**
     * Patch Bill.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/finance/long/rent/bills/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchBillAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminFinanceLongRentBillPermission(AdminPermission::OP_LEVEL_EDIT);

        $bill = $this->getDoctrine()->getRepository('SandboxApiBundle:Finance\FinanceLongRentBill')->find($id);
        $this->throwNotFoundIfNull($bill, self::NOT_FOUND_MESSAGE);

        $oldStatus = $bill->getStatus();

        if ($oldStatus != FinanceLongRentBill::STATUS_PENDING) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_FINANCE_BILL_STATUS_NOT_CORRECT_CODE,
                CustomErrorMessagesConstants::ERROR_FINANCE_BILL_STATUS_NOT_CORRECT_MESSAGE
            );
        }

        $billJson = $this->container->get('serializer')->serialize($bill, 'json');
        $patch = new Patch($billJson, $request->getContent());
        $billJson = $patch->apply();
        $form = $this->createForm(new FinanceBillPatchType(), $bill);
        $form->submit(json_decode($billJson, true));

        if ($bill->getStatus() != FinanceLongRentBill::STATUS_PAID) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_FINANCE_BILLS_PAYLOAD_FORMAT_NOT_CORRECT_CODE,
                CustomErrorMessagesConstants::ERROR_FINANCE_BILLS_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($bill);
        $em->flush();

        return new View();
    }

    /**
     * @param $opLevel
     */
    private function checkAdminFinanceLongRentBillPermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_SERVICE_RECEIPT],
            ],
            $opLevel
        );
    }
}
