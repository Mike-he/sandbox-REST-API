<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Symfony\Component\DomCrawler\Crawler;
use Sandbox\ApiBundle\Constants\DoorAccessConstants;

/**
 * Door Access Trait.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait DoorAccessTrait
{
    use CommonMethod;

    /**
     * @param $ch
     * @param $method
     * @param $data
     *
     * @return mixed
     */
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

    /**
     * @param $xml
     *
     * @return mixed
     */
    public function getArray($xml)
    {
        $crawler = new Crawler($xml);
        $content = $crawler->text();
        $xmlArray = json_decode($content, true);

        return $xmlArray;
    }

    /**
     * @param $url
     *
     * @return mixed
     */
    public function getDoorApi($url)
    {
        $ch = curl_init($url);
        $response = $this->callDoorApi($ch, 'GET', null);
        $xmlArray = $this->getArray($response);

        return $xmlArray;
    }

    /**
     * @param $url
     * @param $data
     *
     * @return mixed
     */
    public function postDoorApi(
        $url,
        $data = null
    ) {
        $ch = curl_init($url);
        $response = $this->callDoorApi($ch, 'POST', $data);
        $xmlArray = $this->getArray($response);

        return $xmlArray;
    }

    /**
     * @param $base
     *
     * @return mixed
     */
    public function getLastSyncTime(
        $base
    ) {
        $globals = $this->getGlobals();
        $ch = curl_init($base.$globals['door_api_get_last_sync_time']);
        $response = $this->callDoorApi($ch, 'POST', null);

        return $response;
    }

    /**
     * @param $base
     *
     * @return mixed
     */
    public function getSessionId($base)
    {
        $globals = $this->getGlobals();

        $data = 'Username='.$globals['door_api_username'].
            '&Password='.$globals['door_api_password'];
        $sessionArray = $this->postDoorApi($base.$globals['door_api_login'], $data);
        $sessionId = $sessionArray['login_session']['SeesionId']; //SeesionId typo in API

        return $sessionId;
    }

    /**
     * @param $sessionId
     * @param $base
     */
    public function logOut($sessionId, $base)
    {
        $globals = $this->getGlobals();

        $data = $globals['door_api_session_id'].$sessionId;
        $this->postDoorApi($base.$globals['door_api_logout'], $data);
    }

    /**
     * @param $base
     * @param $userId
     * @param $name
     * @param $cardNumber
     * @param $method
     */
    public function setEmployeeCard(
        $base,
        $userId,
        $name,
        $cardNumber,
        $method
    ) {
        if (is_null($userId)
            || is_null($cardNumber)
            || is_null($method)) {
            return;
        }

        $globals = $this->getGlobals();
        $sessionId = null;

        try {
            $sessionId = $this->getSessionId($base);

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
            $this->logOut($sessionId, $base);

            if ($periodArray['result'] != DoorAccessConstants::RESULT_OK) {
                error_log('Door Access Error');
            }
        } catch (\Exception $e) {
            error_log('Door Access Error');
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $base);
            }
        }
    }

    /**
     * @param $base
     * @param $userArray
     * @param $orderId
     * @param $start
     * @param $end
     * @param $doorArray
     */
    public function setRoomOrderPermission(
        $base,
        $userArray,
        $orderId,
        $start,
        $end,
        $doorArray
    ) {
        $startHour = (string) $start->format('H:i:s');
        $endHour = (string) $end->format('H:i:s');
        $startDate = (string) $start->format('Y-m-d');
        $endDate = (string) $end->format('Y-m-d');
        $sessionId = null;
        $globals = $this->getGlobals();

        try {
            $sessionId = $this->getSessionId($base);

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
            $this->logOut($sessionId, $base);

            if ($periodArray['result'] == DoorAccessConstants::RESULT_OK) {
                $this->updateDoorAccess(
                    $userArray,
                    $orderId
                );
            } elseif ($periodArray['exceptionmsg'] == '订单号重复，不能添加订单') {
                $this->addEmployeeToOrder(
                    $base,
                    $orderId,
                    $userArray
                );
            } elseif ($periodArray['result'] != DoorAccessConstants::RESULT_OK) {
                error_log('Door Access Error');
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $base);
            }
        }
    }

    /**
     * @param $base
     * @param $orderId
     */
    public function repealRoomOrder(
        $base,
        $orderId
    ) {
        $sessionId = null;
        $globals = $this->getGlobals();

        try {
            $sessionId = $this->getSessionId($base);

            $data = $globals['door_api_session_id'].$sessionId.'&'.$globals['door_api_order_no'].$orderId;

            $periodArray = $this->postDoorApi($base.$globals['door_api_repeal_room_order'], $data);
            $this->logOut($sessionId, $base);

            if ($periodArray['result'] == DoorAccessConstants::RESULT_OK) {
                $this->updateDoorAccess(
                    null,
                    $orderId,
                    ProductOrder::STATUS_CANCELLED
                );
            }

            if ($periodArray['result'] != DoorAccessConstants::RESULT_OK) {
                error_log('Door Access Error');
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $base);
            }
        }
    }

    /**
     * @param $base
     * @param $orderId
     * @param $userArray
     */
    public function addEmployeeToOrder(
        $base,
        $orderId,
        $userArray
    ) {
        $sessionId = null;
        $globals = $this->getGlobals();

        try {
            $sessionId = $this->getSessionId($base);

            $data = [
                'ads_room_order_add_emp' => [
                    'orderno' => "$orderId", //from user account
                    'emps' => $userArray,
                ],
            ];
            $json = json_encode($data);
            $data = $globals['door_api_session_id'].$sessionId.'&'.$globals['door_api_add_emp'].$json;

            $periodArray = $this->postDoorApi($base.$globals['door_api_order_add_emp'], $data);
            $this->logOut($sessionId, $base);

            if ($periodArray['result'] == DoorAccessConstants::RESULT_OK) {
                $this->updateDoorAccess(
                    $userArray,
                    $orderId
                );
            }
            if ($periodArray['result'] != DoorAccessConstants::RESULT_OK) {
                error_log('Door Access Error');
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $base);
            }
        }
    }

    /**
     * @param $base
     * @param $orderId
     * @param $userArray
     */
    public function deleteEmployeeToOrder(
        $base,
        $orderId,
        $userArray
    ) {
        $sessionId = null;
        $globals = $this->getGlobals();

        try {
            $sessionId = $this->getSessionId($base);

            $data = [
                'ads_room_order_del_emp' => [
                    'orderno' => "$orderId", //from user account
                    'emps' => $userArray,
                ],
            ];
            $json = json_encode($data);
            $data = $globals['door_api_session_id'].$sessionId.'&'.$globals['door_api_delete_emp'].$json;

            $periodArray = $this->postDoorApi($base.$globals['door_api_order_delete_emp'], $data);
            $this->logOut($sessionId, $base);

            if ($periodArray['result'] == DoorAccessConstants::RESULT_OK) {
                $this->updateDoorAccess(
                    $userArray,
                    $orderId,
                    DoorAccessConstants::METHOD_DELETE
                );
            }

            if ($periodArray['result'] != DoorAccessConstants::RESULT_OK) {
                error_log('Door Access Error');
            }
        } catch (\Exception $e) {
            if (!is_null($sessionId) && !empty($sessionId)) {
                $this->logOut($sessionId, $base);
            }
        }
    }

    /**
     * @param $userId
     * @param $userName
     * @param $cardNo
     * @param $method
     */
    public function updateEmployeeCardStatus(
        $userId,
        $userName,
        $cardNo,
        $method,
        $buildingIds = null
    ) {
        $servers = [];
        if (!is_null($buildingIds) && !empty($buildingIds)) {
            foreach ($buildingIds as $buildingId) {
                $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
                if (is_null($building)) {
                    continue;
                }
                $server['server'] = $building->getServer();
                array_push($servers, $server);
            }
        } else {
            $servers = $this->getRepo('Room\RoomBuilding')->getDistinctServers();
        }

        if (!empty($servers)) {
            foreach ($servers as $server) {
                try {
                    if (is_null($server['server']) || empty($server['server'])) {
                        continue;
                    }

                    $this->setEmployeeCard(
                        $server['server'],
                        $userId,
                        $userName,
                        $cardNo,
                        $method
                    );
                } catch (\Exception $e) {
                    error_log('Door Access Error, Update Card');
                    continue;
                }
            }
        }
    }

    /**
     * @param $base
     * @param $userId
     * @param $cardNo
     */
    public function setEmployeeCardForOneBuilding(
        $base,
        $userId,
        $cardNo
    ) {
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();
        $this->setEmployeeCard(
            $base,
            $userId,
            $userName,
            $cardNo,
            DoorAccessConstants::METHOD_ADD
        );
    }

    /**
     * @param $base
     * @param $userArray
     * @param $roomDoors
     * @param $order
     */
    public function setRoomOrderAccessIfUserArray(
        $base,
        $userArray,
        $roomDoors,
        $order
    ) {
        $orderId = $order->getId();
        $startDate = $order->getStartDate();
        $endDate = $order->getEndDate();
        $doorArray = [];
        foreach ($roomDoors as $roomDoor) {
            $door = ['doorid' => $roomDoor->getDoorControlId()];
            array_push($doorArray, $door);
        }

        $this->setRoomOrderPermission(
            $base,
            $userArray,
            $orderId,
            $startDate,
            $endDate,
            $doorArray
        );
    }

    /**
     * @param $userArray
     * @param $orderId
     */
    protected function updateDoorAccess(
        $userArray,
        $orderId,
        $status = DoorAccessConstants::METHOD_ADD
    ) {
        $em = $this->getContainer()->get('doctrine')->getManager();

        if (is_null($userArray)) {
            $doors = $this->getRepo('Door\DoorAccess')->findBy(
                array(
                    'orderId' => $orderId,
                    'access' => false,
                    'action' => $status,
                )
            );
            if (!empty($doors)) {
                foreach ($doors as $door) {
                    $door->setAccess(true);
                }
            }
        } else {
            foreach ($userArray as $user) {
                $userId = (int) $user['empid'];
                $doors = $this->getRepo('Door\DoorAccess')->findBy(
                    array(
                        'userId' => $userId,
                        'orderId' => $orderId,
                        'access' => false,
                        'action' => $status,
                    )
                );
                if (!empty($doors)) {
                    foreach ($doors as $door) {
                        $door->setAccess(true);
                    }
                }
            }
        }

        $em->flush();
    }
}
