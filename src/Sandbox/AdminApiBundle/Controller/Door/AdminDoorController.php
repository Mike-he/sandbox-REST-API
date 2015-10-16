<?php

namespace Sandbox\AdminApiBundle\Controller\Door;

use Sandbox\ApiBundle\Controller\Door\DoorController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;

/**
 * Admin Door Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminDoorController extends DoorController
{
    /**
     * @Post("/doors/permission/add")
     *
     * @param Request $request
     *
     * @return View
     */
    public function setCardPermissionAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminDoorPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $requestContent = json_decode($request->getContent(), true);
        $userId = $requestContent['user_id'];
        $cardNo = $requestContent['card_no'];
        $globals = $this->getGlobals();
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();

        $this->updateEmployeeCardStatus(
            $userId,
            $userName,
            $cardNo,
            DoorController::METHOD_ADD
        );

        // testing door access api, delay 5 seconds between api calls
        sleep(5);
        $ids = $this->getRepo('Door\DoorAccess')->getBuildingIds($userId);

        if (!is_null($ids) && !empty($ids)) {
            foreach ($ids as $id) {
                $orderIds = $this->getRepo('Door\DoorAccess')->getOrdersByBuilding(
                    $userId,
                    $id['buildingId']
                );

                $building = $this->getRepo('Room\RoomBuilding')->find($id['buildingId']);
                $base = $building->getServer();

                foreach ($orderIds as $orderId) {
                    $doorArray = [];
                    $order = $this->getRepo('Order\ProductOrder')->find($orderId['orderId']);
                    $startDate = $order->getStartDate();
                    $endDate = $order->getEndDate();
                    $roomId = $order->getProduct()->getRoom()->getId();
                    $roomDoors = $this->getRepo('Room\RoomDoors')->findBy(['room' => $roomId]);
                    foreach ($roomDoors as $roomDoor) {
                        $door = ['doorid' => $roomDoor->getDoorControlId()];
                        array_push($doorArray, $door);
                    }
                    $userArray = [
                        ['empid' => "$userId"],
                    ];

                    $this->get('door_service')->setRoomOrderPermission(
                        $base,
                        $userArray,
                        $orderId['orderId'],
                        $startDate,
                        $endDate,
                        $doorArray,
                        $globals
                    );
                }
            }
        }
    }

    /**
     * @Post("/doors/permission/unlost")
     *
     * @param Request $request
     *
     * @return View
     */
    public function unlostCardPermissionAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminDoorPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $requestContent = json_decode($request->getContent(), true);
        $userId = $requestContent['user_id'];
        $cardNo = $requestContent['card_no'];

        $this->updateEmployeeCardStatus(
            $userId,
            '',
            $cardNo,
            DoorController::METHOD_UNLOST
        );
    }

    /**
     * @Post("/doors/permission/replace")
     *
     * @param Request $request
     *
     * @return View
     */
    public function replaceCardPermissionAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminDoorPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $requestContent = json_decode($request->getContent(), true);
        $userId = $requestContent['user_id'];
        $cardNo = $requestContent['card_no'];

        $this->updateEmployeeCardStatus(
            $userId,
            '',
            $cardNo,
            DoorController::METHOD_CHANGE_CARD
        );
    }

    /**
     * @Get("/doors")
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    default=null,
     *    nullable=true,
     *    description="
     *        building id
     *    "
     * )
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getDoorsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminDoorPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        $globals = $this->getGlobals();
        $buildingId = $paramFetcher->get('building');
        if (is_null($buildingId)) {
            return $this->customErrorView(
                400,
                self::BUILDING_NOT_FOUND_CODE,
                self::BUILDING_NOT_FOUND_MESSAGE
            );
        }
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        $base = $building->getServer();
        $name = $building->getName();

        $sessionId = $this->getSessionId($base, $globals);
        try {
            $data = $globals['door_api_session_id'].$sessionId;

            $doorArray = $this->postDoorApi($base.$globals['door_api_get_doors'], $data);
            $this->logOut($sessionId, $base, $globals);
            if ($doorArray['ads_result']['result'] != self::RESULT_OK) {
                return $this->customErrorView(
                    400,
                    self::RESPONSE_NOT_VALID_CODE,
                    self::RESPONSE_NOT_VALID_MESSAGE
                );
            }
            $updateDoors = [];
            $doors = $doorArray['ads_doors'];
            foreach ($doors as $door) {
                $doorId = $door['id'];
                $doorName = $door['name'];
                $doorName = strstr($doorName, '-');
                $doorName = $name.$doorName;
                $temp = [
                    'id' => $doorId,
                    'name' => $doorName,
                ];
                array_push($updateDoors, $temp);
            }

            return new View($updateDoors);
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $base, $globals);
            }
        }
    }

    /**
     * @Get("/doors/records")
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    default=null,
     *    nullable=true,
     *    description="
     *        building id
     *    "
     * )
     *
     * @Annotations\QueryParam(
     *    name="begin_time",
     *    default=null,
     *    nullable=true,
     *    description="
     *
     *    "
     * )
     *
     * @Annotations\QueryParam(
     *    name="end_time",
     *    default=null,
     *    nullable=true,
     *    description="
     *
     *    "
     * )
     *
     *  @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many rooms to return "
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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getRecordsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminDoorPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        $globals = $this->getGlobals();
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $buildingId = $paramFetcher->get('building');
        if (is_null($buildingId)) {
            return $this->customErrorView(
                400,
                self::BUILDING_NOT_FOUND_CODE,
                self::BUILDING_NOT_FOUND_MESSAGE
            );
        }
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        $base = $building->getServer();

        $sessionId = $this->getSessionId($base, $globals);
        try {
            $begin = $paramFetcher->get('begin_time');
            $end = $paramFetcher->get('end_time');
            if (is_null($end) || empty($end)) {
                $end = new \DateTime();
                $end = (string) $end->format('Y-m-d H:i:s');
            }
            $data = $globals['door_api_session_id'].$sessionId.'&'.
                $globals['door_api_begin_time'].$begin.'&'.
                $globals['door_api_end_time'].$end;

            $recordArray = $this->postDoorApi($base.$globals['door_api_get_card_record'], $data);
            $this->logOut($sessionId, $base, $globals);

            if ($recordArray['ads_result']['result'] != self::RESULT_OK) {
                return $this->customErrorView(
                    400,
                    self::RESPONSE_NOT_VALID_CODE,
                    self::RESPONSE_NOT_VALID_MESSAGE
                );
            }

            $paginator = new Paginator();
            $pagination = $paginator->paginate(
                $recordArray['ads_swipecard_records'],
                $pageIndex,
                $pageLimit
            );

            return new View($pagination);
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $base, $globals);
            }
        }
    }

    /**
     * @Get("/doors/alarms")
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    default=null,
     *    nullable=true,
     *    description="
     *        building id
     *    "
     * )
     *
     * @Annotations\QueryParam(
     *    name="begin_time",
     *    default=null,
     *    nullable=true,
     *    description="
     *
     *    "
     * )
     *
     * @Annotations\QueryParam(
     *    name="end_time",
     *    default=null,
     *    nullable=true,
     *    description="
     *
     *    "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many rooms to return "
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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getAlarmsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminDoorPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        $globals = $this->getGlobals();
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $buildingId = $paramFetcher->get('building');
        if (is_null($buildingId)) {
            return $this->customErrorView(
                400,
                self::BUILDING_NOT_FOUND_CODE,
                self::BUILDING_NOT_FOUND_MESSAGE
            );
        }
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        $base = $building->getServer();

        $sessionId = $this->getSessionId($base, $globals);
        try {
            $begin = $paramFetcher->get('begin_time');
            $end = $paramFetcher->get('end_time');
            if (is_null($end) || empty($end)) {
                $end = new \DateTime();
                $end = (string) $end->format('Y-m-d H:i:s');
            }

            $data = $globals['door_api_session_id'].$sessionId.'&'.
                $globals['door_api_begin_time'].$begin.'&'.
                $globals['door_api_end_time'].$end;

            $recordArray = $this->postDoorApi($base.$globals['door_api_get_alarm_record'], $data);
            $this->logOut($sessionId, $base, $globals);
            if ($recordArray['ads_result']['result'] != self::RESULT_OK) {
                return $this->customErrorView(
                    400,
                    self::RESPONSE_NOT_VALID_CODE,
                    self::RESPONSE_NOT_VALID_MESSAGE
                );
            }

            $paginator = new Paginator();
            $pagination = $paginator->paginate(
                $recordArray['ads_alarm_records'],
                $pageIndex,
                $pageLimit
            );

            return new View($pagination);
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $base, $globals);
            }
        }
    }

    /**
     * Check user permission.
     *
     * @param Integer $OpLevel
     */
    private function checkAdminDoorPermission(
        $OpLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_ACCESS,
            $OpLevel
        );
    }
}
