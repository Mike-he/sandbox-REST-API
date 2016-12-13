<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

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

class LeaseRentTypesController extends SalesRestController
{
    /**
     * Get List of Rent Types.
     *
     * @Route("/lease/renttypes")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getLeaseRentTypesAction()
    {
        $leaseRentTypes =  $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseRentTypes')
            ->findAll();

        return new View($leaseRentTypes);
    }
   
}
