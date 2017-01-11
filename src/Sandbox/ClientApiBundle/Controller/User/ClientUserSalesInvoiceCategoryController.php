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
     *     name="order_ids",
     *     array=true,
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
        $orderIds = $paramFetcher->get('order_ids');

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getProductOrdersByIds($orderIds);
        if (empty($orders)) {
            return new View(array());
        }

        $response = array();
        foreach ($orders as $order) {
            $roomType = $order->getProduct()->getRoom()->getType();
            $roomType = 'longterm';
            $salesCompany = $order->getProduct()->getRoom()->getBuilding()->getCompany();

            $infos = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
                ->findBy(array(
                    'company' => $salesCompany,
                    'roomTypes' => $roomType,
                ));

            $orderArray = array(
                'order_id' => $order->getId(),
                'categories' => array(),
            );

            foreach ($infos as $info) {
                array_push($orderArray['categories'], array(
                    'name' => $info->getInvoicingSubjects(),
                ));
            }

            array_push($response, $orderArray);
        }

        return new View($response);
    }
}
