<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Constants\BundleConstants;
use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Entity\Door\DoorAccess;
use Sandbox\ApiBundle\Entity\Door\DoorDepartmentUsers;
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
 * @see     http://www.Sandbox.cn/
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
        if ('POST' === $method) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } elseif ('PUT' === $method || 'DELETE' === $method) {
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
        $globals = $this->getContainer()
                        ->get('twig')
                        ->getGlobals();

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
        $globals = $this->getContainer()
                        ->get('twig')
                        ->getGlobals();

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
        $globals = $this->getContainer()
                        ->get('twig')
                        ->getGlobals();

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

        $globals = $this->getContainer()
                        ->get('twig')
                        ->getGlobals();

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

            if (DoorAccessConstants::RESULT_OK != $periodArray['result']) {
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
     * @param $userId
     * @param $name
     * @param $cardNumber
     * @param $method
     */
    public function setMembershipEmployeeCard(
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

        $globals = $this->getContainer()
            ->get('twig')
            ->getGlobals();

        $sessionId = null;

        try {
            $sessionId = $this->getSessionId($base);

            $data = [
                'ads_emp_card' => [
                    'empid' => "$userId", //from user account
                    'empname' => $name, //from user account
                    'department' => 'SANDBOX3',
                    'cardno' => $cardNumber,
                    'expiredate' => '2099-07-01 08:00:00',
                    'operation' => $method,
                ],
            ];
            $json = json_encode($data);
            $data = $globals['door_api_session_id'].$sessionId.'&'.$globals['door_api_employee_card'].$json;

            $periodArray = $this->postDoorApi($base.$globals['door_api_set_employee_card'], $data);
            $this->logOut($sessionId, $base);

            if (DoorAccessConstants::RESULT_OK != $periodArray['result']) {
                error_log('Door Access Error');
            } else {
                $em = $this->getContainer()->get('doctrine')->getManager();

                $departmentUser = $this->getContainer()
                    ->get('doctrine')
                    ->getRepository('SandboxApiBundle:Door\DoorDepartmentUsers')
                    ->findBy(array(
                        'userId' => $userId,
                        'buildingServer' => $base,
                    ));
                if (DoorAccessConstants::METHOD_ADD == $method) {
                    if (!$departmentUser) {
                        $departmentUser = new DoorDepartmentUsers();
                        $departmentUser->setUserId($userId);
                        $departmentUser->setBuildingServer($base);
                        $em->persist($departmentUser);
                        $em->flush();
                    }
                } elseif (DoorAccessConstants::METHOD_DELETE == $method) {
                    if ($departmentUser) {
                        foreach ($departmentUser as $dUser) {
                            $em->remove($dUser);
                        }
                        $em->flush();
                    }
                }
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
     * @param $accessNo
     * @param $start
     * @param $end
     * @param $doorArray
     */
    public function setRoomOrderPermission(
        $base,
        $userArray,
        $accessNo,
        $start,
        $end,
        $doorArray
    ) {
        $startHour = (string) $start->format('H:i:s');
        $endHour = (string) $end->format('H:i:s');
        $startDate = (string) $start->format('Y-m-d');
        $endDate = (string) $end->format('Y-m-d');
        $sessionId = null;

        $globals = $this->getContainer()
                        ->get('twig')
                        ->getGlobals();

        try {
            $sessionId = $this->getSessionId($base);

            $data = [
                'ads_room_order' => [
                    'orderno' => "$accessNo", //from user account
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

            if (DoorAccessConstants::RESULT_OK == $periodArray['result']) {
                $this->updateDoorAccess(
                    $userArray,
                    $accessNo
                );
            } elseif ('订单号重复，不能添加订单' == $periodArray['exceptionmsg']) {
                $this->addEmployeeToOrder(
                    $base,
                    $accessNo,
                    $userArray
                );
            } elseif (DoorAccessConstants::RESULT_OK != $periodArray['result']) {
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
     * @param $accessNo
     */
    public function repealRoomOrder(
        $base,
        $accessNo
    ) {
        $sessionId = null;

        $globals = $this->getContainer()
                        ->get('twig')
                        ->getGlobals();

        try {
            $sessionId = $this->getSessionId($base);

            $data = $globals['door_api_session_id'].$sessionId.'&'.$globals['door_api_order_no'].$accessNo;

            $periodArray = $this->postDoorApi($base.$globals['door_api_repeal_room_order'], $data);
            $this->logOut($sessionId, $base);

            if (DoorAccessConstants::RESULT_OK == $periodArray['result']) {
                $this->updateDoorAccess(
                    null,
                    $accessNo,
                    ProductOrder::STATUS_CANCELLED
                );
            }

            if (DoorAccessConstants::RESULT_OK != $periodArray['result']) {
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
     * @param $accessNo
     * @param $userArray
     */
    public function addEmployeeToOrder(
        $base,
        $accessNo,
        $userArray
    ) {
        $sessionId = null;

        $globals = $this->getContainer()
                        ->get('twig')
                        ->getGlobals();

        try {
            $sessionId = $this->getSessionId($base);

            $data = [
                'ads_room_order_add_emp' => [
                    'orderno' => "$accessNo", //from user account
                    'emps' => $userArray,
                ],
            ];
            $json = json_encode($data);
            $data = $globals['door_api_session_id'].$sessionId.'&'.$globals['door_api_add_emp'].$json;

            $periodArray = $this->postDoorApi($base.$globals['door_api_order_add_emp'], $data);
            $this->logOut($sessionId, $base);

            if (DoorAccessConstants::RESULT_OK == $periodArray['result']) {
                $this->updateDoorAccess(
                    $userArray,
                    $accessNo
                );
            }
            if (DoorAccessConstants::RESULT_OK != $periodArray['result']) {
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
     * @param $accessNo
     * @param $userArray
     */
    public function deleteEmployeeToOrder(
        $base,
        $accessNo,
        $userArray
    ) {
        $sessionId = null;

        $globals = $this->getContainer()
                        ->get('twig')
                        ->getGlobals();

        try {
            $sessionId = $this->getSessionId($base);

            $data = [
                'ads_room_order_del_emp' => [
                    'orderno' => "$accessNo", //from user account
                    'emps' => $userArray,
                ],
            ];
            $json = json_encode($data);
            $data = $globals['door_api_session_id'].$sessionId.'&'.$globals['door_api_delete_emp'].$json;

            $periodArray = $this->postDoorApi($base.$globals['door_api_order_delete_emp'], $data);
            $this->logOut($sessionId, $base);

            if (DoorAccessConstants::RESULT_OK == $periodArray['result']) {
                $this->updateDoorAccess(
                    $userArray,
                    $accessNo,
                    DoorAccessConstants::METHOD_DELETE
                );
            }

            if (DoorAccessConstants::RESULT_OK != $periodArray['result']) {
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
                $building = $this->getContainer()
                                 ->get('doctrine')
                                 ->getRepository(BundleConstants::BUNDLE.':'.'Room\RoomBuilding')
                                 ->find($buildingId['buildingId']);

                if (is_null($building)) {
                    continue;
                }

                $server['server'] = $building->getServer();
                array_push($servers, $server);
            }
        } else {
            $servers = $this->getContainer()
                            ->get('doctrine')
                            ->getRepository(BundleConstants::BUNDLE.':'.'Room\RoomBuilding')
                            ->getDistinctServers();
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
        $departmentUser = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Door\DoorDepartmentUsers')
            ->findOneBy(array(
                'userId' => $userId,
                'buildingServer' => $base,
            ));

        if (!$departmentUser) {
            $userProfile = $this->getContainer()
                ->get('doctrine')
                ->getRepository(BundleConstants::BUNDLE.':'.'User\UserProfile')
                ->findOneByUserId($userId);

            $userName = $userProfile->getName();

            $this->setEmployeeCard(
                $base,
                $userId,
                $userName,
                $cardNo,
                DoorAccessConstants::METHOD_ADD
            );
        }
    }

    /**
     * @param $base
     * @param $userId
     * @param $cardNo
     */
    public function setMembershipEmployeeCardForOneBuilding(
        $base,
        $userId,
        $cardNo
    ) {
        $departmentUser = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Door\DoorDepartmentUsers')
            ->findOneBy(array(
                'userId' => $userId,
                'buildingServer' => $base,
            ));

        if (!$departmentUser) {
            $userProfile = $this->getContainer()
                ->get('doctrine')
                ->getRepository(BundleConstants::BUNDLE.':'.'User\UserProfile')
                ->findOneByUserId($userId);

            $userName = $userProfile->getName();

            //remove user from 'SANDBOX' GROUP
            $this->setEmployeeCard(
                $base,
                $userId,
                $userName,
                $cardNo,
                DoorAccessConstants::METHOD_DELETE
            );

            sleep(500);

            // add user to 'SANDBOX3' GROUP
            $this->setMembershipEmployeeCard(
                $base,
                $userId,
                $userName,
                $cardNo,
                DoorAccessConstants::METHOD_ADD
            );
        }
    }

    /**
     * @param $base
     * @param $userArray
     * @param $roomDoors
     * @param $accessNo
     * @param $startDate
     * @param $endDate
     */
    public function setRoomOrderAccessIfUserArray(
        $base,
        $userArray,
        $roomDoors,
        $accessNo,
        $startDate,
        $endDate
    ) {
        $doorArray = [];
        foreach ($roomDoors as $roomDoor) {
            $door = ['doorid' => $roomDoor->getDoorControlId()];
            array_push($doorArray, $door);
        }

        $this->setRoomOrderPermission(
            $base,
            $userArray,
            $accessNo,
            $startDate,
            $endDate,
            $doorArray
        );
    }

    /**
     * @param $userArray
     * @param $accessNo
     */
    protected function updateDoorAccess(
        $userArray,
        $accessNo,
        $status = DoorAccessConstants::METHOD_ADD
    ) {
        $em = $this->getContainer()->get('doctrine')->getManager();

        if (is_null($userArray)) {
            $doors = $this->getContainer()
                          ->get('doctrine')
                          ->getRepository(BundleConstants::BUNDLE.':'.'Door\DoorAccess')
                          ->findBy(
                              array(
                                  'accessNo' => $accessNo,
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

                $doors = $this->getContainer()
                              ->get('doctrine')
                              ->getRepository(BundleConstants::BUNDLE.':'.'Door\DoorAccess')
                              ->findBy(
                                  array(
                                      'userId' => $userId,
                                      'accessNo' => $accessNo,
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

    /**
     * @param $em
     * @param $accessNumber
     * @param $userId
     * @param $buildingId
     * @param $roomId
     * @param $startDate
     * @param $endDate
     */
    public function storeDoorAccess(
        $em,
        $accessNumber,
        $userId,
        $buildingId,
        $roomId,
        $startDate,
        $endDate
    ) {
        $doorAccess = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Door\DoorAccess')
            ->findOneBy(
            [
                'userId' => $userId,
                'accessNo' => $accessNumber,
                'buildingId' => $buildingId,
            ]
        );

        if (is_null($doorAccess)) {
            $doorAccess = new DoorAccess();
            $doorAccess->setBuildingId($buildingId);
            $doorAccess->setUserId($userId);
            $doorAccess->setRoomId($roomId);
            $doorAccess->setAccessNo($accessNumber);
        } else {
            $doorAccess->setAction(DoorAccessConstants::METHOD_ADD);
        }

        $doorAccess->setStartDate($startDate);
        $doorAccess->setEndDate($endDate);
        $doorAccess->setAccess(false);

        $em->persist($doorAccess);
    }

    /**
     * @param $accessNo
     * @param $userId
     * @param $method
     */
    public function setAccessActionToDelete(
        $accessNo,
        $userId = null,
        $method = DoorAccessConstants::METHOD_CANCELLED
    ) {
        $controls = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:Door\DoorAccess')
            ->getAddAccessByOrder(
                $userId,
                $accessNo
            );

        if (!empty($controls)) {
            foreach ($controls as $control) {
                $control->setAction($method);
                $control->isAccess() ? $control->setAccess(false) : $control->setAccess(true);
            }
        }
    }

    /**
     * @param $userIds
     * @param $doorBuildingIds
     */
    public function addUserDoorAccess(
        $userIds,
        $doorBuildingIds
    ) {
        foreach ($doorBuildingIds as $buildingId) {
            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($buildingId);

            $base = $building->getServer();
            if (is_null($base) || empty($base)) {
                continue;
            }

            $em = $this->getDoctrine()->getManager();

            foreach ($userIds as $userId) {
                $result = $this->getCardNoByUser($userId);
                if (
                    !is_null($result) &&
                    DoorController::STATUS_AUTHED === $result['status']
                ) {
                    $this->setMembershipEmployeeCardForOneBuilding(
                        $base,
                        $userId,
                        $result['card_no']
                    );
                }
            }

            $em->flush();
        }
    }
}
