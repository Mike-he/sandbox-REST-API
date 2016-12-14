<?php

namespace Sandbox\ClientApiBundle\Controller\Lease;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Form\Lease\LeasePatchType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use FOS\RestBundle\Controller\Annotations;

class ClientLeaseController extends SandboxRestController
{
    /**
     * Get Lease Detail.
     *
     * @param $id
     *
     * @Route("/leases/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLeaseAction(
        $id
    ) {
        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->find($id);

        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkUserLeasePermission($lease);

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBy(array(
                'lease' => $lease,
                'type' => LeaseBill::TYPE_LEASE,
            ));
        $lease->setBills($bills);

        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['main'])
        );
        $view->setData($lease);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="offset",
     *     default="0",
     *     nullable=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="limit",
     *     default="10",
     *     nullable=true
     * )
     *
     * @Route("/leases")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLeasesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $offset = $paramFetcher->get('offset');
        $limit = $paramFetcher->get('limit');

        $leases = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->getClientLeases(
                $userId,
                $limit,
                $offset
            );

        $response = array();
        foreach ($leases as $lease) {
            $bills = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->findBy(array(
                    'lease' => $lease,
                    'status' => LeaseBill::STATUS_UNPAID,
                    'type' => LeaseBill::TYPE_LEASE,
                ));

            array_push($response, array(
                'serial_number' => $lease->getSerialNumber(),
                'status' => $lease->getStatus(),
                'product' => $lease->degenerateProduct(),
                'unpaid_bill_counts' => count($bills),
                'creation_date' => $lease->getCreationDate(),
            ));
        }

        return new View($response);
    }

    /**
     * Patch Lease Status.
     *
     * @param $request
     * @param $id
     *
     * @Route("/leases/{id}/status")
     * @Method({"PATCH"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function patchLeaseStatusAction(
        Request $request,
        $id
    ) {
        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')->find($id);

        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkUserLeasePermission($lease);

        $leaseJson = $this->container
            ->get('serializer')
            ->serialize($lease, 'json');

        $patch = new Patch($leaseJson, $request->getContent());
        $leaseJson = $patch->apply();

        $form = $this->createForm(new LeasePatchType(), $lease);
        $form->submit(json_decode($leaseJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));

        return new View();
    }

    public function checkUserLeasePermission($lease)
    {
        if ($this->getUser() != $lease->getSuperVisor()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }
    }
}
