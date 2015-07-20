<?php

namespace Sandbox\AdminApiBundle\Controller\Door;

use Sandbox\ApiBundle\Controller\Door\DoorController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
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
    const LOGIN_URI = 'http://211.95.45.26:13390/ADSWebService.asmx/Login?Username=admin&Password=admin';
    const LOGOUT_URI = 'http://211.95.45.26:13390/ADSWebService.asmx/Logout?SessionId=';
    const GET_DOOR_URI = 'http://211.95.45.26:13390/ADSWebService.asmx/GetDoors?SessionId=';
    const SET_TIME = 'http://211.95.45.26:13390/ADSWebService.asmx/SetTimePeriod?SessionId=';
    const RECORD_URI = 'http://211.95.45.26:13390/ADSWebService.asmx/GetSwipeCardRecord?SessionId=';
    const ALARM_URI = 'http://211.95.45.26:13390/ADSWebService.asmx/GetAlarmRecord?SessionId=';
    const SET_PERMISSION = 'http://211.95.45.26:13390/ADSWebService.asmx/SetCardPermission?SessionId=';
    const BEGIN_TIME = '&BeginTime=';
    const END_TIME = '&EndTime=';

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        $sessionArray = $this->getDoorApi(self::LOGIN_URI);
        $sessionId = $sessionArray['login_session']['SeesionId']; //SeesionId typo in API

        return $sessionId;
    }

    /**
     * @param $sessionId
     */
    public function logOut($sessionId)
    {
        $this->getDoorApi(self::GET_DOOR_URI.$sessionId);
    }

    /**
     * @Post("/doors/time")
     *
     * @param Request $request
     */
    public function setTimePeriodAction(
        Request $request
    ) {
        $sessionId = $this->getSessionId();
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
            $data = json_encode($data);

            $periodArray = $this->getDoorApi(self::SET_TIME.$sessionId.'&TimePeriod='.$data);
            $this->logOut($sessionId);
            if ($periodArray['ads_result']['result'] !== self::RESULT_OK) {
                return $this->customErrorView(
                    400,
                    self::RESPONSE_NOT_VALID_CODE,
                    self::RESPONSE_NOT_VALID_MESSAGE
                );
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId);
            }
        }
        //TODO: store to database
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
        $sessionId = $this->getSessionId();
        try {
            $data = [
                'ads_card' => [
                    'empid' => '123456', //from user account
                    'empname' => 'Max', //from user account
                    'department' => 'Sandhill',
                    'cardno' => '3035172', //from user account
                    'begindate' => '2015-07-16 08:00:00',
                    'expiredate' => '2015-09-01 18:00:00',
                    'operation' => 'add',
                ],
                'ads_door_permissions' => [
                    ['doorid' => '{4B169885-76B7-4215-B3F3-318553AC0087}', 'timeperiodid' => '1'],
                ],
            ];
            $data = json_encode($data);

            $url = self::SET_PERMISSION.$sessionId.'&CardPermission='.$data;
            $url = urlencode($url);
            $url = rawurldecode($url);

            $periodArray = $this->getDoorApi($url);
            $this->logOut($sessionId);

            if ($periodArray['ads_result']['result'] !== self::RESULT_OK) {
                return $this->customErrorView(
                    400,
                    self::RESPONSE_NOT_VALID_CODE,
                    self::RESPONSE_NOT_VALID_MESSAGE
                );
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId);
            }
        }
    }

    /**
     * @Delete("/doors/permission/remove")
     *
     * @param Request $request
     *
     * @return View
     */
    public function removeCardPermissionAction(
        Request $request
    ) {
        $sessionId = $this->getSessionId();
        try {
            $data = [
                'ads_card' => [
                    'empid' => '123456', //from user account
                    'empname' => 'Leo', //from user account
                    'department' => 'Sandhill',
                    'cardno' => '1660672', //from user account
                    'begindate' => '2015-07-16 08:00:00',
                    'expiredate' => '2015-09-01 18:00:00',
                    'operation' => 'delete',
                ],
                'ads_door_permissions' => [
                    ['doorid' => '{4B169885-76B7-4215-B3F3-318553AC0087}', 'timeperiodid' => '1'],
                ],
            ];
            $data = json_encode($data);

            $url = self::SET_PERMISSION.$sessionId.'&CardPermission='.$data;
            $url = urlencode($url);
            $url = rawurldecode($url);

            $periodArray = $this->getDoorApi($url);
            $this->logOut($sessionId);

            if ($periodArray['ads_result']['result'] !== self::RESULT_OK) {
                return $this->customErrorView(
                    400,
                    self::RESPONSE_NOT_VALID_CODE,
                    self::RESPONSE_NOT_VALID_MESSAGE
                );
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId);
            }
        }
    }

    /**
     * @Get("/doors")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getDoorsAction(
        Request $request
    ) {
        $sessionId = $this->getSessionId();
        try {
            $doorArray = $this->getDoorApi(self::GET_DOOR_URI.$sessionId);
            $this->logOut($sessionId);
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
                $this->logOut($sessionId);
            }
        }
    }

    /**
     * @Get("/doors/records")
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
        $sessionId = $this->getSessionId();
        try {
            $begin = $paramFetcher->get('begin_time');
            $end = $paramFetcher->get('end_time');
            if (is_null($end) || empty($end)) {
                return $this->customErrorView(
                    400,
                    self::TIME_NOT_VALID_CODE,
                    self::TIME_NOT_VALID_MESSAGE
                );
            }

            $url = self::RECORD_URI.$sessionId.self::BEGIN_TIME.$begin.self::END_TIME.$end;
            $url = urlencode($url);
            $url = rawurldecode($url);

            $recordArray = $this->getDoorApi($url);
            $this->logOut($sessionId);
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
                $this->logOut($sessionId);
            }
        }
    }

    /**
     * @Get("/doors/alarms")
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
        $sessionId = $this->getSessionId();
        try {
            $begin = $paramFetcher->get('begin_time');
            $end = $paramFetcher->get('end_time');
            if (is_null($end) || empty($end)) {
                return $this->customErrorView(
                    400,
                    self::TIME_NOT_VALID_CODE,
                    self::TIME_NOT_VALID_MESSAGE
                );
            }

            $url = self::ALARM_URI.$sessionId.self::BEGIN_TIME.$begin.self::END_TIME.$end;
            $url = urlencode($url);
            $url = rawurldecode($url);

            $recordArray = $this->getDoorApi($url);
            $this->logOut($sessionId);
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
                $this->logOut($sessionId);
            }
        }
    }
}
