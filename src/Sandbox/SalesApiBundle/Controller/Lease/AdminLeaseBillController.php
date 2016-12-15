<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Form\Lease\LeaseBillPatchType;
use Sandbox\ApiBundle\Form\Lease\LeaseBillPostType;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminLeaseBillController extends SalesRestController
{
    use GenerateSerialNumberTrait;

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
            LeaseBill::STATUS_VERIFY,
            LeaseBill::STATUS_CANCELLED,
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
            SerializationContext::create()->setGroups(['lease_bill'])
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
     * Post Other Bill.
     *
     * @param Request $request
     *
     * @Route("/leases/bills")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postBillAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $bill = new LeaseBill();
        $form = $this->createForm(new LeaseBillPostType(), $bill);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $lease = $this->getDoctrine()->getRepository("SandboxApiBundle:Lease\Lease")->find($bill->getLeaseId());
        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        return $this->handleBillPost(
            $lease,
            $bill
        );
    }

    /**
     * Update Bill.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/leases/bills/{id}")
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
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $bill = $this->getDoctrine()->getRepository("SandboxApiBundle:Lease\LeaseBill")->find($id);
        $this->throwNotFoundIfNull($bill, self::NOT_FOUND_MESSAGE);

        $status = array(
            LeaseBill::STATUS_PENDING,
            LeaseBill::STATUS_UNPAID,
        );

        if (!in_array($bill->getStatus(), $status)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $billJson = $this->container->get('serializer')->serialize($bill, 'json');
        $patch = new Patch($billJson, $request->getContent());
        $billJson = $patch->apply();
        $form = $this->createForm(new LeaseBillPatchType(), $bill);
        $form->submit(json_decode($billJson, true));

        if ($bill->getStatus() != LeaseBill::STATUS_UNPAID) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $bill->setReviser($this->getUserId());

        $em = $this->getDoctrine()->getManager();
        $em->persist($bill);
        $em->flush();

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_LEASE_BILL,
            'logObjectId' => $bill->getId(),
        ));

        return new View();
    }

    /**
     * Get Unpaid Bills.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/leases/{id}/bills/unpaid")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getUnpaidBillsAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_VIEW);

        $lease = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\Lease')->find($id);
        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $status = array(
            LeaseBill::STATUS_PENDING,
            LeaseBill::STATUS_UNPAID,
        );

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBills(
                $lease,
                $status
            );

        return new View($bills);
    }

    /**
     * Batch Push Bills.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/leases/{id}/bills/batch/push")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postBatchPushAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $lease = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\Lease')->find($id);
        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $payload = json_decode($request->getContent(), true);

        $this->handleBatchPush($payload);
    }

    /**
     * @param $payloads
     */
    private function handleBatchPush(
        $payloads
    ) {
        $em = $this->getDoctrine()->getManager();

        foreach ($payloads as $payload) {
            $bill = $this->getDoctrine()
                ->getRepository("SandboxApiBundle:Lease\LeaseBill")
                ->findOneBy(
                    array(
                        'id' => $payload['id'],
                        'status' => LeaseBill::STATUS_PENDING,
                        'type' => LeaseBill::TYPE_LEASE,
                    )
                );
            if (!$bill) {
                continue;
            }
            if (!is_null($payload['revised_amount'])) {
                $bill->setRevisedAmount($payload['revised_amount']);
            }
            if (!is_null($payload['revision_note'])) {
                $bill->setRevisionNote($payload['revision_note']);
            }
            $bill->setReviser($this->getUserId());
            $bill->setStatus(LeaseBill::STATUS_UNPAID);

            $em->persist($bill);
            $em->flush();

            // generate log
            $this->generateAdminLogs(array(
                'logModule' => Log::MODULE_LEASE,
                'logAction' => Log::ACTION_EDIT,
                'logObjectKey' => Log::OBJECT_LEASE_BILL,
                'logObjectId' => $bill->getId(),
            ));
        }
    }

    /**
     * @param $lease
     * @param $bill
     *
     * @return View
     */
    private function handleBillPost(
        $lease,
        $bill
    ) {
        $serialNumber = $this->generateSerialNumber(LeaseBill::LEASE_BILL_LETTER_HEAD);
        $startDate = new \DateTime($bill->getStartDate());
        $endDate = new \DateTime($bill->getEndDate());

        $bill->setSerialNumber($serialNumber);
        $bill->setStartDate($startDate);
        $bill->setEndDate($endDate);
        $bill->setType(LeaseBill::TYPE_OTHER);
        $bill->setSendDate(new \DateTime());
        $bill->setStatus(LeaseBill::STATUS_UNPAID);
        $bill->setSender($this->getUserId());
        $bill->setLease($lease);

        $em = $this->getDoctrine()->getManager();
        $em->persist($bill);
        $em->flush();

        $response = array(
            'id' => $bill->getId(),
        );

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_CREATE,
            'logObjectKey' => Log::OBJECT_LEASE_BILL,
            'logObjectId' => $bill->getId(),
        ));

        return new View($response, 201);
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
                ['key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE],
            ],
            $opLevel
        );
    }
}
