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
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\Room;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
    const DOOR_MODULE_USER = 'user';
    const DOOR_MODULE_ACCESS = 'access';

    /**
     * @Get("/doors/data/sync")
     *
     * @Annotations\QueryParam(
     *    name="module",
     *    array=true,
     *    strict=true,
     *    nullable=false,
     *    description="module of door"
     * )
     *
     * @Annotations\QueryParam(
     *    name="user",
     *    default=null,
     *    nullable=true,
     *    description="user id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    default=null,
     *    nullable=true,
     *    description="city id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    default=null,
     *    nullable=true,
     *    description="building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="room",
     *    default=null,
     *    nullable=true,
     *    description="room id"
     * )
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function syncDataAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminDoorPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // get parameters
        $syncModules = $paramFetcher->get('module');
        if (is_null($syncModules) || empty($syncModules)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $syncUserId = $paramFetcher->get('user');
        $syncCityId = $paramFetcher->get('city');
        $syncBuildingId = $paramFetcher->get('building');
        $syncRoomId = $paramFetcher->get('room');

        // get entities
        $syncUser = !is_null($syncUserId) ? $this->getRepo('User\User')->find($syncUserId) : null;
        $syncCity = !is_null($syncCityId) ? $this->getRepo('Room\RoomCity')->find($syncCityId) : null;
        $syncBuilding = !is_null($syncBuildingId) ? $this->getRepo('Room\RoomBuilding')->find($syncBuildingId) : null;
        $syncRoom = !is_null($syncRoomId) ? $this->getRepo('Room\Room')->find($syncRoomId) : null;

        foreach ($syncModules as $module) {
            if ($module == self::DOOR_MODULE_USER) {
                // sync user data
                $this->syncUserData($syncUser, $syncCity, $syncBuilding, $syncRoom);
            } elseif ($module == self::DOOR_MODULE_ACCESS) {
                // sync access data
            }
        }

        return new View();
    }

    /**
     * @param User         $syncUser
     * @param RoomCity     $syncCity
     * @param RoomBuilding $syncBuilding
     * @param Room         $syncRoom
     */
    private function syncUserData(
        $syncUser,
        $syncCity,
        $syncBuilding,
        $syncRoom
    ) {
        if (is_null($syncUser)) {
            $users = $this->getRepo('User\User')->findByAuthorized(true);

            foreach ($users as $user) {
                $this->syncSingleUserData($user, $syncCity, $syncBuilding, $syncRoom);
            }
        } elseif ($syncUser->isAuthorized()) {
            $this->syncSingleUserData($syncUser, $syncCity, $syncBuilding, $syncRoom);
        }
    }

    /**
     * @param User         $user
     * @param RoomCity     $city
     * @param RoomBuilding $building
     * @param Room         $room
     */
    private function syncSingleUserData(
        $user,
        $city,
        $building,
        $room
    ) {
        $userId = $user->getId();

        // get user's name
        $profile = $this->getRepo('User\UserProfile')->findOneByUser($user);
        $name = !is_null($profile) ? $profile->getName() : "$userId";

        // get user's card no
        $result = $this->getCardNoByUser($userId);
        if (is_null($result)) {
            return;
        }

        // comment out for phase 1
//        if (is_null($room)) {
//            // update user to doors of a room
//        } elseif (is_null($building)) {
//            // update user to doors of a building
//        } elseif (is_null($city)) {
//            // update user to doors of a city
//        } else {

        // update user to all door servers
        $this->updateEmployeeCardStatus(
            $userId,
            $name,
            $result['card_no'],
            DoorController::METHOD_ADD
        );

//        }
    }

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

        $buildingIds = $this->getRepo('Door\DoorAccess')->getBuildingIds($userId);
        if (!is_null($buildingIds) && !empty($buildingIds)) {
            // delay 5 seconds between api calls
            sleep(5);
            $this->checkIfAccessIsSet(
                $buildingIds,
                $userId,
                $globals
            );
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

        // set card
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();
        $this->updateEmployeeCardStatus(
            $userId,
            $userName,
            $cardNo,
            DoorController::METHOD_ADD
        );
        sleep(1);

        // update card
        $this->updateEmployeeCardStatus(
            $userId,
            '',
            $cardNo,
            DoorController::METHOD_UNLOST
        );

        // set access
        $buildingIds = $this->getRepo('Door\DoorAccess')->getBuildingIds($userId);
        if (!is_null($buildingIds) && !empty($buildingIds)) {
            $this->checkIfAccessIsSet(
                $buildingIds,
                $userId,
                $this->getGlobals()
            );
        }
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

        // get user's current card no
        $result = $this->getCardNoByUser($userId);
        if (is_null($result)) {
            return;
        }

        // set card
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();
        $this->updateEmployeeCardStatus(
            $userId,
            $userName,
            $result['card_no'],
            DoorController::METHOD_ADD
        );
        sleep(1);

        // update card
        $this->updateEmployeeCardStatus(
            $userId,
            '',
            $cardNo,
            DoorController::METHOD_CHANGE_CARD
        );

        // set access
        $buildingIds = $this->getRepo('Door\DoorAccess')->getBuildingIds($userId);
        if (!is_null($buildingIds) && !empty($buildingIds)) {
            $this->checkIfAccessIsSet(
                $buildingIds,
                $userId,
                $this->getGlobals()
            );
        }
    }

    /**
     * @param $buildingIds
     * @param $userId
     * @param $globals
     */
    public function checkIfAccessIsSet(
        $buildingIds,
        $userId,
        $globals
    ) {
        foreach ($buildingIds as $id) {
            try {
                $building = $this->getRepo('Room\RoomBuilding')->find($id['buildingId']);
                if (is_null($building)) {
                    continue;
                }

                // get building door access server
                $base = $building->getServer();

                // get orders by building
                $orderIds = $this->getRepo('Door\DoorAccess')->getOrdersByBuilding(
                    $userId,
                    $id['buildingId']
                );
                if (is_null($orderIds) || empty($orderIds)) {
                    continue;
                }

                foreach ($orderIds as $orderId) {
                    try {
                        $doorArray = [];

                        $order = $this->getRepo('Order\ProductOrder')->find($orderId['orderId']);
                        if (is_null($order)) {
                            continue;
                        }

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

                        $this->setRoomOrderPermission(
                            $base,
                            $userArray,
                            $orderId['orderId'],
                            $startDate,
                            $endDate,
                            $doorArray,
                            $globals
                        );
                    } catch (\Exception $e) {
                        error_log('Door Access Error, Set Card');
                        continue;
                    }
                }
            } catch (\Exception $e) {
                error_log('Door Access Error, Set Card');
                continue;
            }
        }
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
        $sessionId = null;

        try {
            $sessionId = $this->getSessionId($base, $globals);

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
            error_log('Get doors went wrong!');
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
        $sessionId = null;

        try {
            $sessionId = $this->getSessionId($base, $globals);

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
            error_log('Get swipe records went wrong!');
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
        $sessionId = null;

        try {
            $sessionId = $this->getSessionId($base, $globals);

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
            error_log('Get alarm records went wrong!');
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
