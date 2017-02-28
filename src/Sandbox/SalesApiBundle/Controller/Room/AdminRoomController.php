<?php

namespace Sandbox\SalesApiBundle\Controller\Room;

use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\Room\RoomAttachmentBinding;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomDoors;
use Sandbox\ApiBundle\Entity\Room\RoomFixed;
use Sandbox\ApiBundle\Entity\Room\RoomMeeting;
use Sandbox\ApiBundle\Entity\Room\RoomSupplies;
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
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
                AdminPermission::KEY_SALES_BUILDING_ROOM,
                AdminPermission::KEY_SALES_BUILDING_PRODUCT,
            )
        );

        if (empty($myBuildingIds) ||
            (
                !is_null($buildingId) &&
                !in_array((int) $buildingId, $myBuildingIds)
            )
        ) {
            return new View();
        }

        //sort by
        $sortBy = $paramFetcher->get('sortBy');
        $direction = $paramFetcher->get('direction');

        //search by name and number
        $query = $paramFetcher->get('query');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;
        $floor = !is_null($floorId) ? $this->getRepo('Room\RoomFloor')->find($floorId) : null;

        $query = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomView')
            ->getSalesRooms(
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_ROOM,
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
                    $usages = $this->getRepo('Room\Room')->getSalesRoomUsageStatus($roomId);
                    $user = [];
                    $appointed = '';
                    $invited = [];

                    if (!is_null($usages) && !empty($usages)) {
                        $status = true;

                        $userArray = $usages[0];
                        $user = [
                            'userId' => $userArray['userId'],
                            'name' => $userArray['name'],
                            'phone' => $userArray['phone'],
                            'startDate' => $userArray['startDate'],
                            'endDate' => $userArray['endDate'],
                        ];

                        $appointed = $userArray['appointed'];
                    } else {
                        $status = false;
                    }

                    foreach ($usages as $usage) {
                        $people = ['userId' => $usage['invited_people']];

                        array_push($invited, $people);
                    }

                    $status = [
                        'room_id' => $roomId,
                        'usage' => $status,
                        'user' => $user,
                        'appointed_user' => $appointed,
                        'invited' => $invited,
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_ROOM,
                AdminPermission::KEY_SALES_BUILDING_PRODUCT,
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        $type = $paramFetcher->get('type');
        $floorId = $paramFetcher->get('floor');

        $floor = !is_null($floorId) ? $this->getRepo('Room\RoomFloor')->find($floorId) : null;

        $query = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\Room')
            ->getSalesNotProductedRooms(
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
     *    array=true,
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_PRICE,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        $cityId = $paramFetcher->get('city');
        $buildingIds = $paramFetcher->get('building');
        $types = $paramFetcher->get('type');

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_PRICE,
            )
        );

        if (empty($buildingIds)) {
            return new View(array());
        }

        foreach ($buildingIds as $buildingId) {
            if (!in_array((int) $buildingId, $myBuildingIds)) {
                return new View(array());
            }
        }

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;

        $query = $this->getRepo('Room\RoomView')->getSalesProductedRooms(
            $types,
            $city,
            $buildingIds,
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_ROOM,
            )
        );

        // check user permission
        if (empty($myBuildingIds)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $usage = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\Room')
            ->getSalesRoomUsersUsage(
                $id,
                $myBuildingIds
            );

        return new View($usage);
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_BUILDING_ROOM],
                ['key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT],
                ['key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER],
                ['key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE],
                ['key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE],
                ['key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT],
                ['key' => AdminPermission::KEY_SALES_BUILDING_ORDER],
            ],
            AdminPermission::OP_LEVEL_VIEW
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
                AdminPermission::KEY_SALES_BUILDING_ROOM,
                AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
                AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
                AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
                AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT,
                AdminPermission::KEY_SALES_BUILDING_ORDER,
            )
        );

        // check user permission
        if (empty($myBuildingIds) || !in_array($room->getBuildingId(), $myBuildingIds)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // set rent type
        $roomType = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomTypes')
            ->findOneBy(array(
                'name' => $room->getType(),
            ));

        $room->setRentType($roomType->getType());

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

        $buildingId = $room->getBuildingId();

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                    'building_id' => $buildingId,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        $building = $this->getRepo('Room\RoomBuilding')->findOneBy(array(
            'id' => $buildingId,
            'status' => RoomBuilding::STATUS_ACCEPT,
            'visible' => true,
        ));
        if (is_null($building)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                    'building_id' => $room->getBuildingId(),
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                    'building_id' => $room->getBuildingId(),
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                    'building_id' => $room->getBuildingId(),
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                    'building_id' => $room->getBuildingId(),
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                    'building_id' => $room->getBuildingId(),
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                    'building_id' => $room->getBuildingId(),
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        // set room deleted
        $room->setIsDeleted(true);

        // get product
        $products = $this->getRepo('Product\Product')->findByRoomId($id);
        foreach ($products as $product) {
            if (!is_null($product) || !empty($product)) {
                $product->setVisible(false);
                $product->setIsDeleted(true);
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_ROOM,
            'logAction' => Log::ACTION_DELETE,
            'logObjectKey' => Log::OBJECT_ROOM,
            'logObjectId' => $room->getId(),
        ));

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

        $type = $room->getType();

        // handle meeting rooms
        if (!is_null($meeting) &&
            ($type == Room::TYPE_MEETING || $type == Room::TYPE_STUDIO || $type == Room::TYPE_SPACE)
        ) {
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
        if (!is_null($fixed) && !empty($fixed) && $type == Room::TYPE_FIXED) {
            $this->handleRoomFixed(
                $em,
                $room,
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

        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_ROOM,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_ROOM,
            'logObjectId' => $room->getId(),
        ));

        $response = array(
            'id' => $room->getId(),
        );

        return new View($response);
    }

    /**
     * @param $em
     * @param $room
     * @param $fixed
     */
    private function handleRoomFixed(
        $em,
        $room,
        $fixed
    ) {
        if (array_key_exists('remove', $fixed) && !empty($fixed['remove'])) {
            foreach ($fixed['remove'] as $removeSeat) {
                $seat = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomFixed')
                    ->findOneBy([
                        'id' => $removeSeat['seat_id'],
                        'room' => $room,
                    ]);

                if (is_null($seat)) {
                    continue;
                }

                $em->remove($seat);
            }

            $em->flush();
        }

        if (array_key_exists('add', $fixed) && !empty($fixed['add'])) {
            $this->addRoomTypeData(
                $em,
                $room,
                null,
                $fixed['add']
            );
        }

        if (array_key_exists('modify', $fixed) && !empty($fixed['modify'])) {
            foreach ($fixed['modify'] as $modifySeat) {
                $seat = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomFixed')
                    ->findOneBy([
                        'id' => $modifySeat['seat_id'],
                        'room' => $room,
                    ]);

                $oldSeat = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomFixed')
                    ->findOneBy([
                        'seatNumber' => $modifySeat['seat_number'],
                        'room' => $room,
                    ]);

                if (is_null($seat) || !is_null($oldSeat)) {
                    continue;
                }

                $seat->setSeatNumber($modifySeat['seat_number']);
            }

            $em->flush();
        }
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
        $roomBuilding = $this->getRepo('Room\RoomBuilding')->find($room->getBuildingId());
        $roomCity = $this->getRepo('Room\RoomCity')->find($roomBuilding->getCityId());
        $roomFloor = $this->getRepo('Room\RoomFloor')->find($room->getFloorId());

//        $myRoom = $this->getRepo('Room\Room')->findOneBy(array(
//                'building' => $roomBuilding,
//                'number' => $room->getNumber(),
//                'isDeleted' => false,
//            )
//        );

//        if (!is_null($myRoom)) {
//            //304 Not Modified
//            return $this->customErrorView(
//                400,
//                400001,
//                self::ALREADY_EXISTS_MESSAGE
//            );
//        }

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

        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_ROOM,
            'logAction' => Log::ACTION_CREATE,
            'logObjectKey' => Log::OBJECT_ROOM,
            'logObjectId' => $room->getId(),
        ));

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
            case Room::TYPE_STUDIO:
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
            case Room::TYPE_SPACE:
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
                    $oldSeat = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Room\RoomFixed')
                        ->findOneBy([
                            'seatNumber' => $fixed['seat_number'],
                            'room' => $room,
                        ]);

                    if (!is_null($oldSeat)) {
                        continue;
                    }

                    $roomFixed = new RoomFixed();
                    $roomFixed->setRoom($room);
                    $roomFixed->setSeatNumber($fixed['seat_number']);

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
        $room = $this->getRepo('Room\Room')->find($id);
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);

        $products = $this->getRepo('Product\Product')->findBy(['roomId' => $id]);

        // check user permission
        $this->checkPermissionForRoomUsage($room->getBuildingId());

        $yearString = $paramFetcher->get('year');
        $results = [];
        if (!is_null($yearString) && !empty($yearString)) {
            $yearStart = new \DateTime($yearString);
            $yearStart = $yearStart->modify('first day of January'.$yearString);
            $yearStart->setTime(0, 0, 0);
            $yearEnd = new \DateTime($yearString);
            $yearEnd = $yearEnd->modify('last day of December'.$yearString);
            $yearEnd->setTime(23, 59, 59);

            foreach ($products as $product) {
                $productId = $product->getId();

                $results = $this->getRepo('Room\RoomUsageView')->getSalesRoomUsersUsage(
                    $productId,
                    $yearStart,
                    $yearEnd
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
                    'isDeleted' => false,
                ]
            );
            $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

            // check user permission
            $this->checkPermissionForRoomUsage($product->getRoom()->getBuildingId());

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
                    $end,
                    $seat
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
            [
                'roomId' => $id,
                'isDeleted' => false,
            ]
        );
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkPermissionForRoomUsage($product->getRoom()->getBuildingId());

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
                    $invited = $result->getInvitedPeople();
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
                            'invited_people' => $invited,
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
        $product = $this->getRepo('Product\Product')->findOneBy([
            'roomId' => $id,
            'isDeleted' => false,
        ]);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkPermissionForRoomUsage($product->getRoom()->getBuildingId());

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

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/rooms/longterm/{id}/usage")
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
    public function getLongtermRoomUsageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $product = $this->getRepo('Product\Product')->findOneBy([
            'roomId' => $id,
            'isDeleted' => false,
        ]);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkPermissionForRoomUsage($product->getRoom()->getBuildingId());

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
            $status = array(
                Lease::LEASE_STATUS_CONFIRMED,
                Lease::LEASE_STATUS_RECONFIRMING,
                Lease::LEASE_STATUS_PERFORMING,
                Lease::LEASE_STATUS_END,
                Lease::LEASE_STATUS_MATURED,
                Lease::LEASE_STATUS_TERMINATED,
                Lease::LEASE_STATUS_CLOSED,
            );
            $results = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\Lease')
                ->getRoomUsersUsage(
                    $productId,
                    $yearStart,
                    $yearEnd,
                    $status
                );
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['room_usage']));
        $view->setData($results);

        return $view;
    }

    /**
     * @param $product
     */
    private function checkPermissionForRoomUsage(
        $buildingId
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                    'building_id' => $buildingId,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                    'building_id' => $buildingId,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
                    'building_id' => $buildingId,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
                    'building_id' => $buildingId,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );
    }
}
