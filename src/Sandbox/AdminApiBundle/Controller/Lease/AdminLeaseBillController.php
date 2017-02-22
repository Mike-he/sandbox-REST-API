<?php

namespace Sandbox\AdminApiBundle\Controller\Lease;

use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\Lease\LeaseController;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentServiceBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseBillOfflineTransfer;
use Sandbox\ApiBundle\Form\Lease\LeaseBillOfflineTransferPatch;
use Sandbox\ApiBundle\Traits\FinanceTrait;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;

class AdminLeaseBillController extends LeaseController
{
    const WRONG_BILL_STATUS_CODE = 400015;
    const WRONG_BILL_STATUS_MESSAGE = 'Wrong Bill Status';

    use FinanceTrait;

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="ids",
     *     array=true
     * )
     *
     * @Route("/leases/bills/numbers")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBillsNumbersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $ids = $paramFetcher->get('ids');

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->getBillsNumbers(
                $ids
            );

        $response = array();
        foreach ($bills as $bill) {
            array_push($response, array(
                'id' => $bill->getId(),
                'bill_number' => $bill->getSerialNumber(),
                'company_name' => $bill->getLease()->getProduct()->getRoom()->getBuilding()->getCompany()->getName(),
            ));
        }

        return new View($response);
    }

    /**
     * Get Lease Bills.
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
     * @Route("/leases/{id}/bills")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_VIEW);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $lease = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\Lease')->find($id);
        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $status = array(
            LeaseBill::STATUS_UNPAID,
            LeaseBill::STATUS_PAID,
            LeaseBill::STATUS_CANCELLED,
            LeaseBill::STATUS_VERIFY,
        );

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBills(
                $lease,
                $status
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
     * @Route("/leases/bills/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getBillByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_VIEW);

        $bill = $this->getDoctrine()->getRepository("SandboxApiBundle:Lease\LeaseBill")->find($id);
        $this->throwNotFoundIfNull($bill, self::NOT_FOUND_MESSAGE);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['lease_bill']));
        $view->setData($bill);

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/leases/bills/{id}/transfer")
     * @Method({"PATCH"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function patchTransferStatusAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $bill = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findOneBy(
                array(
                    'id' => $id,
                    'payChannel' => LeaseBill::CHANNEL_OFFLINE,
                )
            );

        $this->throwNotFoundIfNull($bill, self::NOT_FOUND_MESSAGE);

        $existTransfer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBillOfflineTransfer')
            ->findOneBy(array('bill' => $id));
        $this->throwNotFoundIfNull($existTransfer, self::NOT_FOUND_MESSAGE);

        $oldStatus = $existTransfer->getTransferStatus();

        // bind data
        $transferJson = $this->container->get('serializer')->serialize($existTransfer, 'json');
        $patch = new Patch($transferJson, $request->getContent());
        $transferJson = $patch->apply();

        $form = $this->createForm(new LeaseBillOfflineTransferPatch(), $existTransfer);
        $form->submit(json_decode($transferJson, true));

        $status = $existTransfer->getTransferStatus();
        $now = new \DateTime();

        switch ($status) {
            case LeaseBillOfflineTransfer::STATUS_PAID:
                if ($oldStatus != LeaseBillOfflineTransfer::STATUS_PENDING) {
                    return $this->customErrorView(
                        400,
                        self::WRONG_BILL_STATUS_CODE,
                        self::WRONG_BILL_STATUS_MESSAGE
                    );
                }

                $bill->setStatus(LeaseBill::STATUS_PAID);
                $bill->setPaymentDate($now);

                break;
            case LeaseBillOfflineTransfer::STATUS_RETURNED:
                if ($oldStatus != LeaseBillOfflineTransfer::STATUS_PENDING) {
                    return $this->customErrorView(
                        400,
                        self::WRONG_BILL_STATUS_CODE,
                        self::WRONG_BILL_STATUS_MESSAGE
                    );
                }
                break;
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->generateLongRentServiceFee(
            $bill,
            FinanceLongRentServiceBill::TYPE_BILL_SERVICE_FEE
        );

        // add invoice amount
        $this->postConsumeBalance(
            $bill->getLease()->getDraweeId(),
            $bill->getRevisedAmount(),
            $bill->getLease()->getSerialNumber()
        );

        return new View();
    }

    /**
     * @param $opLevel
     */
    private function checkAdminLeasePermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_LEASE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_TRANSFER_CONFIRM],
            ],
            $opLevel
        );
    }
}
