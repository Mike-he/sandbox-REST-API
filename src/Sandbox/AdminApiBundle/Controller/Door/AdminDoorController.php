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
        $userId = $request->get('user_id');
        $cardNo = $request->get('card_no');
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();

        $orders = $this->getRepo('Order\ProductOrder')->getOrdersByUser($userId);
        if (empty($orders)) {
            return $this->customErrorView(
                400,
                self::NO_ORDER_CODE,
                self::NO_ORDER_MESSAGE
            );
        }

        $ids = $this->getRepo('Door\DoorAccess')->getBuildingIds($userId);

        foreach ($ids as $id) {
            $doors = $this->getRepo('Door\DoorAccess')->getDoorsByBuilding(
                $userId,
                $id['buildingId']
            );

            $doorArray = [];
            foreach ($doors as $door) {
                $doorId = $door->getDoorId();
                $timeId = $door->getTimeId();
                $door = ['doorid' => $doorId, 'timeperiodid' => "$timeId"];

                array_push($doorArray, $door);
            }

            $this->get('door_service')->cardPermission(
                $id['buildingId'],
                $userId,
                $userName,
                $cardNo,
                $doorArray,
                DoorController::METHOD_ADD
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
        $userId = $request->get('user_id');
        $cardNo = $request->get('card_no');
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();

        $orders = $this->getRepo('Order\ProductOrder')->findBy(['userId' => $userId]);
        if (empty($orders)) {
            return $this->customErrorView(
                400,
                self::NO_ORDER_CODE,
                self::NO_ORDER_MESSAGE
            );
        }

        $ids = $this->getRepo('Door\DoorAccess')->getBuildingIds($userId);

        foreach ($ids as $id) {
            $this->get('door_service')->cardPermission(
                $id['buildingId'],
                $userId,
                $userName,
                $cardNo,
                $doorArray = [],
                DoorController::METHOD_UNLOST
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
        $userId = $request->get('user_id');
        $cardNo = $request->get('card_no');
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();

        $orders = $this->getRepo('Order\ProductOrder')->findBy(['userId' => $userId]);
        if (empty($orders)) {
            return $this->customErrorView(
                400,
                self::NO_ORDER_CODE,
                self::NO_ORDER_MESSAGE
            );
        }

        $ids = $this->getRepo('Door\DoorAccess')->getBuildingIds($userId);

        foreach ($ids as $id) {
            $this->get('door_service')->cardPermission(
                $id['buildingId'],
                $userId,
                $userName,
                $cardNo,
                $doorArray = [],
                DoorController::METHOD_CHANGE_CARD
            );
        }
    }

    /**
     * @Get("/doors")
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    default=1,
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
        $buildingId = $paramFetcher->get('building');
        $sessionId = $this->getSessionId($buildingId);
        try {
            $data = self::SESSION_ID.$sessionId;
            $base = $this->getBaseURL($buildingId);

            $doorArray = $this->postDoorApi($base.self::GET_DOOR_URI, $data);
            $this->logOut($sessionId, $buildingId);
            if ($doorArray['ads_result']['result'] !== self::RESULT_OK) {
                return $this->customErrorView(
                    400,
                    self::RESPONSE_NOT_VALID_CODE,
                    self::RESPONSE_NOT_VALID_MESSAGE
                );
            }

            return new View($doorArray['ads_doors']);
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $buildingId);
            }
        }
    }

    /**
     * @Get("/doors/records")
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    default=1,
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
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $buildingId = $paramFetcher->get('building');
        $sessionId = $this->getSessionId($buildingId);
        try {
            $begin = $paramFetcher->get('begin_time');
            $end = $paramFetcher->get('end_time');
            if (is_null($end) || empty($end)) {
                $end = new \DateTime();
                $end = (string) $end->format('Y-m-d H:i:s');
            }
            $data = self::SESSION_ID.$sessionId.self::BEGIN_TIME.$begin.self::END_TIME.$end;
            $base = $this->getBaseURL($buildingId);
            $recordArray = $this->postDoorApi($base.self::RECORD_URI, $data);
            $this->logOut($sessionId, $buildingId);

            if ($recordArray['ads_result']['result'] !== self::RESULT_OK) {
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
                $this->logOut($sessionId, $buildingId);
            }
        }
    }

    /**
     * @Get("/doors/alarms")
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    default=1,
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
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $buildingId = $paramFetcher->get('building');
        $sessionId = $this->getSessionId($buildingId);
        try {
            $begin = $paramFetcher->get('begin_time');
            $end = $paramFetcher->get('end_time');
            if (is_null($end) || empty($end)) {
                $end = new \DateTime();
                $end = (string) $end->format('Y-m-d H:i:s');
            }

            $data = self::SESSION_ID.$sessionId.self::BEGIN_TIME.$begin.self::END_TIME.$end;
            $base = $this->getBaseURL($buildingId);
            $recordArray = $this->postDoorApi($base.self::ALARM_URI, $data);
            $this->logOut($sessionId, $buildingId);
            if ($recordArray['ads_result']['result'] !== self::RESULT_OK) {
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
                $this->logOut($sessionId, $buildingId);
            }
        }
    }
}
