<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use Sandbox\SalesApiBundle\Controller\SalesRestController;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;

class AdminLeaseRentTypesController extends SalesRestController
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
        $leaseRentTypes = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseRentTypes')
            ->findBy(array(
                'status' => true,
            ));

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['lease_rent_types_list']));
        $view->setData($leaseRentTypes);

        return $view;
    }
}
