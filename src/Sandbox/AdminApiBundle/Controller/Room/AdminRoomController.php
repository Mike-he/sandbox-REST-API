<?php

namespace Sandbox\AdminApiBundle\Controller\Room;

use Sandbox\ApiBundle\Entity\Room\RoomAttachment;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Sandbox\ApiBundle\Form\Room\RoomType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Acl\Exception\Exception;
use Sandbox\ApiBundle\Controller\Room\RoomController;
use Sandbox\ApiBundle\Entity\Room\Room;
use FOS\RestBundle\View\View;

/**
 * Login controller
 *
 * @category Sandbox
 * @package  Sandbox\ClientApiBundle\Controller
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class AdminRoomController extends RoomController
{

    /**
     * Room
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/rooms")
     * @Method({"GET"})
     *
     * @return View
     * @throws \Exception
     */
    public function getRoomsAction(
        Request $request
    ) {
        // get room
        $repo = $this->getRepo('Room\Room');
        $allRooms = $repo->findAll();

        return $this->handleGetRooms($allRooms);
    }

    /**
     * Get room by id
     *
     * @param Request $request
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/rooms/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getRoomByIdAction(
        Request $request,
        $id
    ) {
        // get room
        $room = $this->getRepo('Room\Room')->find($id);

        if (is_null($room)) {
            $this->createNotFoundException(self::NOT_FOUND_MESSAGE);
        }

        $result = $this->getRoomObject($room);

        return new View($result);
    }

    /**
     * Room
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     201 = "Returned when successful created"
     *  }
     * )
     *
     * @Route("/rooms")
     * @Method({"POST"})
     *
     * @return View
     * @throws \Exception
     */
    public function postRoomAction(
        Request $request
    ) {
        return $this->handleRoomPost($request);
    }

    /**
     * Delete a Room
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "OK"
     *  }
     * )
     *
     * @Route("/rooms/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     * @throws \Exception
     */
    public function deleteRoomAction(
        Request $request,
        $id
    ) {
        // get room
        $room = $this->getRepo('Room\Room')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($room);
        $em->flush();
    }

    /**
     * @param  Request    $request
     * @return array|View
     */
    private function handleRoomPost(
        Request $request
    ) {
        $room = new Room();

        $form = $this->createForm(new RoomType(), $room);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $myRoom = $this->getRepo('Room\Room')->findOneBy(array(
                'buildingId2' => $room->getBuildingId(),
                'number' => $room->getNumber(),
            )
        );

        if (!is_null($myRoom)) {
            //304 Not Modified
            return $this->customErrorView(
                304,
                304,
                'Room already exists'
            );
        }

        $now = new \DateTime("now");
        $room->setCreationDate($now);
        $room->setModificationDate($now);

        $em = $this->getDoctrine()->getManager();
        $em->persist($room);
        $em->flush();

        //Add attachment (limited to one)
        $this->addRoomAttachment($em, $room);

        //TODO Add office supplies - TBD

        $response = array(
            "id" => $room->getId(),
        );

        return new View($response);
    }

    /**
     * Handle rooms
     *
     * @param $rooms
     * @return View
     */
    private function handleGetRooms(
        $rooms
    ) {
        $result = [];

        foreach ($rooms as $room) {
            $result[] = $this->getRoomObject($room);
        }

        return new View($result);
    }

    /**
     * Create the room array
     *
     * @param $room
     * @return array
     */
    private function getRoomObject(
        $room
    ) {
        $city = $this->getRepo('Room\RoomCity')->find($room->getCityId());
        $building = $this->getRepo('Room\RoomBuilding')->find($room->getBuildingId());
        $floor = $this->getRepo('Room\RoomFloor')->find($room->getFloorId());

        $attachments =  $this->getRepo('Room\RoomAttachment')->findOneBy(array(
                'roomId' => $room->getId(),
            )
        );

        $result = array(
            'id' => $room->getId(),
            'name' => $room->getName(),
            'description' => $room->getDescription(),
            'city' => $city->getName(),
            'building' => $building->getName(),
            'floor' => $floor->getFloorNumber(),
            'number' => $room->getNumber(),
            'allowed_people' => $room->getAllowedPeople(),
            'area' => $room->getArea(),
            //'office_supplies' => 'TODO', //TODO Add office supplies - TBD
            'type' => $room->getType(),
            //'available' => 'TODO',      //TODO Check availability
            //'current_user_id' => 'TODO', //TODO Check User ID
            'attachments' => $attachments,
            'creation_date' => $room->getCreationDate(),
            'modification_date' => $room->getModificationDate(),
        );

        return $result;
    }

    /**
     * Save attachment to db
     *
     * @param $em
     * @param $room
     * @throws \Exception
     * @internal param $attachment
     */
    private function addRoomAttachment(
        $em,
        $room
    ) {
        try {
            $attachment = $room->getAttachments();

            $content = $attachment['content'];
            $attachmentType = $attachment['attachment_type'];
            $filename = $attachment['filename'];
            $preview = $attachment['preview'];
            $size = $attachment['size'];

            if (is_null($content) ||
                $content === '' ||
                is_null($attachmentType) ||
                $attachmentType === '' ||
                is_null($size) ||
                $size === '') {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $roomAttachment = new RoomAttachment();
            $roomAttachment->setRoomId($room->getId());
            $roomAttachment->setContent($content);
            $roomAttachment->setAttachmenttype($attachmentType);
            $roomAttachment->setFilename($filename);
            $roomAttachment->setPreview($preview);
            $roomAttachment->setSize($size);

            $em->persist($roomAttachment);
            $em->flush();
        } catch (Exception $e) {
            throw new \Exception('Something went wrong!');
        }
    }
}
