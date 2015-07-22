<?php

namespace Sandbox\ApiBundle\Controller\Door;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;

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

    public function cardPermission(
        $userId,
        $name,
        $buildingId,
        $cardNumber,
        $startDate,
        $endDate,
        $doorId,
        $method
    ) {
        $sessionId = $this->getSessionId($buildingId);
        try {
            $data = [
                'ads_card' => [
                    'empid' => "$userId", //from user account
                    'empname' => $name, //from user account
                    'department' => 'BUILDING'."$buildingId",
                    'cardno' => $cardNumber, //from user account
                    'begindate' => $startDate,
                    'expiredate' => $endDate,
                    'operation' => $method,
                ],
                'ads_door_permissions' => [
                    ['doorid' => $doorId, 'timeperiodid' => '1'],
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

    public function getPermission(
        $userId,
        $name,
        ProductOrder $order,
        $method
    ) {
        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $cardNumber = '1660672'; //from CRM using userId
        $startDate = (string) $order->getStartDate()->format('Y-m-d H:i:s');
        $endDate = (string) $order->getEndDate()->format('Y-m-d H:i:s');
        $doorId = $order->getProduct()->getRoom()->getDoorControlId();

        $this->cardPermission(
            $userId,
            $name,
            $buildingId,
            $cardNumber,
            $startDate,
            $endDate,
            $doorId,
            $method
        );
    }
}
