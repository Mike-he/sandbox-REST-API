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
            case Log::OBJECT_USER:
                $json = $this->getUserJson($objectId);

                break;
            case Log::OBJECT_ROOM:
                $json = $this->getRoomJson($objectId);

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
     * @return string
     */
    private function getUserJson(
        $objectId
    ) {
        $userProfile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserProfile')
            ->findOneBy(array(
                'userId' => $objectId,
            ));

        if (!is_null($userProfile)) {
            return $this->transferToJson($userProfile);
        }

        return '';
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
