<?php

namespace Sandbox\AdminApiBundle\Controller\Product;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class AdminProductAppointmentController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="pending, withdrawn, accepted, rejected"
     * )
     *
     * @Annotations\QueryParam(
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by company id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by building id"
     * )
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
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="applicant, room, number"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword_search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_date_range",
     *    default=null,
     *    nullable=true,
     *    description="last_week, last_month"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_start",
     *    default=null,
     *    nullable=true,
     *    description="create start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_end",
     *    default=null,
     *    nullable=true,
     *    description="create end date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment end date"
     * )
     *
     * @Route("/products/appointments/list")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getProductAppointmentsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->checkAdminProductAppointmentPermission(AdminPermission::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $companyId = $paramFetcher->get('company');
        $buildingId = $paramFetcher->get('building');
        $status = $paramFetcher->get('status');

        // search keyword and query
        $keyword = $paramFetcher->get('keyword');
        $search = $paramFetcher->get('keyword_search');

        // creation date filter
        $createRange = $paramFetcher->get('create_date_range');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');

        // appointment date filter
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        return $this->handleProductAppointmentList(
            $buildingId,
            $companyId,
            $status,
            $keyword,
            $search,
            $createRange,
            $createStart,
            $createEnd,
            $startDate,
            $endDate,
            $pageIndex,
            $pageLimit
        );
    }

    /**
     * Get product appointments by Id.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/products/appointments/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getProductAppointmentByIdAction(
        Request $request,
        $id
    ) {
        $this->checkAdminProductAppointmentPermission(AdminPermission::OP_LEVEL_VIEW);

        $appointment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductAppointment')
            ->find($id);
        $this->throwNotFoundIfNull($appointment, self::NOT_FOUND_MESSAGE);

        $view = new View($appointment);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups([
                'client_appointment_list',
                'client_appointment_detail',
                'admin_appointment',
            ]));

        return $view;
    }

    /**
     * @param $buildingId
     * @param $companyId
     * @param $status
     * @param $keyword
     * @param $search
     * @param $createRange
     * @param $createStart
     * @param $createEnd
     * @param $startDate
     * @param $endDate
     * @param $pageIndex
     * @param $pageLimit
     *
     * @return View
     */
    private function handleProductAppointmentList(
        $buildingId,
        $companyId,
        $status,
        $keyword,
        $search,
        $createRange,
        $createStart,
        $createEnd,
        $startDate,
        $endDate,
        $pageIndex,
        $pageLimit
    ) {
        $offset = ($pageIndex - 1) * $pageLimit;
        $limit = $pageLimit;

        $myBuildingIds = [];
        if (!is_null($buildingId) && !empty($buildingId)) {
            $myBuildingIds = [$buildingId];
        }

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductAppointment')
            ->countSalesProductAppointments(
                $myBuildingIds,
                $status,
                $keyword,
                $search,
                $createRange,
                $createStart,
                $createEnd,
                $startDate,
                $endDate,
                $companyId
            );

        $appointments = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductAppointment')
            ->getSalesProductAppointments(
                $myBuildingIds,
                $status,
                $keyword,
                $search,
                $createRange,
                $createStart,
                $createEnd,
                $startDate,
                $endDate,
                $limit,
                $offset,
                $companyId
            );

        foreach ($appointments as $appointment) {
            $lease = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\Lease')
                ->findOneBy(['productAppointment' => $appointment]);
            if (!is_null($lease)) {
                $appointment->setLeaseId($lease->getId());
            }
        }

        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups([
                'client_appointment_list',
                'client_appointment_detail',
                'admin_appointment',
            ]));
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $appointments,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    private function checkAdminProductAppointmentPermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_APPOINTMENT],
            ],
            $opLevel
        );
    }
}
