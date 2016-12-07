<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use Knp\Component\Pager\Paginator;
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

class LeaseBillController extends SalesRestController
{
    use GenerateSerialNumberTrait;

    const LEASE_BILL_LETTER_HEAD = 'P';

    /**
     * Get Room Buildings.
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
     *    name="lease_id",
     *    default=null,
     *    nullable=false,
     *    description="lease id"
     * )
     *
     * @Route("/leases/bills")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $leaseId = $paramFetcher->get('lease_id');

        $lease = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\Lease')->find($leaseId);
        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBills(
                $lease
            );

        foreach ($bills as $bill) {
            $this->handleBills($bill);
        }

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
     * @Route("/leases/bill/{id}")
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
        $bill = $this->getDoctrine()->getRepository("SandboxApiBundle:Lease\LeaseBill")->find($id);
        $this->throwNotFoundIfNull($bill, self::NOT_FOUND_MESSAGE);

        $this->handleBills($bill);

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
     * @Route("/leases/bill")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postBillAction(
        Request $request
    ) {
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
     * @Route("/leases/bill/{id}")
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
        $bill = $this->getDoctrine()->getRepository("SandboxApiBundle:Lease\LeaseBill")->find($id);
        $this->throwNotFoundIfNull($bill, self::NOT_FOUND_MESSAGE);

        if ($bill->getStatus() != LeaseBill::STATUS_UNPAID) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $billJson = $this->container->get('serializer')->serialize($bill, 'json');
        $patch = new Patch($billJson, $request->getContent());
        $billJson = $patch->apply();
        $form = $this->createForm(new LeaseBillPatchType(), $bill);
        $form->submit(json_decode($billJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->persist($bill);
        $em->flush();

        return new View();
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
        $serialNumber = $this->generateSerialNumber(self::LEASE_BILL_LETTER_HEAD);
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

        return new View($response, 201);
    }

    /**
     * @param $bill
     */
    private function handleBills(
        $bill
    ) {
        $sender = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView')->find($bill->getSender());
        $bill->setPushPeople($sender);

        $drawee = $bill->getDrawee() ? $bill->getDrawee() : $bill->getLease()->getDrawee();
        $payer = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView')->find($drawee);
        $bill->setPayer($payer);
    }
}
