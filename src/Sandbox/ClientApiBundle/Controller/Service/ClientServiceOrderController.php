<?php

namespace Sandbox\ClientApiBundle\Controller\Service;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Service\Service;
use Sandbox\ApiBundle\Entity\Service\ServiceOrder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;

class ClientServiceOrderController extends SandboxRestController
{
    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Offset of page"
     * )
     *
     * @Route("/service/orders/my/sales/invoice")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getServiceOrderInvoiceAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $serviceOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->getInvoiceServiceOrders(
                $userId,
                $limit,
                $offset
            );

        $response = [];
        foreach ($serviceOrders as $serviceOrder) {
            /** @var ServiceOrder $serviceOrder */
            $service = $serviceOrder->getService();

            /** @var Service $service */
            $salesCompanyId = $service->getSalesCompanyId();
            $salesCompany = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
                ->find($salesCompanyId);

            array_push($response, [
                'trade_id' => $serviceOrder->getId(),
                'company_id' => $salesCompanyId,
                'company_name' => $salesCompany->getName(),
                'trade_type' => 'service_order',
                'trade_number' => $serviceOrder->getOrderNumber(),
                'trade_name' => $service->getName(),
                'payment_date' => $serviceOrder->getPaymentDate(),
                'amount' => (float) $serviceOrder->getPrice(),
                'sales_invoice' => true,
            ]);
        }

        return new View($response);
    }
}