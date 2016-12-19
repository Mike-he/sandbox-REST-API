<?php

namespace Sandbox\ApiBundle\Traits;

use JMS\Serializer\SerializationContext;
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
            case Log::OBJECT_ROOM:
                $json = $this->getRoomJson($objectId);
                break;
            case Log::OBJECT_PRODUCT:
                $json = $this->getProductJson($objectId);
                break;
            case Log::OBJECT_ROOM_ORDER:
                $json = $this->getRoomOrderJson($objectId);
                break;
            case Log::OBJECT_BUILDING:
                $json = $this->getBuildingJson($objectId);
                break;
            case Log::OBJECT_ADMIN:
                $json = $this->getAdminJson($objectId);
                break;
            case Log::OBJECT_LEASE:
                $json = $this->getLeaseJson($objectId);
                break;
            case Log::OBJECT_LEASE_BILL:
                $json = $this->getLeaseBillJson($objectId);
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
     * @return mixed|void
     */
    private function getRoomOrderJson(
        $objectId
    ) {
        $object = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->find($objectId);

        if (is_null($object)) {
            return;
        }

        return $this->transferToJsonWithViewGroup($object, 'admin_detail');
    }

    /**
     * @param $objectId
     *
     * @return mixed|void
     */
    private function getProductJson(
        $objectId
    ) {
        $object = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->find($objectId);

        if (is_null($object)) {
            return;
        }

        return $this->transferToJsonWithViewGroup($object, 'admin_room');
    }

    /**
     * @param $objectId
     *
     * @return string
     */
    private function getRoomJson(
        $objectId
    ) {
        $object = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\Room')
            ->find($objectId);

        if (is_null($object)) {
            return;
        }

        return $this->transferToJsonWithViewGroup($object, 'admin_room');
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
            return;
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

    /**
     * @param $objectId
     *
     * @return mixed|null
     */
    private function getAdminJson(
        $objectId
    ) {
        $admin = $this->getContainer()
             ->get('doctrine')
             ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
             ->findPositionByAdmin($objectId);

        if (is_null($admin)) {
            return;
        }

        return $this->transferToJsonWithViewGroup($admin, 'admin');
    }

    /**
     * @param $objectId
     *
     * @return mixed|void
     */
    private function getLeaseJson(
        $objectId
    ) {
        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->find($objectId);

        if (is_null($lease)) {
            return;
        }

        return $this->transferToJsonWithViewGroup(
            $lease,
            'main'
        );
    }

    /**
     * @param $objectId
     *
     * @return mixed|void
     */
    private function getLeaseBillJson(
        $objectId
    ) {
        $bill = $this->getDoctrine()
            ->getRepository("SandboxApiBundle:Lease\LeaseBill")
            ->find($objectId);

        if (is_null($bill)) {
            return;
        }

        return $this->transferToJsonWithViewGroup(
            $bill,
            'lease_bill'
        );
    }

    /**
     * @param $input
     * @param $group
     *
     * @return mixed
     */
    private function transferToJsonWithViewGroup(
        $input,
        $group
    ) {
        return $this->getContainer()
            ->get('serializer')
            ->serialize(
                $input,
                'json',
                SerializationContext::create()->setGroups([$group])
            );
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
