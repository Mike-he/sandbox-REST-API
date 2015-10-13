<?php

namespace Sandbox\ApiBundle\Controller\Door;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Door Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class DoorController extends SandboxRestController
{
    const RESULT_OK = 'RESULT_OK';
    const METHOD_ADD = 'add';
    const METHOD_DELETE = 'delete';
    const METHOD_LOST = 'lost';
    const METHOD_UNLOST = 'unlost';
    const METHOD_CHANGE_CARD = 'changecard';
    const RESPONSE_NOT_VALID_CODE = 400005;
    const RESPONSE_NOT_VALID_MESSAGE = 'Response Not Valid';
    const TIME_NOT_VALID_CODE = 400006;
    const TIME_NOT_VALID_MESSAGE = 'Times Are Not Valid';
    const NO_ORDER_CODE = 400007;
    const NO_ORDER_MESSAGE = 'Orders Not Found';
    const BUILDING_NOT_FOUND_CODE = 400015;
    const BUILDING_NOT_FOUND_MESSAGE = 'Building Not Found';
    const CARDNO_NOT_FOUND_CODE = 400008;
    const CARDNO_NOT_FOUND_MESSAGE = 'Cardno Not Found';

    public function callDoorApi(
        $ch,
        $method,
        $data
    ) {
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } elseif ($method === 'PUT' || $method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function getArray($xml)
    {
        $crawler = new Crawler($xml);
        $content = $crawler->text();
        $xmlArray = json_decode($content, true);

        return $xmlArray;
    }

    public function getDoorApi($url)
    {
        $ch = curl_init($url);
        $response = $this->callDoorApi($ch, 'GET', null);
        $xmlArray = $this->getArray($response);

        return $xmlArray;
    }

    public function postDoorApi($url, $data)
    {
        $ch = curl_init($url);
        $response = $this->callDoorApi($ch, 'POST', $data);
        $xmlArray = $this->getArray($response);

        return $xmlArray;
    }

    /**
     * @return mixed
     */
    public function getSessionId($base, $globals)
    {
        $data = 'Username='.$globals['door_api_username'].
            '&Password='.$globals['door_api_password'];
        $sessionArray = $this->postDoorApi($base.$globals['door_api_login'], $data);
        $sessionId = $sessionArray['login_session']['SeesionId']; //SeesionId typo in API

        return $sessionId;
    }

    /**
     * @param $sessionId
     */
    public function logOut($sessionId, $base, $globals)
    {
        $data = $globals['door_api_session_id'].$sessionId;
        $this->postDoorApi($base.$globals['door_api_logout'], $data);
    }

    /**
     * @param $base
     * @param $userId
     * @param $name
     * @param $cardNumber
     * @param $method
     * @param $globals
     */
    public function setEmployeeCard(
        $base,
        $userId,
        $name,
        $cardNumber,
        $method,
        $globals
    ) {
        $sessionId = $this->getSessionId($base, $globals);
        try {
            $data = [
                'ads_emp_card' => [
                    'empid' => "$userId", //from user account
                    'empname' => $name, //from user account
                    'department' => 'SANDBOX',
                    'cardno' => $cardNumber,
                    'expiredate' => '2099-07-01 08:00:00',
                    'operation' => $method,
                ],
            ];
            $json = json_encode($data);
            $data = $globals['door_api_session_id'].$sessionId.'&'.$globals['door_api_employee_card'].$json;

            $periodArray = $this->postDoorApi($base.$globals['door_api_set_employee_card'], $data);
            $this->logOut($sessionId, $base, $globals);

            if ($periodArray['result'] != self::RESULT_OK) {
                error_log('Door Access Error');
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $base, $globals);
            }
        }
    }

    /**
     * @param $base
     * @param $userId
     * @param $orderId
     * @param $start
     * @param $end
     * @param $doorArray
     * @param $globals
     */
    public function setRoomOrderPermission(
        $base,
        $userArray,
        $orderId,
        $start,
        $end,
        $doorArray,
        $globals
    ) {
        $startHour = (string) $start->format('H:i:s');
        $endHour = (string) $end->format('H:i:s');
        $startDate = (string) $start->format('Y-m-d');
        $endDate = (string) $end->format('Y-m-d');
        $sessionId = $this->getSessionId($base, $globals);
        try {
            $data = [
                'ads_room_order' => [
                    'orderno' => "$orderId", //from user account
                    'emps' => $userArray,
                    'doors' => $doorArray,
                ],
                'ads_timeperiod' => [
                    'begindate' => $startDate,
                    'enddate' => $endDate,
                    'times' => [
                        ['begin' => $startHour, 'end' => $endHour],
                    ],
                ],
            ];
            $json = json_encode($data);
            $data = $globals['door_api_session_id'].$sessionId.'&'.$globals['door_api_room_order'].$json;

            $periodArray = $this->postDoorApi($base.$globals['door_api_set_room_order'], $data);
            $this->logOut($sessionId, $base, $globals);

            if ($periodArray['exceptionmsg'] == '订单号重复，不能添加订单') {
                $this->addEmployeeToOrder(
                    $base,
                    $orderId,
                    $userArray,
                    $globals
                );
            } elseif ($periodArray['result'] != self::RESULT_OK) {
                error_log('Door Access Error');
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $base, $globals);
            }
        }
    }

    /**
     * @param $base
     * @param $orderId
     * @param $globals
     */
    public function repealRoomOrder(
        $base,
        $orderId,
        $globals
    ) {
        $sessionId = $this->getSessionId($base, $globals);
        try {
            $data = $globals['door_api_session_id'].$sessionId.'&'.$globals['door_api_order_no'].$orderId;

            $periodArray = $this->postDoorApi($base.$globals['door_api_repeal_room_order'], $data);
            $this->logOut($sessionId, $base, $globals);

            if ($periodArray['result'] != self::RESULT_OK) {
                error_log('Door Access Error');
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $base, $globals);
            }
        }
    }

    /**
     * @param $base
     * @param $orderId
     * @param $userArray
     * @param $globals
     */
    public function addEmployeeToOrder(
        $base,
        $orderId,
        $userArray,
        $globals
    ) {
        $sessionId = $this->getSessionId($base, $globals);
        try {
            $data = [
                'ads_room_order_add_emp' => [
                    'orderno' => "$orderId", //from user account
                    'emps' => $userArray,
                ],
            ];
            $json = json_encode($data);
            $data = $globals['door_api_session_id'].$sessionId.'&'.$globals['door_api_add_emp'].$json;

            $periodArray = $this->postDoorApi($base.$globals['door_api_order_add_emp'], $data);
            $this->logOut($sessionId, $base, $globals);

            if ($periodArray['result'] != self::RESULT_OK) {
                error_log('Door Access Error');
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $base, $globals);
            }
        }
    }

    /**
     * @param $base
     * @param $orderId
     * @param $userArray
     * @param $globals
     */
    public function deleteEmployeeToOrder(
        $base,
        $orderId,
        $userArray,
        $globals
    ) {
        $sessionId = $this->getSessionId($base, $globals);
        try {
            $data = [
                'ads_room_order_del_emp' => [
                    'orderno' => "$orderId", //from user account
                    'emps' => $userArray,
                ],
            ];
            $json = json_encode($data);
            $data = $globals['door_api_session_id'].$sessionId.'&'.$globals['door_api_delete_emp'].$json;

            $periodArray = $this->postDoorApi($base.$globals['door_api_order_delete_emp'], $data);
            $this->logOut($sessionId, $base, $globals);

            if ($periodArray['result'] != self::RESULT_OK) {
                error_log('Door Access Error');
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $base, $globals);
            }
        }
    }

//    public function setTimePeriod(
//        $updatedDoors,
//        $base,
//        $globals
//    ) {
//        $id = $updatedDoors[0]->getTimeId();
//
//        $timeArray = [];
//        foreach ($updatedDoors as $updatedDoor) {
//            $start = $updatedDoor->getStartDate();
//            $end = $updatedDoor->getEndDate();
//            $startHour = (string) $start->format('H:i:s');
//            $endHour = (string) $end->format('H:i:s');
//            $startDate = (string) $start->format('Y-m-d');
//            $endDate = (string) $end->format('Y-m-d');
//            $timePeriod = [
//                'begindate' => $startDate,
//                'enddate' => $endDate,
//                'Mon' => '1',
//                'Tues' => '1',
//                'Weds' => '1',
//                'Thurs' => '1',
//                'Fri' => '1',
//                'Sat' => '1',
//                'Sun' => '1',
//                'times' => [
//                    ['begin' => $startHour, 'end' => $endHour],
//                ],
//            ];
//            array_push($timeArray, $timePeriod);
//        }
//
//        $sessionId = $this->getSessionId($base, $globals);
//
//        try {
//            $data = [
//                'ads_timeperiod' => [
//                    'id' => "$id",
//                    'name' => 'time',
//                    'ads_timeperiods' => $timeArray,
//                ],
//            ];
//            $json = json_encode($data);
//            $data = $globals['door_api_session_id'].$sessionId.'&'.$globals['door_api_time_period'].$json;
//
//            $periodArray = $this->postDoorApi($base.$globals['door_api_set_time'], $data);
//            $this->logOut($sessionId, $base, $globals);
//
//            if ($periodArray['result'] != self::RESULT_OK) {
//                error_log('Door Access Error');
//            }
//        } catch (\Exception $e) {
//            if (!is_null($sessionId) && !empty($sessionId)) {
//                $this->logOut($sessionId, $base, $globals);
//            }
//        }
//    }
}
