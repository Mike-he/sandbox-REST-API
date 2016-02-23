<?php

namespace Sandbox\SalesApiBundle\Controller\Room;

use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Room\RoomAttachmentBinding;
use Sandbox\ApiBundle\Entity\Room\RoomDoors;
use Sandbox\ApiBundle\Entity\Room\RoomFixed;
use Sandbox\ApiBundle\Entity\Room\RoomMeeting;
use Sandbox\ApiBundle\Entity\Room\RoomSupplies;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermission;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermissionMap;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminType;
use Sandbox\ApiBundle\Form\Room\RoomPatchType;
use Sandbox\ApiBundle\Form\Room\RoomType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminRoomController extends SalesRestController
{
    const ALREADY_EXISTS_MESSAGE = 'This resource already exists';
    const LOCATION_CANNOT_NULL = 'City, Building or Floor cannot be null';

    const ROOM_TYPE_PREFIX = 'room.type.';

    /**
     * Room.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
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
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="(use|unuse)",
     *    strict=true,
     *    description="Filter by room usage"
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
     *    name="floor",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by floor id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many rooms to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Annotations\QueryParam(
     *    name="sortBy",
     *    array=false,
     *    default="creationDate",
     *    nullable=true,
     *    requirements="(number|floor|area|allowedPeople)",
     *    strict=true,
     *    description="Sort by date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="direction",
     *    array=false,
     *    default="DESC",
     *    nullable=true,
     *    requirements="(ASC|DESC)",
     *    strict=true,
     *    description="sort direction"
     * )
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    default=null,
     *    nullable=true,
     *    description="search query"
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
        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
                SalesAdminPermission::KEY_PLATFORM_PRODUCT,
                SalesAdminPermission::KEY_PLATFORM_EVENT,
            )
        );

        //filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $type = $paramFetcher->get('type');
        $status = $paramFetcher->get('status');
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $floorId = $paramFetcher->get('floor');

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
                SalesAdminPermission::KEY_PLATFORM_PRODUCT,
                SalesAdminPermission::KEY_PLATFORM_EVENT,
            )
        );

        if (!is_null($buildingId) && !in_array((int) $buildingId, $myBuildingIds)) {
            return new View(array());
        }

        //sort by
        $sortBy = $paramFetcher->get('sortBy');
        $direction = $paramFetcher->get('direction');

        //search by name and number
        $query = $paramFetcher->get('query');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;
        $floor = !is_null($floorId) ? $this->getRepo('Room\RoomFloor')->find($floorId) : null;

        $query = $this->getRepo('Room\RoomView')->getSalesRooms(
            $type,
            $city,
            $building,
            $floor,
            $status,
            $sortBy,
            $direction,
            $query,
            $myBuildingIds
        );

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $query,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Room.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
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
     *    name="room_id",
     *    default=null,
     *    array=true,
     *    nullable=true,
     *    description="room status"
     * )
     *
     * @Route("/rooms/status")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getRoomsUsageStatusAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            )
        );

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            )
        );

        $roomIds = $paramFetcher->get('room_id');
        $statusArray = [];
        if (!is_null($roomIds) && !empty($roomIds)) {
            foreach ($roomIds as $roomId) {
                $room = $this->getRepo('Room\Room')->findOneById($roomId);

                // check room valid and belong to my buildings
                if (is_null($room) || !in_array($room->getBuildingId(), $myBuildingIds)) {
                    continue;
                }

                if (!$room->isDeleted()) {
                    $usage = $this->getRepo('Room\Room')->getRoomUsageStatus($roomId);

                    if (!is_null($usage) && !empty($usage)) {
                        $status = true;
                    } else {
                        $status = false;
                    }
                    $status = [
                        'room_id' => $roomId,
                        'usage' => $status,
                        'user' => $usage,
                    ];
                    array_push($statusArray, $status);
                }
            }
        }

        return new View($statusArray);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
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
     *    name="floor",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by floor id"
     * )
     *
     * @Route("/rooms/notproducted")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getNotProductedRoomsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            )
        );

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            )
        );

        $type = $paramFetcher->get('type');
        $floorId = $paramFetcher->get('floor');

        $floor = !is_null($floorId) ? $this->getRepo('Room\RoomFloor')->find($floorId) : null;

        $query = $this->getRepo('Room\Room')->getNotProductedRooms(
            $floor,
            $type,
            $myBuildingIds
        );

        return new View($query);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     *    name="type",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    requirements="(office|meeting|flexible|fixed)",
     *    strict=true,
     *    description="Filter by room type"
     * )
     *
     * @Route("/rooms/producted")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getProductedRoomsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                SalesAdminPermission::KEY_PLATFORM_PRICE,
            )
        );

        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $types = $paramFetcher->get('type');

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                SalesAdminPermission::KEY_PLATFORM_PRICE,
            )
        );

        if (!is_null($buildingId) && !in_array((int) $buildingId, $myBuildingIds)) {
            return new View(array());
        }

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        $query = $this->getRepo('Room\RoomView')->getSalesProductedRooms(
            $types,
            $city,
            $building,
            'creationDate',
            'DESC'
        );

        return new View($query);
    }

    /**
     * Get room by id.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/rooms/{id}/usage")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getRoomUsersUsageById(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            )
        );

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            )
        );

        // check user permission
        if (empty($myBuildingIds)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $usage = $this->getRepo('Room\Room')->getSalesRoomUsersUsage(
            $id,
            $myBuildingIds
        );

        return new View($usage);
    }

    /**
     * Get rooms types.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful created"
     *  }
     * )
     *
     * @Route("/rooms/types")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getRoomTypes(
        Request $request
    ) {
        $roomKeys = array(Room::TYPE_OFFICE, Room::TYPE_MEETING, Room::TYPE_FLEXIBLE, Room::TYPE_FIXED);

        // get rooms types
        $roomTypes = array();
        foreach ($roomKeys as $roomKey) {
            $roomType = array(
                'key' => $roomKey,
                'description' => $this->get('translator')->trans(self::ROOM_TYPE_PREFIX.$roomKey),
            );
            array_push($roomTypes, $roomType);
        }

        return new View($roomTypes);
    }

    /**
     * Get room by id.
     *
     * @param Request $request
     * @param int     $id
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
        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
                SalesAdminPermission::KEY_PLATFORM_PRODUCT,
                SalesAdminPermission::KEY_PLATFORM_EVENT,
            )
        );

        // get room
        $room = $this->getRepo('Room\Room')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
                SalesAdminPermission::KEY_PLATFORM_PRODUCT,
                SalesAdminPermission::KEY_PLATFORM_EVENT,
            )
        );

        // check user permission
        if (empty($myBuildingIds) || !in_array($room->getBuildingId(), $myBuildingIds)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
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

        if (!$form->isValid() || is_null($room->getBuildingId())) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            ),
            $room->getBuildingId()
        );

        $meeting = $form['room_meeting']->getData();
        $fixed = $form['room_fixed']->getData();
        $attachments_id = $form['attachment_id']->getData();
        $office_supplies = $form['office_supplies']->getData();
        $doors_control = $form['doors_control']->getData();

        return $this->handleRoomPost(
            $room,
            $meeting,
            $fixed,
            $attachments_id,
            $office_supplies,
            $doors_control
        );
    }

    /**
     * Update Room.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     201 = "Returned when successful created"
     *  }
     * )
     *
     * @Route("/rooms/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchRoomAction(
        Request $request,
        $id
    ) {
        // get room
        $room = $this->getRepo('Room\Room')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            ),
            $room->getBuildingId()
        );

        // bind data
        $roomJson = $this->container->get('serializer')->serialize($room, 'json');
        $patch = new Patch($roomJson, $request->getContent());
        $roomJson = $patch->apply();

        $form = $this->createForm(new RoomPatchType(), $room);
        $form->submit(json_decode($roomJson, true));

        $meeting = $form['room_meeting']->getData();
        $fixed = $form['room_fixed']->getData();
        $attachments = $form['attachments']->getData();
        $office_supplies = $form['office_supplies']->getData();

        return $this->handleRoomPatch(
            $room,
            $meeting,
            $fixed,
            $attachments,
            $office_supplies
        );
    }

    /**
     * Room attachment.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/rooms/{id}/attachments")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postRoomAttachmentsAction(
        Request $request,
        $id
    ) {
        //get array with ids
        $attachments_id = json_decode($request->getContent(), true);
        if (!is_array($attachments_id)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // get room
        $room = $this->getRepo('Room\Room')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            ),
            $room->getBuildingId()
        );

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
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/rooms/{id}/supplies")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postRoomSuppliesAction(
        Request $request,
        $id
    ) {
        $office_supplies = json_decode($request->getContent(), true);
        if (!is_array($office_supplies)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // get room
        $room = $this->getRepo('Room\Room')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            ),
            $room->getBuildingId()
        );

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
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "OK"
     *  }
     * )
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    strict=true,
     *    description=""
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
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // get room
        $room = $this->getRepo('Room\Room')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            ),
            $room->getBuildingId()
        );

        //supplies id
        $suppliesIds = $paramFetcher->get('id');

        $this->getRepo('Room\RoomSupplies')->deleteRoomSuppliesByIds(
            $room,
            $suppliesIds
        );

        return new View();
    }

    /**
     * Delete Room attachments.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "OK"
     *  }
     * )
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    strict=true,
     *    description=""
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
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // get room
        $room = $this->getRepo('Room\Room')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            ),
            $room->getBuildingId()
        );

        // attachments id
        $attachmentIds = $paramFetcher->get('id');

        $this->getRepo('Room\RoomAttachmentBinding')->deleteRoomAttachmentByIds(
            $room,
            $attachmentIds
        );

        return new View();
    }

    /**
     * Delete a Room.
     *
     * @param Request $request the request object
     * @param int     $id
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
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            ),
            $room->getBuildingId()
        );

        // set room deleted
        $room->setIsDeleted(true);

        // get product
        $products = $this->getRepo('Product\Product')->findByRoomId($id);
        foreach ($products as $product) {
            if (!is_null($product) || !empty($product)) {
                $product->setVisible(false);
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Room   $room
     * @param object $meeting
     * @param object $fixed
     * @param object $attachments
     * @param object $office_supplies
     *
     * @return View
     */
    private function handleRoomPatch(
        $room,
        $meeting,
        $fixed,
        $attachments,
        $office_supplies
    ) {
        $room->setModificationDate(new \DateTime('now'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($room);
        $em->flush();

        // handle meeting rooms
        if (!is_null($meeting) && $room->getType() == Room::TYPE_MEETING) {
            $roomMeeting = $this->getRepo('Room\RoomMeeting')->findOneByRoom($room);
            // remove the old data
            if (!is_null($roomMeeting)) {
                $em->remove($roomMeeting);
                $em->flush();
            }
            // add the new one
            $this->addRoomTypeData(
                $em,
                $room,
                $meeting,
                null
            );
        }

        // handle fixed rooms
        if (!is_null($fixed) && $room->getType() == Room::TYPE_FIXED) {
            $roomsFixed = $this->getRepo('Room\RoomFixed')->findByRoom($room);
            array_map($this->removeFixedSeatNumbers($em), $roomsFixed);
            $this->addRoomTypeData(
                $em,
                $room,
                null,
                $fixed
            );
        }

        // handle room attachments
        if (!is_null($attachments)) {
            $attachmentsBind = $this->getRepo('Room\RoomAttachmentBinding')->findByRoom($room);
            array_map($this->removeAttachments($em), $attachmentsBind);
            $this->addRoomAttachment($em, $room, $attachments);
        }

        // handle office supplies
        if (!is_null($office_supplies)) {
            $supplyOk = true;

            //check if provided supplies id exists
            foreach ($office_supplies as $office_Supply) {
                $supplyObject = $this->getRepo('Room\Supplies')->find($office_Supply['id']);
                if (is_null($supplyObject)) {
                    $supplyOk = false;
                }
            }
            if ($supplyOk) {
                $roomSupplies = $this->getRepo('Room\RoomSupplies')->findByRoom($room);
                array_map($this->removeOfficeSupplies($em), $roomSupplies);
                $this->addOfficeSupplies($em, $room, $office_supplies);
            }
        }

        $response = array(
            'id' => $room->getId(),
        );

        return new View($response);
    }

    /**
     * @param Room                  $room
     * @param RoomMeeting           $meeting
     * @param RoomFixed             $roomsFixed
     * @param RoomAttachmentBinding $attachments_id
     * @param RoomSupplies          $office_supplies
     * @param RoomDoors             $doors_control
     *
     * @return View
     */
    private function handleRoomPost(
        $room,
        $meeting,
        $roomsFixed,
        $attachments_id,
        $office_supplies,
        $doors_control
    ) {
        $roomCity = $this->getRepo('Room\RoomCity')->find($room->getCityId());
        $roomBuilding = $this->getRepo('Room\RoomBuilding')->find($room->getBuildingId());
        $roomFloor = $this->getRepo('Room\RoomFloor')->find($room->getFloorId());

        $myRoom = $this->getRepo('Room\Room')->findOneBy(array(
                'building' => $roomBuilding,
                'number' => $room->getNumber(),
                'isDeleted' => false,
            )
        );

        if (!is_null($myRoom)) {
            //304 Not Modified
            return $this->customErrorView(
                400,
                400001,
                self::ALREADY_EXISTS_MESSAGE
            );
        }

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

        //add doors control
        if (!is_null($doors_control)) {
            foreach ($doors_control as $doors) {
                $roomDoor = new RoomDoors();
                $roomDoor->setRoom($room);
                $roomDoor->setDoorControlId($doors['control_id']);
                $roomDoor->setName($doors['control_name']);
                $em->persist($roomDoor);
            }
            $em->flush();
        }

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
     * @param array         $attachments_id
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
            $roomAttachment->setAttachmentId($attachment);

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
            $supplyObject = $this->getRepo('Room\Supplies')->find($supply['id']);

            $roomSupply = new RoomSupplies();
            $roomSupply->setRoom($room);
            $roomSupply->setSupply($supplyObject);
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
            case Room::TYPE_MEETING:
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
            case Room::TYPE_FIXED:
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
     * Check user permission.
     *
     * @param int   $opLevel
     * @param array $permissions
     * @param int   $buildingId
     */
    private function checkAdminRoomPermission(
        $opLevel,
        $permissions,
        $buildingId = null
    ) {
        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $this->getAdminId(),
            SalesAdminType::KEY_PLATFORM,
            $permissions,
            $opLevel,
            $buildingId
        );
    }

    /**
     * Callback function to remove attachments from a room.
     *
     * @param EntityManager $em
     *
     * @return \Closure
     */
    private function removeAttachments(
        $em
    ) {
        return function ($attachmentBind) use ($em) {
            $attachBind = $this->getRepo('Room\RoomAttachmentBinding')->find($attachmentBind->getId());
            $em->remove($attachBind);
            $em->flush();
        };
    }

    /**
     * Callback function to remove office supplies from a room.
     *
     * @param EntityManager $em
     *
     * @return \Closure
     */
    private function removeOfficeSupplies(
        $em
    ) {
        return function ($roomSupply) use ($em) {
            $roomSupply = $this->getRepo('Room\RoomSupplies')->find($roomSupply->getId());
            $em->remove($roomSupply);
            $em->flush();
        };
    }

    /**
     * Callback function to remove seat numbers from a room.
     *
     * @param EntityManager $em
     *
     * @return \Closure
     */
    private function removeFixedSeatNumbers(
        $em
    ) {
        return function ($roomFixed) use ($em) {
            $fixed = $this->getRepo('Room\RoomFixed')->find($roomFixed->getId());
            $em->remove($fixed);
            $em->flush();
        };
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/rooms/office/{id}/usage")
     * @Method({"GET"})
     *
     * @Annotations\QueryParam(
     *    name="year",
     *    nullable=false,
     *    description=""
     * )
     *
     * @return View
     */
    public function getOfficeRoomUsageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $product = $this->getRepo('Product\Product')->findOneBy(['roomId' => $id]);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            ),
            $product->getRoom()->getBuildingId()
        );

        $yearString = $paramFetcher->get('year');
        $results = [];
        if (!is_null($product) && !is_null($yearString) && !empty($yearString)) {
            $productId = $product->getId();
            $yearStart = new \DateTime($yearString);
            $yearStart = $yearStart->modify('first day of January'.$yearString);
            $yearStart->setTime(0, 0, 0);
            $yearEnd = new \DateTime($yearString);
            $yearEnd = $yearEnd->modify('last day of December'.$yearString);
            $yearEnd->setTime(23, 59, 59);
            $results = $this->getRepo('Room\RoomUsageView')->getSalesRoomUsersUsage(
                $productId,
                $yearStart,
                $yearEnd
            );
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['room_usage']));
        $view->setData($results);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/rooms/fixed/{id}/usage")
     * @Method({"GET"})
     *
     * @Annotations\QueryParam(
     *    name="seat",
     *    nullable=false,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="start",
     *    nullable=false,
     *    description=""
     * )
     *
     *  @Annotations\QueryParam(
     *    name="end",
     *    nullable=false,
     *    description=""
     * )
     *
     * @return View
     */
    public function getFixedRoomUsageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $seat = $paramFetcher->get('seat');
        $results = [];
        if (!is_null($seat) && !empty($seat)) {
            $product = $this->getRepo('Product\Product')->findOneBy(
                [
                    'roomId' => $id,
                    'seatNumber' => $seat,
                ]
            );
            $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

            // check user permission
            $this->checkAdminRoomPermission(
                SalesAdminPermissionMap::OP_LEVEL_VIEW,
                array(
                    SalesAdminPermission::KEY_PLATFORM_ROOM,
                ),
                $product->getRoom()->getBuildingId()
            );

            $startString = $paramFetcher->get('start');
            $endString = $paramFetcher->get('end');
            if (
                !is_null($product) &&
                !empty($product) &&
                !is_null($startString) &&
                !empty($startString) &&
                !is_null($endString) &&
                !empty($endString)
            ) {
                $productId = $product->getId();
                $start = new \DateTime($startString);
                $start->setTime(0, 0, 0);
                $end = new \DateTime($endString);
                $end->setTime(23, 59, 59);
                $results = $this->getRepo('Room\RoomUsageView')->getSalesRoomUsersUsage(
                    $productId,
                    $start,
                    $end
                );
            }
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['room_usage']));
        $view->setData($results);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/rooms/flexible/{id}/usage")
     * @Method({"GET"})
     *
     * @Annotations\QueryParam(
     *    name="start",
     *    nullable=false,
     *    description=""
     * )
     *
     *  @Annotations\QueryParam(
     *    name="end",
     *    nullable=false,
     *    description=""
     * )
     *
     * @return View
     */
    public function getFlexibleRoomUsageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $product = $this->getRepo('Product\Product')->findOneBy(
            ['roomId' => $id]
        );
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            ),
            $product->getRoom()->getBuildingId()
        );

        $startString = $paramFetcher->get('start');
        $endString = $paramFetcher->get('end');

        $resultArray = [];
        if (
            !is_null($product) &&
            !empty($product) &&
            !is_null($startString) &&
            !empty($startString) &&
            !is_null($endString) &&
            !empty($endString)
        ) {
            $productId = $product->getId();
            $start = new \DateTime($startString);
            $start->setTime(0, 0, 0);
            $end = new \DateTime($endString);
            $end->setTime(23, 59, 59);
            $results = $this->getRepo('Room\RoomUsageView')->getSalesRoomUsersUsage(
                $productId,
                $start,
                $end
            );
            if (!empty($results)) {
                foreach ($results as $result) {
                    $startDate = $result->getStartDate();
                    $endDate = $result->getEndDate();
                    $user = $result->getUser();
                    $appointed = $result->getAppointedUser();
                    $days = new \DatePeriod(
                        $startDate,
                        new \DateInterval('P1D'),
                        $endDate
                    );

                    foreach ($days as $day) {
                        $dayArray = [
                            'date' => $day->format('Y-m-d'),
                            'user' => $user,
                            'appointed_user' => $appointed,

                        ];

                        array_push($resultArray, $dayArray);
                    }
                }
            }
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['room_usage']));
        $view->setData($resultArray);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/rooms/meeting/{id}/usage")
     * @Method({"GET"})
     *
     * @Annotations\QueryParam(
     *    name="day",
     *    nullable=false,
     *    description=""
     * )
     *
     * @return View
     */
    public function getMeetingRoomUsageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $product = $this->getRepo('Product\Product')->findOneBy(['roomId' => $id]);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkAdminRoomPermission(
            SalesAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                SalesAdminPermission::KEY_PLATFORM_ROOM,
            ),
            $product->getRoom()->getBuildingId()
        );

        $dayString = $paramFetcher->get('day');
        $results = [];
        if (!is_null($product) && !is_null($dayString) && !empty($dayString)) {
            $productId = $product->getId();
            $start = new \DateTime($dayString);
            $start->setTime(0, 0, 0);
            $end = new \DateTime($dayString);
            $end->setTime(23, 59, 59);

            $results = $this->getRepo('Room\RoomUsageView')->getSalesRoomUsersUsage(
                $productId,
                $start,
                $end
            );
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['room_usage']));
        $view->setData($results);

        return $view;
    }
}
