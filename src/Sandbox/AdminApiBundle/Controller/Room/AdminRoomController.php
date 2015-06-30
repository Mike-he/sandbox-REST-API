<?php

namespace Sandbox\AdminApiBundle\Controller\Room;

use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Room\RoomAttachment;
use Sandbox\ApiBundle\Entity\Room\RoomFixed;
use Sandbox\ApiBundle\Entity\Room\RoomMeeting;
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
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Validator\Tests\Fixtures\Entity;

/**
 * Admin room controller
 *
 * @category Sandbox
 * @package  Sandbox\ClientApiBundle\Controller
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class AdminRoomController extends RoomController
{
    const ALREADY_EXISTS_MESSAGE = "This resource already exists";

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
     * @Route("/rooms")
     * @Method({"GET"})
     *
     * @return View
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

        //find all with or without filters
        $rooms = $room->findBy(
            $filters
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_room']));
        $view->setData($rooms);

        return $view;
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

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_room']));
        $view->setData($room);

        return $view;
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
        $room = new Room();

        $form = $this->createForm(new RoomType(), $room);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $meeting = $form['room_meeting']->getData();
        $fixed = $form['room_fixed']->getData();

        return $this->handleRoomPost(
            $room,
            $meeting,
            $fixed
        );
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
     * @param  Room        $room
     * @param  RoomMeeting $meeting
     * @param  RoomFixed   $roomsFixed
     * @return View
     */
    private function handleRoomPost(
        $room,
        $meeting,
        $roomsFixed
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
            throw new BadRequestHttpException('City, Building or Floor cannot be null');
        }

        $now = new \DateTime("now");
        $room->setCreationDate($now);
        $room->setModificationDate($now);
        $room->setCity($roomCity);
        $room->setBuilding($roomBuilding);
        $room->setFloor($roomFloor);

        $em = $this->getDoctrine()->getManager();
        $em->persist($room);
        $em->flush();

        //manage room types
        $this->addRoomTypeData(
            $em,
            $room,
            $meeting,
            $roomsFixed
        );

        //TODO Add office supplies - TBD

        $response = array(
            "id" => $room->getId(),
        );

        return new View($response);
    }

    /**
     * Save attachment to db
     *
     * @param $em
     * @param $id
     * @param $attachment
     * @throws \Exception
     * @internal param $room
     * @internal param $attachment
     */
    private function addRoomAttachment(
        $em,
        $id,
        $attachment
    ) {
        try {
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
            $roomAttachment->setRoomId($id);
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

    /**
     * Add room type data
     *
     * @param EntityManager $em
     * @param Room $room
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
                //TODO
//                foreach ($roomsFixed as $fixed) {
//                    $roomFixed = new RoomFixed();
//                    $roomFixed->setRoomId($room->getId());
//                    $roomFixed->setSeatNumber($fixed['seat_number']);
//                    $roomFixed->setAvailable($fixed['available']);
//                    $em->persist($roomFixed);
//                    $em->flush();
//                }
            break;
            default:
                /* Do nothing */
                break;
        }
    }

    /**
     * Get filters from rooms get request
     *
     * @param $paramFetcher
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
