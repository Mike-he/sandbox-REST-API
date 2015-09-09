<?php

namespace Sandbox\AdminApiBundle\Controller\Door;

use Sandbox\ApiBundle\Controller\Door\DoorController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Sandbox\ApiBundle\Entity\Door\DoorAccess;
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
    const SHANGHAI_SANDBOX = '上海Sandbox';
    const SHANGHAI_SANDBOX_DOOR = '{4F768196-2A07-4E50-B716-B725BF877C42}';

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

        $this->setFrontDoorAccess(
            $userId,
            $userName,
            $cardNo,
            $globals
        );

        $ids = $this->getRepo('Door\DoorAccess')->getBuildingIds($userId);

        if (!is_null($ids) && !empty($ids)) {
            foreach ($ids as $id) {
                $doors = $this->getRepo('Door\DoorAccess')->getDoorsByBuilding(
                    $userId,
                    $id['buildingId']
                );
                $building = $this->getRepo('Room\RoomBuilding')->find($id['buildingId']);
                $base = $building->getServer();

                $doorArray = [];
                foreach ($doors as $door) {
                    $doorId = $door->getDoorId();
                    $timeId = $door->getTimeId();
                    $door = ['doorid' => $doorId, 'timeperiodid' => "$timeId"];

                    array_push($doorArray, $door);
                }

                $this->get('door_service')->cardPermission(
                    $base,
                    $userId,
                    $userName,
                    $cardNo,
                    $doorArray,
                    DoorController::METHOD_ADD,
                    $globals
                );
            }
        }
    }

    /**
     * @param $userId
     * @param $userName
     * @param $cardNo
     * @param $globals
     */
    public function setFrontDoorAccess(
        $userId,
        $userName,
        $cardNo,
        $globals
    ) {
        $sandboxBuilding = $this->getRepo('Room\RoomBuilding')->findOneBy(['name' => self::SHANGHAI_SANDBOX]);
        $sandboxBuildingId = $sandboxBuilding->getId();
        $sandboxBase = $sandboxBuilding->getServer();

        $now = new \DateTime();
        $start = new \DateTime('2015-07-01');
        $end = new \DateTime('2099-01-01');

        $door = new DoorAccess();
        $door->setUserId($userId);
        $door->setStartDate($start);
        $door->setEndDate($end);
        $door->setBuildingId($sandboxBuildingId);
        $door->setCreationDate($now);
        $door->setDoorId(self::SHANGHAI_SANDBOX_DOOR);
        $door->setOrderId(0);
        $door->setRoomId(0);
        $door->setTimeId(1);
        $em = $this->getDoctrine()->getManager();
        $em->persist($door);
        $em->flush();

        $sandboxArray = [];
        array_push($sandboxArray, $door);
        $this->setTimePeriod($sandboxArray, $sandboxBase, $globals);
        $timeArray = [];
        $time = ['doorid' => self::SHANGHAI_SANDBOX_DOOR, 'timeperiodid' => '1'];
        array_push($timeArray, $time);

        $this->get('door_service')->cardPermission(
            $sandboxBase,
            $userId,
            $userName,
            $cardNo,
            $timeArray,
            DoorController::METHOD_ADD,
            $globals
        );
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
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();

        $globals = $this->getGlobals();
        $ids = $this->getRepo('Door\DoorAccess')->getBuildingIds($userId);

        foreach ($ids as $id) {
            $building = $this->getRepo('Room\RoomBuilding')->find($id['buildingId']);
            $base = $building->getServer();

            $this->get('door_service')->cardPermission(
                $base,
                $userId,
                $userName,
                $cardNo,
                $doorArray = [],
                DoorController::METHOD_UNLOST,
                $globals
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
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();

        $ids = $this->getRepo('Door\DoorAccess')->getBuildingIds($userId);
        $globals = $this->getGlobals();
        foreach ($ids as $id) {
            $building = $this->getRepo('Room\RoomBuilding')->find($id['buildingId']);
            $base = $building->getServer();

            $this->get('door_service')->cardPermission(
                $base,
                $userId,
                $userName,
                $cardNo,
                $doorArray = [],
                DoorController::METHOD_CHANGE_CARD,
                $globals
            );
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
