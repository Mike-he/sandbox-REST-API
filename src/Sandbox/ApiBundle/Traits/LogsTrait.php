<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\Log\Log;

/**
 * Log Trait.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait LogsTrait
{
    use CommonMethod;

    /**
     * @param Log $log
     *
     * @return bool
     */
    protected function handleLog(
        $log
    ) {
        $objectKey = $log->getLogObjectKey();
        $objectId = $log->getLogObjectId();

        switch ($objectKey) {
            case Log::OBJECT_USER:
                $json = $this->getUserJson($objectId);
            break;
            case Log::OBJECT_BUILDING:
                $json = $this->getBuildingJson($objectId);
                break;
            case Log::OBJECT_SALES_ADMIN:
                $json = $this->getSalesAdminJson($objectId);
                break;
            default:
                return false;
        }

        if (!is_null($json)) {
            $log->setLogObjectJson($json);

            return true;
        }

        return false;
    }

    /**
     * @param $objectId
     *
     * @return string
     */
    private function getUserJson(
        $objectId
    ) {
        $userProfile = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:User\UserProfile')
            ->findOneBy(array(
                'userId' => $objectId,
            ));

        if (is_null($userProfile)) {
            return null;
        }

        return $this->transferToJson($userProfile);
    }

    /**
     * @param $objectId
     *
     * @return mixed|null
     */
    private function getBuildingJson(
        $objectId
    ) {
        $building = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->find($objectId);

        if (is_null($building)) {
            return null;
        }

        // set floor numbers
        $floors = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Room\RoomFloor')
            ->findByBuilding($building);
        $building->setFloors($floors);

        // set building attachments
        $buildingAttachments = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Room\RoomBuildingAttachment')
            ->findByBuilding($building);
        $building->setBuildingAttachments($buildingAttachments);

        // set building company
        $buildingCompany = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Room\RoomBuildingCompany')
            ->findOneByBuilding($building);
        $building->setBuildingCompany($buildingCompany);

        // set phones
        $phones = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Room\RoomBuildingPhones')
            ->findByBuilding($building);
        $building->setPhones($phones);

        // set shop counts
        $shopCounts = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Shop\Shop')
            ->countsShopByBuilding($building);
        $building->setShopCounts((int) $shopCounts);

        // set room counts
        $roomCounts = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Room\Room')
            ->countsRoomByBuilding($building);
        $building->setRoomCounts((int) $roomCounts);

        // set product counts
        $productCounts = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Product\Product')
            ->countsProductByBuilding($building);
        $building->setProductCounts((int) $productCounts);

        // set order counts
        $orderCounts = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countsOrderByBuilding($building);
        $building->setOrderCounts((int) $orderCounts);

        return $this->transferToJson($building);
    }

    private function getSalesAdminJson(
        $objectId
    ) {
         $admin = $this->getContainer()
             ->get('doctrine')
             ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
             ->find($objectId);

        if (is_null($admin)) {
            return null;
        }

        // set building id and city id
        $permissions = $admin->getPermissions();
        foreach ($permissions as $permission) {
            $buildingId = $permission->getBuildingId();
            if (is_null($buildingId)) {
                continue;
            }

            $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);

            if (is_null($building)) {
                continue;
            }

            $permission->setBuilding($building);
        }

        return $this->transferToJson($admin);
    }

    /**
     * @param $input
     *
     * @return mixed
     */
    private function transferToJson(
        $input
    ) {
        return $this->getContainer()->get('serializer')->serialize($input, 'json');
    }
}
