<?php

namespace Sandbox\AdminApiBundle\Controller\Room;

use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Room\RoomAttachmentBinding;
use Sandbox\ApiBundle\Entity\Room\RoomFixed;
use Sandbox\ApiBundle\Entity\Room\RoomMeeting;
use Sandbox\ApiBundle\Entity\Room\RoomSupplies;
use Sandbox\ApiBundle\Form\Room\RoomType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Controller\Room\RoomController;
use Sandbox\ApiBundle\Entity\Room\Room;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;

/**
 * Admin room controller.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminRoomController extends RoomController
{
    const ALREADY_EXISTS_MESSAGE = 'This resource already exists';
    const LOCATION_CANNOT_NULL = 'City, Building or Floor cannot be null';

    /**
     * Room.
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
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="(office|meeting|flexible|fixed)",
     *    strict=true,
     *    description="Filter by room type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by city id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many rooms to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Offset from which to start listing rooms"
     * )
     *
     * @Route("/rooms")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getRoomsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // get room
        $room = $this->getRepo('Room\Room');

        //filters
        $filters = $this->getFilters($paramFetcher);
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        //find all with or without filters
        $rooms = $room->findBy(
            $filters,
            null,
            $limit,
            $offset
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_room']));
        $view->setData($rooms);

        return $view;
    }

    /**
     * Get room by id.
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

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_room']));
        $view->setData($room);

        return $view;
    }

    /**
     * Room.
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
     *
     * @throws \Exception
     */
    public function postRoomAction(
        Request $request
    ) {
        $room = new Room();

        $form = $this->createForm(new RoomType(), $room);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $meeting = $form['room_meeting']->getData();
        $fixed = $form['room_fixed']->getData();
        $attachments_id = $form['attachment_id']->getData();
        $office_supplies = $form['office_supplies']->getData();

        return $this->handleRoomPost(
            $room,
            $meeting,
            $fixed,
            $attachments_id,
            $office_supplies
        );
    }

    /**
     * Room attachment.
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
     * @Route("/rooms/{id}/attachments")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchRoomAttachmentsAction(
        Request $request,
        $id
    ) {
        //get array with ids
        $attachments_id = json_decode($request->getContent(), true);
        if (!is_array($attachments_id)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // get room
        $room = $this->getRepo('Room\Room')->find($id);
        if (is_null($room)) {
            $this->createNotFoundException(self::NOT_FOUND_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();

        //add attachments
        foreach ($attachments_id as $attachment_id) {
            //check if the attachment exists
            $attachment = $this->getRepo('Room\RoomAttachment')->find($attachment_id);
            if (is_null($attachment)) {
                continue;
            }

            $roomAttachment = new RoomAttachmentBinding();
            $roomAttachment->setRoom($room);
            $roomAttachment->setAttachmentId($attachment_id);

            $em->persist($roomAttachment);
            $em->flush();
        }
    }

    /**
     * Room office supplies.
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
     * @Route("/rooms/{id}/supplies")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchRoomSuppliesAction(
        Request $request,
        $id
    ) {
        $office_supplies = json_decode($request->getContent(), true);
        if (!is_array($office_supplies)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // get room
        $room = $this->getRepo('Room\Room')->find($id);
        if (is_null($room)) {
            $this->createNotFoundException(self::NOT_FOUND_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();

        // Add office supplies
        if (!is_null($office_supplies)) {
            $this->addOfficeSupplies(
                $em,
                $room,
                $office_supplies
            );
        }
    }

    /**
     * Delete Room supplies.
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
     * @Route("/rooms/{id}/supplies")
     * @Method({"DELETE"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteRoomSuppliesAction(
        Request $request,
        $id
    ) {
        //501 Not Implemented
        return $this->customErrorView(
            501,
            501,
            'Not Implemented'
        );
    }

    /**
     * Delete Room attachments.
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
     * @Route("/rooms/{id}/attachments")
     * @Method({"DELETE"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteRoomAttachmentsAction(
        Request $request,
        $id
    ) {
        // get room
        $room = $this->getRepo('Room\Room')->find($id);

        //get array with ids
        $attachments_id = json_decode($request->getContent(), true);
        if (!is_array($attachments_id)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();

        //remove attachments
        foreach ($attachments_id as $attachment_id) {
            //check if the attachment exists
            $attachmentBind = $this->getRepo('Room\RoomAttachmentBinding')->findOneBy(array(
                'room' => $room,
                'attachmentId' => $attachment_id,
            ));

            $em->remove($attachmentBind);
            $em->flush();
        }
    }

    /**
     * Delete a Room.
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
     *
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
     * @param Room                  $room
     * @param RoomMeeting           $meeting
     * @param RoomFixed             $roomsFixed
     * @param RoomAttachmentBinding $attachments_id
     * @param RoomSupplies          $office_supplies
     *
     * @return View
     */
    private function handleRoomPost(
        $room,
        $meeting,
        $roomsFixed,
        $attachments_id,
        $office_supplies
    ) {
        $myRoom = $this->getRepo('Room\Room')->findOneBy(array(
                'buildingId' => $room->getBuildingId(),
                'number' => $room->getNumber(),
            )
        );

        if (!is_null($myRoom)) {
            //304 Not Modified
            return $this->customErrorView(
                304,
                304,
                self::ALREADY_EXISTS_MESSAGE
            );
        }

        $roomCity = $this->getRepo('Room\RoomCity')->find($room->getCityId());
        $roomBuilding = $this->getRepo('Room\RoomBuilding')->find($room->getBuildingId());
        $roomFloor = $this->getRepo('Room\RoomFloor')->find($room->getFloorId());

        if (is_null($roomCity) ||
            is_null($roomBuilding) ||
            is_null($roomFloor)
        ) {
            throw new BadRequestHttpException(self::LOCATION_CANNOT_NULL);
        }

        $now = new \DateTime('now');
        $room->setCreationDate($now);
        $room->setModificationDate($now);
        $room->setCity($roomCity);
        $room->setBuilding($roomBuilding);
        $room->setFloor($roomFloor);

        $em = $this->getDoctrine()->getManager();
        $em->persist($room);
        $em->flush();

        //add attachments
        if (!is_null($attachments_id)) {
            $this->addRoomAttachment(
                $em,
                $room,
                $attachments_id
            );
        }

        //manage room types
        if (!is_null($meeting) || !is_null($roomsFixed)) {
            $this->addRoomTypeData(
                $em,
                $room,
                $meeting,
                $roomsFixed
            );
        }

        // Add office supplies
        if (!is_null($office_supplies)) {
            $this->addOfficeSupplies(
                $em,
                $room,
                $office_supplies
            );
        }

        $response = array(
            'id' => $room->getId(),
        );

        return new View($response);
    }

    /**
     * Save attachment to db.
     *
     * @param EntityManager $em
     * @param Room          $room
     * @param Array         $attachments_id
     */
    private function addRoomAttachment(
        $em,
        $room,
        $attachments_id
    ) {
        foreach ($attachments_id as $attachment_id) {

            //check if the attachment exists
            $attachment = $this->getRepo('Room\RoomAttachment')->find($attachment_id);
            if (is_null($attachment)) {
                continue;
            }

            $roomAttachment = new RoomAttachmentBinding();
            $roomAttachment->setRoom($room);
            $roomAttachment->setAttachmentId($attachment_id['id']);

            $em->persist($roomAttachment);
            $em->flush();
        }
    }

    /**
     * Save office supply id and quantity.
     *
     * @param EntityManager $em
     * @param Room          $room
     * @param RoomSupplies  $office_supplies
     *
     * @internal param $attachments_id
     */
    private function addOfficeSupplies(
        $em,
        $room,
        $office_supplies
    ) {
        foreach ($office_supplies as $supply) {
            //check if the office supply exists
            $office_supply = $this->getRepo('Room\RoomSupplies')->find($supply['id']);
            if (is_null($office_supply)) {
                continue;
            }

            $roomSupply = new RoomSupplies();
            $roomSupply->setRoom($room);
            $roomSupply->setSuppliesId($supply['id']);
            $roomSupply->setQuantity($supply['quantity']);
            $em->persist($roomSupply);
            $em->flush();
        }
    }

    /**
     * Add room type data.
     *
     * @param EntityManager $em
     * @param Room          $room
     * @param RoomMeeting   $meeting
     * @param RoomFixed     $roomsFixed
     *
     * @internal param $id
     * @internal param $type
     * @internal param $meeting
     * @internal param $room
     */
    private function addRoomTypeData(
        $em,
        $room,
        $meeting,
        $roomsFixed
    ) {
        switch ($room->getType()) {
            case 'meeting':
                $format = 'H:i:s';

                $start = \DateTime::createFromFormat(
                    $format,
                    $meeting['start_hour']
                );

                $end = \DateTime::createFromFormat(
                    $format,
                    $meeting['end_hour']
                );

                $roomMeeting = new RoomMeeting();
                $roomMeeting->setRoom($room);
                $roomMeeting->setStartHour($start);
                $roomMeeting->setEndHour($end);

                $em->persist($roomMeeting);
                $em->flush();
                break;
            case 'fixed':
                foreach ($roomsFixed as $fixed) {
                    $roomFixed = new RoomFixed();
                    $roomFixed->setRoom($room);
                    $roomFixed->setSeatNumber($fixed['seat_number']);
                    $roomFixed->setAvailable($fixed['available']);
                    $em->persist($roomFixed);
                    $em->flush();
                }
            break;
            default:
                /* Do nothing */
                break;
        }
    }

    /**
     * Get filters from rooms get request.
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return array
     */
    private function getFilters(
        $paramFetcher
    ) {
        $type = $paramFetcher->get('type');
        $city = $paramFetcher->get('city');
        $building = $paramFetcher->get('building');

        $filters = [];

        if (!is_null($type)) {
            $filters['type'] = $type;
        }

        if (!is_null($city)) {
            $filters['cityId'] = $city;
        }

        if (!is_null($building)) {
            $filters['buildingId'] = $building;
        }

        return $filters;
    }
}
