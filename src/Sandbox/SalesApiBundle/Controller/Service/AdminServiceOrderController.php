<?php

namespace Sandbox\SalesApiBundle\Controller\Service;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Service\Service;
use Sandbox\ApiBundle\Entity\Service\ServiceOrder;
use Sandbox\ApiBundle\Traits\HandleServiceDataTrait;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;

class AdminServiceOrderController extends SalesRestController
{
    use HandleServiceDataTrait;
    /**
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
     *    description="How many products to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Route("/service/orders")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->getServiceOrders(
                $companyId,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->countServiceOrders(
                $companyId
            );

        foreach ($orders as $order) {
            /**
             * @var ServiceOrder
             */
            $service = $order->getService();

            $this->handleServicesData($service);
        }

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $orders,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/service/orders/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getOrderByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->findOneBy(array(
                'id' => $id,
                'companyId' => $companyId,
            ));
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $view = new View($order);

        return $view;
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/service/orders/purchase")
     * @Method({"GET"})
     *
     * @return mixed
     */
    public function getPurchaseUserAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permission
        $this->checkPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $limit = $pageLimit;
        $offset = ($pageIndex-1)*$pageLimit;

        $orders = $this->getDoctrine()->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->findBy(array(
                        'serviceId'=>$id,
                        'customerId'=>$companyId
                    ),
                        null,
                        $limit,
                        $offset
                  );
        $this->throwNotFoundIfNull($orders, self::NOT_FOUND_MESSAGE);

        $count =  $this->getDoctrine()->getRepository('SandboxApiBundle:Service\ServiceOrder')
        ->getServicePurchaseCount($id);

        foreach ($orders as $order){
            $purchaseForm = $this->getDoctrine()->getRepository('SandboxApiBundle:Service\ServicePurchaseForm')
                ->findByOrders($order);

            $order->setPurchaseForm($purchaseForm);
        }

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $orders,
                'total_count' => (int) $count,
            )
        );

        return new $view;
    }

    /**
     * @param $id
     *
     * @Route("/service/orders/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function changeServiceOrdersStatusAction(
        $id
    ) {
        // check user permission
        $this->checkPermission(AdminPermission::OP_LEVEL_EDIT);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        $order = $this->getDoctrine()->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->findOneBy(array(
                'id'=>$id,
                'status'=>ServiceOrder::STATUS_PAID,
                'companyId'=>$companyId
            ));

        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $order->setStatus(ServiceOrder::STATUS_COMPLETED);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_SERVICE_ORDER,
                ),
            ),
            $opLevel
        );
    }
}
