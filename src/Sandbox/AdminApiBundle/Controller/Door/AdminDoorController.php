<?php

namespace Sandbox\AdminApiBundle\Controller\Door;

use Sandbox\ApiBundle\Controller\Door\DoorController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
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
        $userId = 1;
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();
        $cardNumber = '9391756'; //from CRM using userId
        $startDate = '2015-07-16 08:00:00'; //from Order
        $endDate = '2015-09-01 18:00:00'; //from Order
        $doorId = '{4B169885-76B7-4215-B3F3-318553AC0087}'; //from Room
        $buildingId = $paramFetcher->get('building');
        $this->cardPermission(
            $userId,
            $userName,
            $buildingId,
            $cardNumber,
            $startDate,
            $endDate,
            $doorId,
            self::METHOD_ADD
        );
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
        $userId = 1;
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();
        $cardNumber = '1660672'; //from CRM using userId
        $startDate = '2015-07-16 08:00:00'; //from Order
        $endDate = '2015-09-01 18:00:00'; //from Order
        $doorId = '{4B169885-76B7-4215-B3F3-318553AC0087}'; //from Room
        $buildingId = $paramFetcher->get('building');
        $this->cardPermission(
            $userId,
            $userName,
            $buildingId,
            $cardNumber,
            $startDate,
            $endDate,
            $doorId,
            self::METHOD_DELETE
        );
    }

    /**
     * @Post("/doors/permission/lost")
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
    public function lostCardPermissionAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $buildingId = $paramFetcher->get('building');
        $cardNumber = '1660672'; //POST JSON
        $userId = '123456'; //POST JSON
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();
        $sessionId = $this->getSessionId($buildingId);
        try {
            $data = [
                'ads_card' => [
                    'empid' => $userId, //from user account
                    'empname' => $userName, //from user account
                    'department' => 'BUILDING'."$buildingId",
                    'cardno' => $cardNumber, //from user account 1660672
                    'begindate' => '2015-07-16 08:00:00',
                    'expiredate' => '2015-09-01 18:00:00',
                    'operation' => self::METHOD_LOST,
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
     * @Post("/doors/permission/unlost")
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
    public function unlostCardPermissionAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $buildingId = $paramFetcher->get('building');
        $cardNumber = '1660672'; //POST JSON
        $userId = '123456'; //POST JSON
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();
        $sessionId = $this->getSessionId($buildingId);
        try {
            $data = [
                'ads_card' => [
                    'empid' => $userId, //from user account
                    'empname' => $userName, //from user account
                    'department' => 'BUILDING'."$buildingId",
                    'cardno' => $cardNumber, //from user account 1660672
                    'begindate' => '2015-07-16 08:00:00',
                    'expiredate' => '2015-09-01 18:00:00',
                    'operation' => self::METHOD_UNLOST,
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
     * @Post("/doors/permission/replace")
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
    public function replaceCardPermissionAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $buildingId = $paramFetcher->get('building');
        $cardNumber = '1660672'; //POST JSON
        $userId = '123456'; //POST JSON
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();
        $sessionId = $this->getSessionId($buildingId);
        try {
            $data = [
                'ads_card' => [
                    'empid' => $userId, //from user account
                    'empname' => $userName, //from user account
                    'department' => 'BUILDING'."$buildingId",
                    'cardno' => $cardNumber, //from user account 1660672
                    'begindate' => '2015-07-16 08:00:00',
                    'expiredate' => '2015-09-01 18:00:00',
                    'operation' => self::METHOD_CHANGE_CARD,
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
