<?php

namespace Sandbox\SalesApiBundle\Controller\Service;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Service\Service;
use Sandbox\ApiBundle\Entity\Service\ServiceOrder;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;

class AdminServiceOrderController extends SalesRestController
{
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
             * @var Service      $service
             */
            $service = $order->getService();

            $city = $this->getRepo('Room\RoomCity')->find($service->getCityId());
            $country = $this->getRepo('Room\RoomCity')->find($service->getCountryId());
            $province = $this->getRepo('Room\RoomCity')->find($service->getProvinceId());
            $district = $this->getRepo('Room\RoomCity')->find($service->getDistrictId());

            $service->setCountry($country);
            $service->setProvince($province);
            $service->setCity($city);
            $service->setDistrict($district);
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
