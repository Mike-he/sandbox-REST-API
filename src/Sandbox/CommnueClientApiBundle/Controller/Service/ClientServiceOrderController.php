<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Service;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Error\Error;
use Sandbox\ApiBundle\Entity\Service\ServiceOrder;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sandbox\ApiBundle\Entity\Service\Service;

class ClientServiceOrderController extends SandboxRestController
{
    const SERVICE_NOT_AVAILABLE_CODE = 400031;
    const SERVICE_NOT_AVAILABLE_MESSAGE = 'Service Is Not Available';
    const SERVICE_REGISTRATION_NOT_AVAILABLE_CODE = 400032;
    const SERVICE_REGISTRATION_NOT_AVAILABLE_MESSAGE = 'Event Registration Is Not Available';
    const WRONG_SERVICE_ORDER_STATUS_CODE = 400033;
    const WRONG_SERVICE_ORDER_STATUS_MESSAGE = 'Wrong Order Status';
    const SERVICE_ORDER_EXIST_CODE = 400034;
    const SERVICE_ORDER_EXIST_MESSAGE = 'Service Order Already Exists';

    public function postServiceOrderController(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();
        $now = new \DateTime();

        $service = $this->getDoctrine()->getManager()
            ->getRepository('SandboxApiBundle:Service\Service')
            ->findOneBy(array(
                'id'=>$id,
                'visible' => true
            ));
        $this->throwNotFoundIfNull($service, self::NOT_FOUND_MESSAGE);

        $serviceOrder = new ServiceOrder();
        $customerId = null;

        if ($service->getSalesCompanyId()) {
            $customerId = $this->get('sandbox_api.sales_customer')->createCustomer(
                $service,
                $service->getSalesCompanyId()
            );
        }

        $error = new Error();
        $this->checkIfAvailable(
            $userId,
            $service,
            $now,
            $error
        );

    }

    /**
     * @param $userId
     * @param Service $service
     * @param $now
     * @param $error
     */
    private function checkIfAvailable(
        $userId,
        $service,
        $now,
        $error
    ) {
        $serviceEnd = $service->getServiceEndDate();

        if(
            $serviceEnd < $now ||
            !$service->isCharge() ||
            is_null($service->getPrice()) ||
            false == $service->isVisible()
        ) {
            $error->setCode(self::SERVICE_NOT_AVAILABLE_CODE);
            $error->setMessage(self::SERVICE_NOT_AVAILABLE_MESSAGE);
        }

        // check service order exists
        $serviceId = $service->getId();
        $order = $this->getDoctrine()->getManager()
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->getUserLastOrder(
                $userId,
                $serviceId
            );
        if (!is_null($order) && EventOrder::STATUS_CANCELLED != $order->getStatus()) {
            $error->setCode(self::EVENT_ORDER_EXIST_CODE);
            $error->setMessage(self::EVENT_ORDER_EXIST_MESSAGE);
        }
    }
}