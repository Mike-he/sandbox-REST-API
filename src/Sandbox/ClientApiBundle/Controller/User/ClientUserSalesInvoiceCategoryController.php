<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ClientUserSalesInvoiceCategoryController.
 */
class ClientUserSalesInvoiceCategoryController extends SalesRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="order_id",
     *     array=false,
     *     strict=true
     * )
     *
     * @Route("/sales/invoice/categories")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSalesInvoiceCategoriesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $orderId = $paramFetcher->get('order_id');

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->find($orderId);
        if (is_null($order)) {
            return new View(array());
        }

        $roomType = $order->getProduct()->getRoom()->getType();
        $salesCompany = $order->getProduct()->getRoom()->getBuilding()->getCompany();

        $infos = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
            ->findBy(array(
                'company' => $salesCompany,
                'roomTypes' => $roomType,
            ));

        $response = array();
        foreach ($infos as $info) {
            array_push($response, array(
                'name' => $info->getInvoicingSubjects(),
            ));
        }

        return new View($response);
    }
}
