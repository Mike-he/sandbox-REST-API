<?php

namespace Sandbox\SalesApiBundle\Controller\Product;

use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Product\ProductAppointment;
use Sandbox\ApiBundle\Form\Product\ProductAppointmentPatchType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Admin product appointment controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminProductAppointmentController extends AdminProductController
{
    /**
     * Get product appointments.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
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
     *    name="buildingId",
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
     * @Route("/appointments/list")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getProductAppointmentsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $adminId = $this->getAdminId();
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $buildingId = $paramFetcher->get('buildingId');
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

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT,
            )
        );

        if (!is_null($buildingId) && !in_array((int) $buildingId, $myBuildingIds)) {
            return new View();
        }

        return $this->handleProductAppointmentList(
            $buildingId,
            $myBuildingIds,
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
     * @Route("/appointments/{id}")
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
        $appointment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductAppointment')
            ->find($id);
        $this->throwNotFoundIfNull($appointment, self::NOT_FOUND_MESSAGE);

        $buildingId = $appointment->getProduct()->getRoom()->getBuildingId();

        // check user permission
        $adminId = $this->getAdminId();
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT,
                    'building_id' => $buildingId,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

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
     * @Route("/appointments/{id}")
     * @Method({"PATCH"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function patchProductAppointmentByIdAction(
        Request $request,
        $id
    ) {
        $appointment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductAppointment')
            ->findOneBy([
                'id' => $id,
                'status' => ProductAppointment::STATUS_PENDING,
            ]);
        $this->throwNotFoundIfNull($appointment, self::NOT_FOUND_MESSAGE);

        $buildingId = $appointment->getProduct()->getRoom()->getBuildingId();

        // check user permission
        $adminId = $this->getAdminId();
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT,
                    'building_id' => $buildingId,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        return $this->handleProductAppointmentPatch(
            $request,
            $appointment
        );
    }

    /**
     * @param Request            $request
     * @param ProductAppointment $appointment
     */
    private function handleProductAppointmentPatch(
        $request,
        $appointment
    ) {
        $appointmentJson = $this->container->get('serializer')->serialize($appointment, 'json');
        $patch = new Patch($appointmentJson, $request->getContent());
        $appointmentJson = $patch->apply();

        $form = $this->createForm(new ProductAppointmentPatchType(), $appointment);
        $form->submit(json_decode($appointmentJson, true));

        $status = $appointment->getStatus();
        if ($status !== ProductAppointment::STATUS_REJECTED && $status !== ProductAppointment::STATUS_ACCEPTED) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $action = Log::ACTION_AGREE;
        if ($status == ProductAppointment::STATUS_REJECTED) {
            $action = Log::ACTION_REJECT;
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_PRODUCT_APPOINTMENT,
            'logAction' => $action,
            'logObjectKey' => Log::OBJECT_PRODUCT_APPOINTMENT,
            'logObjectId' => $appointment->getId(),
        ));
    }

    /**
     * @param $buildingId
     * @param $myBuildingIds
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
        $myBuildingIds,
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

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductAppointment')
            ->countSalesProductAppointments(
                $buildingId,
                $myBuildingIds,
                $status,
                $keyword,
                $search,
                $createRange,
                $createStart,
                $createEnd,
                $startDate,
                $endDate
            );

        $appointments = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductAppointment')
            ->getSalesProductAppointments(
                $buildingId,
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
                $offset
            );

        foreach ($appointments as $appointment) {
            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy([
                    'userId' => $appointment->getUserId(),
                ]);
            if (!is_null($profile)) {
                $appointment->setUser($profile->getName());
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
}
