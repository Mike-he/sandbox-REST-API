<?php

namespace Sandbox\AdminApiBundle\Controller\Door;

use Sandbox\ApiBundle\Controller\Door\DoorController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;

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
    const RESULT_OK = 'RESULT_OK';
    const RESPONSE_NOT_VALID_CODE = 400005;
    const RESPONSE_NOT_VALID_MESSAGE = 'Response Not Valid';
    const TIME_NOT_VALID_CODE = 400006;
    const TIME_NOT_VALID_MESSAGE = 'Times Are Not Valid';
    const BASE_URI = 'http://211.95.45.26:13390/ADSWebService.asmx';
    const LOGIN_URI = '/Login';
    const LOGOUT_URI = '/Logout';
    const GET_DOOR_URI = '/GetDoors';
    const SET_TIME = '/SetTimePeriod';
    const RECORD_URI = '/GetSwipeCardRecord';
    const ALARM_URI = '/GetAlarmRecord';
    const SET_PERMISSION = '/SetCardPermission';
    const SESSION_ID = 'SessionId=';
    const BEGIN_TIME = '&BeginTime=';
    const END_TIME = '&EndTime=';
    const TIME_PERIOD = '&TimePeriod=';
    const CARD_PERMISSION = '&CardPermission=';
    private static $serverIP = [
        1 => self::BASE_URI,
    ];

    public static function getBuildingIdToBaseURL()
    {
        return self::$serverIP;
    }

    public static function getBaseURL($buildingId)
    {
        if (array_key_exists($buildingId, self::getBuildingIdToBaseURL())) {
            return self::getBuildingIdToBaseURL()[$buildingId];
        }

        throw new NotFoundHttpException('Building Does Not Exist!');
    }

    /**
     * @return mixed
     */
    public function getSessionId($buildingId)
    {
        $base = $this->getBaseURL($buildingId);
        $data = 'Username=admin&Password=admin';
        $sessionArray = $this->postDoorApi($base.self::LOGIN_URI, $data);
        $sessionId = $sessionArray['login_session']['SeesionId']; //SeesionId typo in API

        return $sessionId;
    }

    /**
     * @param $sessionId
     */
    public function logOut($sessionId, $buildingId)
    {
        $base = $this->getBaseURL($buildingId);
        $data = self::SESSION_ID.$sessionId;
        $this->postDoorApi($base.self::GET_DOOR_URI, $data);
    }

    /**
     * @Post("/doors/time")
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
     */
    public function setTimePeriodAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $buildingId = $paramFetcher->get('building');
        $sessionId = $this->getSessionId($buildingId);
        try {
            $data = [
                'ads_timeperiod' => [
                    'id' => '1',
                    'name' => 'time1',
                    'begindate' => '2000-07-01',
                    'enddate' => '2099-07-01',
                    'Mon' => '1',
                    'Tues' => '1',
                    'Weds' => '1',
                    'Thurs' => '1',
                    'Fri' => '1',
                    'Sat' => '1',
                    'Sun' => '1',
                    'times' => [
                            ['begin' => '00:00:00', 'end' => '23:59:59'],
                        ],
                ],
            ];
            $json = json_encode($data);
            $data = self::SESSION_ID.$sessionId.self::TIME_PERIOD.$json;

            $base = $this->getBaseURL($buildingId);
            $periodArray = $this->postDoorApi($base.self::SET_TIME, $data);
            $this->logOut($sessionId, $buildingId);
            if ($periodArray['ads_result']['result'] !== self::RESULT_OK) {
                return $this->customErrorView(
                    400,
                    self::RESPONSE_NOT_VALID_CODE,
                    self::RESPONSE_NOT_VALID_MESSAGE
                );
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $buildingId);
            }
        }
    }

    /**
     * @Post("/doors/permission/add")
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
    public function setCardPermissionAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $buildingId = $paramFetcher->get('building');
        $sessionId = $this->getSessionId($buildingId);
        try {
            $data = [
                'ads_card' => [
                    'empid' => '123456', //from user account
                    'empname' => 'Leo', //from user account
                    'department' => 'Sandhill',
                    'cardno' => '1660672', //from user account
                    'begindate' => '2015-07-16 08:00:00',
                    'expiredate' => '2015-09-01 18:00:00',
                    'operation' => 'add',
                ],
                'ads_door_permissions' => [
                    ['doorid' => '{4B169885-76B7-4215-B3F3-318553AC0087}', 'timeperiodid' => '1'],
                ],
            ];
            $json = json_encode($data);
            $data = self::SESSION_ID.$sessionId.self::CARD_PERMISSION.$json;

            $base = $this->getBaseURL($buildingId);
            $periodArray = $this->postDoorApi($base.self::SET_PERMISSION, $data);
            $this->logOut($sessionId, $buildingId);

            if ($periodArray['ads_result']['result'] !== self::RESULT_OK) {
                return $this->customErrorView(
                    400,
                    self::RESPONSE_NOT_VALID_CODE,
                    self::RESPONSE_NOT_VALID_MESSAGE
                );
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $buildingId);
            }
        }
    }

    /**
     * @Post("/doors/permission/remove")
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
    public function removeCardPermissionAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $buildingId = $paramFetcher->get('building');
        $sessionId = $this->getSessionId($buildingId);
        try {
            $data = [
                'ads_card' => [
                    'empid' => '123456', //from user account
                    'empname' => 'leo', //from user account
                    'department' => 'Sandhill',
                    'cardno' => '1660672', //from user account 1660672
                    'begindate' => '2015-07-16 08:00:00',
                    'expiredate' => '2015-09-01 18:00:00',
                    'operation' => 'delete',
                ],
                'ads_door_permissions' => [
                    ['doorid' => '{4B169885-76B7-4215-B3F3-318553AC0087}', 'timeperiodid' => '1'],
                ],
            ];
            $json = json_encode($data);
            $data = self::SESSION_ID.$sessionId.self::CARD_PERMISSION.$json;

            $base = $this->getBaseURL($buildingId);
            $periodArray = $this->postDoorApi($base.self::SET_PERMISSION, $data);
            $this->logOut($sessionId, $buildingId);

            if ($periodArray['ads_result']['result'] !== self::RESULT_OK) {
                return $this->customErrorView(
                    400,
                    self::RESPONSE_NOT_VALID_CODE,
                    self::RESPONSE_NOT_VALID_MESSAGE
                );
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $buildingId);
            }
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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getRecordsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
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

            return new View($recordArray['ads_swipecard_records']);
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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getAlarmsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
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

            return new View($recordArray['ads_alarm_records']);
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $buildingId);
            }
        }
    }
}
