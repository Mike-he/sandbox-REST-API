<?php

namespace Sandbox\ClientApiBundle\Controller\Lease;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sandbox\ApiBundle\Form\Lease\LeasePatchType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClientLeaseController extends SandboxRestController
{
    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="ids",
     *     array=true
     * )
     *
     * @Route("/leases/time_remaining")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLeaseTimeRemainingAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $ids = $paramFetcher->get('ids');

        $response = array();
        foreach ($ids as $id) {
            $lease = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\Lease')
                ->findOneBy(array(
                    'id' => $id,
                    'status' => Lease::LEASE_STATUS_CONFIRMING,
                ));

            if (is_null($lease)) {
                continue;
            }

            $modificationDate = $lease->getModificationDate();
            $expireInParameter = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Parameter\Parameter')
                ->findOneBy(array(
                    'key' => Parameter::KEY_LEASE_CONFIRM_EXPIRE_IN,
                ));
            $leaseExpireInDate = $modificationDate->add(new \DateInterval('P'.$expireInParameter->getValue()));

            $now = new \DateTime('now');
            $diffDate = $now->diff($leaseExpireInDate);

            array_push($response, array(
                'lease_id' => $id,
                'remaining_days' => $diffDate->d,
                'remaining_hours' => $diffDate->h,
                'remaining_minutes' => $diffDate->i,
                'remaining_seconds' => $diffDate->s,
            ));
        }

        if (empty($response)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return new View($response);
    }

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
