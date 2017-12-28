<?php

namespace Sandbox\SalesApiBundle\Controller\Building;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\Location\LocationController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroupMember;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\Room\RoomAttachment;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingAttachment;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingCompany;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingPhones;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingServiceBinding;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingServiceMember;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Sandbox\ApiBundle\Entity\Room\RoomFloor;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;
use Sandbox\ApiBundle\Form\Room\RoomAttachmentPostType;
use Sandbox\ApiBundle\Form\Room\RoomBuildingAttachmentPostType;
use Sandbox\ApiBundle\Form\Room\RoomBuildingCompanyPostType;
use Sandbox\ApiBundle\Form\Room\RoomBuildingCompanyPutType;
use Sandbox\ApiBundle\Form\Room\RoomBuildingPostType;
use Sandbox\ApiBundle\Form\Room\RoomBuildingPutType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesBuildingPatchVisibleType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManager;

/**
 * Class AdminBuildingController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminBuildingController extends LocationController
{
    const ROOM_FLOOR_BAK = '.bak';

    /**
     * Get Room Buildings.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="query key word"
     * )
     *
     * @Route("/buildings/search")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function findAdminBuildingsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // filters
        $query = $paramFetcher->get('query');

        // get my buildings list
        $buildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_PLATFORM_BUILDING,
                AdminPermission::KEY_SALES_BUILDING_BUILDING,
            )
        );

        $buildings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getMySalesBuildings(
                $query,
                $buildingIds
            );

        $result = array();
        foreach ($buildings as $building) {
            $result[] = array(
                'id' => $building->getId(),
                'name' => $building->getName(),
                'avatar' => $building->getAvatar(),
                'address' => $building->getAddress(),
            );
        }

        return new View($result);
    }

    /**
     * @Route("/buildings/{id}/room/attachment")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function postRoomAttachmentAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                ['key' => AdminPermission::KEY_SALES_BUILDING_SPACE],
                ['key' => AdminPermission::KEY_SALES_BUILDING_BUILDING],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING],
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        $em = $this->getDoctrine()->getManager();

        $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($id);
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        $payload = json_decode($request->getContent(), true);

        $attachments = $payload['room_attachments'];

        foreach ($attachments as $attachment) {
            $roomAttachment = new RoomAttachment();
            $form = $this->createForm(new RoomAttachmentPostType(), $roomAttachment);
            $form->submit($attachment, true);

            $roomAttachment->setBuilding($building);
            $roomAttachment->setCreationDate(new \DateTime('now'));
            $em->persist($roomAttachment);
        }

        $em->flush();

        return new View();
    }

    /**
     * @Route("/buildings/{id}/sync")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function syncAccessByBuildingAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_BUILDING,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        $building = $this->getRepo('Room\RoomBuilding')->find($id);
        if (is_null($building)) {
            throw new NotFoundHttpException(RoomBuilding::BUILDING_NOT_FOUND_MESSAGE);
        }

        $base = $building->getServer();
        if (is_null($base) || empty($base)) {
            return;
        }

        $orderControls = $this->getRepo('Door\DoorAccess')->getAccessByBuilding($id);

        foreach ($orderControls as $orderControl) {
            $this->syncAccessByOrder($base, $orderControl);
        }

        return new Response();
    }

    /**
     * Get Room Buildings.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="city id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="query key word"
     * )
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    description="building id"
     * )
     *
     * @Route("/buildings")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminBuildingsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_BUILDING_BUILDING],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $cityId = $paramFetcher->get('city');
        $query = $paramFetcher->get('query');
        $buildingIds = $paramFetcher->get('id');

        // custom building ids
        if (!empty($buildingIds)) {
            $buildings = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->getSalesRoomBuildings(
                    $cityId,
                    $query,
                    $buildingIds
                );

            return new View($buildings);
        }

        // get my buildings list
        $buildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_PLATFORM_BUILDING,
                AdminPermission::KEY_SALES_BUILDING_BUILDING,
            )
        );

        $buildings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getSalesRoomBuildings(
                $cityId,
                $query,
                $buildingIds
            );
        foreach ($buildings as $building) {
            // set more information
            $this->setRoomBuildingMoreInformation($building, $request);
        }

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $buildings,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get definite id of building.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Route("/buildings/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminBuildingAction(
        Request $request,
        $id
    ) {
        // get a building
        $building = $this->getRepo('Room\RoomBuilding')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        // check user permission
        $companyId = $building->getCompanyId();
        $this->get('sandbox_api.admin_permission_check_service')
            ->checkHasPosition(
                $this->getAdminId(),
                AdminPermission::PERMISSION_PLATFORM_SALES,
                $companyId
            );

        // set more information
        $this->setRoomBuildingMoreInformation($building, $request);

        // set view
        $view = new View($building);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('admin_building'))
        );

        return $view;
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Method({"POST"})
     * @Route("/buildings")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postAdminBuildingAction(
        Request $request
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                ['key' => AdminPermission::KEY_SALES_BUILDING_SPACE],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING],
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        $building = new RoomBuilding();

        $form = $this->createForm(new RoomBuildingPostType(), $building);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $response = $this->handleAdminBuildingPost(
            $building
        );

        // add log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_BUILDING,
            'logAction' => Log::ACTION_CREATE,
            'logObjectKey' => Log::OBJECT_BUILDING,
            'logObjectId' => $building->getId(),
        ));

        return $response;
    }

    /**
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
     * @Method({"PUT"})
     * @Route("/buildings/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putAdminBuildingAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                [
                    'key' => AdminPermission::KEY_SALES_BUILDING_SPACE,
                    'building_id' => $id,
                ],
                [
                    'key' => AdminPermission::KEY_SALES_BUILDING_BUILDING,
                    'building_id' => $id,
                ],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING],
            ],
            AdminPermission::OP_LEVEL_EDIT
        );

        $building = $this->getRepo('Room\RoomBuilding')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new RoomBuildingPutType(),
            $building,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // handle building form
        $response = $this->handleAdminBuildingPut(
            $building
        );

        // add log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_BUILDING,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_BUILDING,
            'logObjectId' => $building->getId(),
        ));

        return $response;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Route("/buildings/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchAdminBuildingVisibleAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                [
                    'key' => AdminPermission::KEY_SALES_BUILDING_SPACE,
                    'building_id' => $id,
                ],
                [
                    'key' => AdminPermission::KEY_SALES_BUILDING_BUILDING,
                    'building_id' => $id,
                ],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING],
            ],
            AdminPermission::OP_LEVEL_EDIT
        );

        $building = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->findOneBy(array(
                'id' => $id,
                'isDeleted' => false,
                'status' => RoomBuilding::STATUS_ACCEPT,
            ));
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        $statusOld = $building->getStatus();
        $visibleOld = $building->isVisible();

        // bind data
        $buildingJson = $this->container->get('serializer')->serialize($building, 'json');
        $patch = new Patch($buildingJson, $request->getContent());
        $adminJson = $patch->apply();

        $form = $this->createForm(new SalesBuildingPatchVisibleType(), $building);
        $form->submit(json_decode($adminJson, true));

        // handle building status
        $this->handleBuildingVisible(
            $statusOld,
            $visibleOld,
            $building
        );

        // add log
        if ($visibleOld && !$building->isVisible()) {
            $action = Log::ACTION_OFF_SALE;
        } else {
            $action = Log::ACTION_ON_SALE;
        }

        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_BUILDING,
            'logAction' => $action,
            'logObjectKey' => Log::OBJECT_BUILDING,
            'logObjectId' => $building->getId(),
        ));

        return new View();
    }

    /**
     * Get definite id of building.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Route("/buildings/{id}/lessor")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBuildingLessorAction(
        Request $request,
        $id
    ) {
        // check user permission

        // get a building
        $building = $this->getRepo('Room\RoomBuilding')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        // set view
        $view = new View($building);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('lessor'))
        );

        return $view;
    }

    /**
     * @param string       $statusOld
     * @param string       $visibleOld
     * @param RoomBuilding $building
     */
    private function handleBuildingVisible(
        $statusOld,
        $visibleOld,
        $building
    ) {
        $em = $this->getDoctrine()->getManager();

        if ($statusOld != RoomBuilding::STATUS_ACCEPT) {
            return;
        }

        $visible = $building->isVisible();
        if ($visibleOld == $visible) {
            return;
        }

        if (!$visible) {
            // hide products
            $this->hideProductsByBuilding(
                $building
            );

            // hide shops
            $this->hideShopByBuilding(
                $building
            );
        } else {
            // recover valid productions' visible status
            $this->getRepo('Product\Product')->setVisibleTrue($building);
        }

        $em->flush();
    }

    /**
     * @param $building
     */
    private function hideProductsByBuilding(
        $building
    ) {
        // hide all of the products
        $products = $this->getRepo('Product\Product')->getSalesProductsByBuilding($building);

        if (empty($products)) {
            return;
        }

        foreach ($products as $product) {
            $product->setVisible(false);
        }
    }

    /**
     * @param $building
     */
    private function hideShopByBuilding(
        $building
    ) {
        // hide all shops
        $this->getRepo('Shop\Shop')->setShopOffline($building);

        // set shops
        $shops = $this->getRepo('Shop\Shop')->findByBuilding($building);

        if (empty($shops)) {
            return;
        }

        foreach ($shops as $shop) {
            // set shop products offline
            $this->getRepo('Shop\ShopProduct')->setShopProductsOfflineByShopId(
                $shop->getId()
            );
        }
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Method({"DELETE"})
     * @Route("/buildings/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteAdminBuildingAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                [
                    'key' => AdminPermission::KEY_SALES_BUILDING_BUILDING,
                    'building_id' => $id,
                ],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING],
            ],
            AdminPermission::OP_LEVEL_EDIT
        );

        $building = $this->getRepo('Room\RoomBuilding')->find($id);
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        if ($building->getStatus() == RoomBuilding::STATUS_BANNED) {
            return new View();
        }

        $building->setIsDeleted(true);

        // delete all of the rooms
        $rooms = $this->getRepo('Room\Room')->findByBuilding($building);
        if (!empty($rooms)) {
            foreach ($rooms as $room) {
                $room->setIsDeleted(true);
            }
        }

        // delete all of the products
        $products = $this->getRepo('Product\Product')->getSalesProductsByBuilding($building);
        if (!empty($products)) {
            foreach ($products as $product) {
                $product->setVisible(false);
                $product->setIsDeleted(true);
            }
        }

        // delete all of the shops
        $this->getRepo('Shop\Shop')->setShopDeleted(
            $building
        );

        // delete all of the shop products
        $this->getRepo('Shop\ShopProduct')->setShopProductsDeletedByBuilding(
            $building
        );

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // add log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_BUILDING,
            'logAction' => Log::ACTION_DELETE,
            'logObjectKey' => Log::OBJECT_BUILDING,
            'logObjectId' => $building->getId(),
        ));

        return new View();
    }

    /**
     * Save room building to db.
     *
     * @param RoomBuilding $building
     *
     * @return View
     */
    private function handleAdminBuildingPost(
        $building
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $em = $this->getDoctrine()->getManager();
        $roomAttachments = $building->getRoomAttachments();
        $floors = $building->getFloors();
        $phones = $building->getPhones();
        $buildingAttachments = $building->getBuildingAttachments();
        $buildingCompany = $building->getBuildingCompany();
        $customerServicesIds = $building->getCustomerServices();
        $removeDates = $building->getRemoveDates();

        $salesCompany = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($salesCompanyId);
        $buildingServices = $building->getBuildingServices();

        // check city
        $roomCity = !is_null($building->getCityId()) ?
            $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomCity')->find($building->getCityId()) : null;

        if (is_null($roomCity)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $district = !is_null($building->getDistrictId()) ?
            $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomCity')->find($building->getDistrictId()) : null;

        // add room building
        $this->addAdminBuilding(
            $building,
            $roomCity,
            $salesCompany,
            $em,
            $district
        );

        // add room attachments
        $this->addRoomAttachments(
            $building,
            $roomAttachments,
            $em
        );

        // add floors
        $this->addFloors(
            $building,
            $floors,
            $em
        );

        if (!is_null($phones) && !empty($phones)) {
            // add admin phones
            $this->addPhones(
                $building,
                $phones,
                $em
            );
        }

        // add building company
        $this->addBuildingCompany(
            $building,
            $buildingCompany,
            $em
        );

        // add building attachments
        $this->addBuildingAttachments(
            $building,
            $buildingAttachments,
            $em
        );

        // add building services
        $this->addBuildingServices(
            $building,
            $buildingServices,
            $em
        );

        // add customer services
//        $this->addCustomerService(
//            $salesCompanyId,
//            $building,
//            $customerServicesIds,
//            $em
//        );

        $building = $this->addRemoveDates($building, $removeDates);

        $em->flush();

        $buildingId = $building->getId();

        $response = array(
            'id' => $buildingId,
        );

        return new View($response);
    }

    /**
     * @param $building
     * @param $removeDates
     *
     * @return mixed
     */
    private function addRemoveDates(
        $building,
        $removeDates
    ) {
        if (!is_null($removeDates) && !empty($removeDates)) {
            $removeDates = json_encode($removeDates, true);
        }

        $building->setRemoveDatesInfo($removeDates);

        return $building;
    }

    /**
     * Save room building to db.
     *
     * @param RoomBuilding $building
     *
     * @return View
     */
    private function handleAdminBuildingPut(
        $building
    ) {
        $em = $this->getDoctrine()->getManager();
        $roomAttachments = $building->getRoomAttachments();
        $floors = $building->getFloors();
        $phones = $building->getPhones();
        $buildingAttachments = $building->getBuildingAttachments();
        $buildingCompany = $building->getBuildingCompany();
        $buildingServices = $building->getBuildingServices();
        $removeDates = $building->getRemoveDates();

        // check city
        $roomCity = !is_null($building->getCityId()) ?
            $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomCity')->find($building->getCityId()) : null;

        if (is_null($roomCity)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $district = !is_null($building->getDistrictId()) ?
            $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomCity')->find($building->getDistrictId()) : null;

        // modify room building
        $this->modifyAdminBuilding(
            $building,
            $roomCity,
            $em,
            $district
        );

        // add room attachments
        $this->addRoomAttachments(
            $building,
            $roomAttachments,
            $em
        );

        // remove room attachments
        $this->removeRoomAttachments(
            $roomAttachments,
            $em
        );

        if (!is_null($phones) && !empty($phones)) {
            // add admin phones
            $this->addPhones(
                $building,
                $phones,
                $em
            );

            // modify admin phones
            $this->modifyPhones($phones);

            // remove admin phones
            $this->removePhones(
                $phones,
                $em
            );
        }

        // remove room attachments
        $this->removeBuildingAttachments(
            $building,
            $buildingAttachments,
            $em
        );

        // add building attachments
        $this->addBuildingAttachments(
            $building,
            $buildingAttachments,
            $em
        );

        // modify building company
        $this->modifyBuildingCompany(
            $building,
            $buildingCompany,
            $em
        );

        // modify floors
        $this->modifyFloors(
            $building,
            $floors,
            $em
        );

        // add floor number
        $this->addFloors(
            $building,
            $floors,
            $em
        );

        // remove old building services
        $this->removeBuildingServices(
            $building,
            $em
        );

        // add new building services
        $this->addBuildingServices(
            $building,
            $buildingServices,
            $em
        );

        // update customer service members
        $this->updateCustomerServices(
            $building,
            $em
        );

        $building = $this->addRemoveDates($building, $removeDates);

        $em->flush();

        return new View();
    }

    /**
     * @param RoomBuilding  $building
     * @param EntityManager $em
     */
    private function updateCustomerServices(
        $building,
        $em
    ) {
        $services = $building->getCustomerServices();
        $buildingId = $building->getId();
        $companyId = $building->getCompanyId();
        $chatGroups = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
            ->findBy([
                'buildingId' => $buildingId,
                'tag' => ChatGroup::CUSTOMER_SERVICE
            ]);

        if (array_key_exists('add', $services)) {
            $this->addChatGroupMembers(
                $em,
                $services,
                $companyId,
                $buildingId,
                $chatGroups
            );
        }

        if (array_key_exists('remove', $services)) {
            $this->removeChatGroupMembers(
                $em,
                $services,
                $buildingId,
                $chatGroups
            );
        }
    }

    /**
     * @param EntityManager $em
     * @param array         $services
     * @param int           $companyId
     * @param int           $buildingId
     * @param array         $chatGroups
     */
    private function addChatGroupMembers(
        $em,
        $services,
        $companyId,
        $buildingId,
        $chatGroups
    ) {
        $addServices = $services['add'];
        $addGroups = [];

        $addServices = array_unique($addServices, SORT_REGULAR);

        foreach ($addServices as $addService) {
            $userId = $addService['user_id'];
            $tag = $addService['tag'];

            $user = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->findOneBy([
                    'id' => $userId,
                    'banned' => false,
                ]);
            if (is_null($user)) {
                continue;
            }

            //check user is company admin
            $positions = $this->checkUserIsAdmin(
                $userId,
                $companyId
            );

            if (empty($positions)) {
                continue;
            }

            // check if user already added
            $member = $this->getExistingService(
                $buildingId,
                $userId,
                $tag
            );

            if (!is_null($member)) {
                continue;
            }

            $newMember = new RoomBuildingServiceMember();
            $newMember->setBuildingId($buildingId);
            $newMember->setCompanyId($companyId);
            $newMember->setUserId($userId);
            $newMember->setTag($tag);

            $em->persist($newMember);

            //add member to chat group
            foreach ($chatGroups as $chatGroup) {
                $groupMember = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:ChatGroup\ChatGroupMember')
                    ->findOneBy([
                        'chatGroup' => $chatGroup,
                        'user' => $user,
                    ]);
                if (!is_null($groupMember)) {
                    continue;
                }

                if ($chatGroup->getTag() !== $tag) {
                    continue;
                }

                $newGroupMember = new ChatGroupMember();
                $newGroupMember->setChatGroup($chatGroup);
                $newGroupMember->setUser($user->getId());
                $newGroupMember->setAddBy($this->getUser()->getMyUser());

                $em->persist($newGroupMember);

                $groupId = $chatGroup->getId();
                $addGroups[$groupId][] = $user->getId();
            }
        }

        $em->flush();

        $appKey = $this->getParameter('jpush_property_key');
        foreach ($addGroups as $key => $users) {
            $group = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
                ->find($key);

            $membersIds = [];
            foreach ($users as $userId) {
                $salesAdmin = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
                    ->findOneBy(array('userId' => $userId));
                if ($salesAdmin) {
                    $membersIds[] = $salesAdmin->getXmppUsername();
                }
            }
            // call openfire
            $this->addXmppChatGroupMember($group, $membersIds, $appKey);
        }
    }

    /**
     * @param EntityManager $em
     * @param array         $services
     * @param int           $buildingId
     * @param array         $chatGroups
     */
    private function removeChatGroupMembers(
        $em,
        $services,
        $buildingId,
        $chatGroups
    ) {
        $removeServices = $services['remove'];
        $removeGroups = [];

        $removeServices = array_unique($removeServices, SORT_REGULAR);

        foreach ($removeServices as $removeService) {
            $userId = $removeService['user_id'];
            $tag = $removeService['tag'];

            $member = $this->getExistingService(
                $buildingId,
                $userId,
                $tag
            );

            if (is_null($member)) {
                continue;
            }

            $em->remove($member);

            //remove member from chat group
            foreach ($chatGroups as $chatGroup) {
                $groupMember = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:ChatGroup\ChatGroupMember')
                    ->findOneBy([
                        'chatGroup' => $chatGroup,
                        'user' => $userId,
                    ]);
                if (is_null($groupMember)) {
                    continue;
                }

                if ($chatGroup->getTag() !== $tag) {
                    continue;
                }

                $em->remove($groupMember);

                $groupId = $chatGroup->getId();
                $removeGroups[$groupId][] = $groupMember->getUser();
            }
        }

        $em->flush();

        $appKey = $this->getParameter('jpush_property_key');
        foreach ($removeGroups as $key => $users) {
            $group = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
                ->find($key);

            $membersIds = [];
            foreach ($users as $userId) {
                $salesAdmin = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
                    ->findOneBy(array('userId' => $userId));
                if ($salesAdmin) {
                    $membersIds[] = $salesAdmin->getXmppUsername();
                }
            }

            // call openfire
            $this->deleteXmppChatGroupMember($group, $membersIds, $appKey);
        }
    }

    /**
     * @param $buildingId
     * @param $userId
     * @param $tag
     *
     * @return \Sandbox\ApiBundle\Entity\Room\RoomBuildingServiceMember
     */
    private function getExistingService(
        $buildingId,
        $userId,
        $tag
    ) {
        $member = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingServiceMember')
            ->findOneBy([
                'userId' => $userId,
                'tag' => $tag,
                'buildingId' => $buildingId,
            ]);

        return $member;
    }

    /**
     * Modify room building.
     *
     * @param RoomBuilding $building
     * @param RoomCity     $roomCity
     * @param              $em
     * @param RoomCity     $district
     */
    private function modifyAdminBuilding(
        $building,
        $roomCity,
        $em,
        $district
    ) {
        $now = new \DateTime('now');

        // change building status into pending while refused
        $status = $building->getStatus();
        if ($status == RoomBuilding::STATUS_REFUSE) {
            $building->setStatus(RoomBuilding::STATUS_PENDING);
        }

        $building->setCity($roomCity);
        $building->setDistrict($district);
        $building->setModificationDate($now);

        $em->flush();
    }

    /**
     * @param array         $roomAttachments
     * @param EntityManager $em
     */
    private function removeRoomAttachments(
        $roomAttachments,
        $em
    ) {
        // check room attachments
        if (!isset($roomAttachments['remove']) || empty($roomAttachments['remove'])) {
            return;
        }

        foreach ($roomAttachments['remove'] as $attachment) {
            $attachment = $this->getRepo('Room\RoomAttachment')->find($attachment['id']);
            $em->remove($attachment);
        }
    }

    /**
     * @param RoomBuilding  $building
     * @param array         $roomBuildingAttachments
     * @param EntityManager $em
     */
    private function removeBuildingAttachments(
        $building,
        $roomBuildingAttachments,
        $em
    ) {
        $attachments = $this->getRepo('Room\RoomBuildingAttachment')->findByBuilding($building);
        if (empty($attachments)) {
            return;
        }

        foreach ($attachments as $attachment) {
            $em->remove($attachment);
        }
    }

    /**
     * Modify floor numbers.
     *
     * @param RoomBuilding  $building
     * @param array         $floors
     * @param EntityManager $em
     */
    private function modifyFloors(
        $building,
        $floors,
        $em
    ) {
        if (!isset($floors['modify']) || empty($floors['modify'])) {
            return;
        }

        //modify to prevent duplicate
        foreach ($floors['modify'] as $floor) {
            $roomFloor = $this->getRepo('Room\RoomFloor')->find($floor['id']);
            $roomFloor->setFloorNumber($roomFloor->getFloorNumber().self::ROOM_FLOOR_BAK);

            $em->persist($roomFloor);
        }
        $em->flush();

        //modify floors
        foreach ($floors['modify'] as $floor) {
            $roomFloor = $this->getRepo('Room\RoomFloor')->find($floor['id']);
            $roomFloor->setFloorNumber($floor['floor_number']);

            $em->persist($roomFloor);
        }
        $em->flush();
    }

    /**
     * Add room building.
     *
     * @param RoomBuilding  $building
     * @param RoomCity      $roomCity
     * @param SalesCompany  $salesCompany
     * @param EntityManager $em
     * @param RoomCity      $area
     */
    private function addAdminBuilding(
        $building,
        $roomCity,
        $salesCompany,
        $em,
        $area
    ) {
        $now = new \DateTime('now');

        $building->setCompany($salesCompany);
        $building->setCity($roomCity);
        $building->setDistrict($area);
        $building->setStatus(RoomBuilding::STATUS_ACCEPT);
        $building->setCreationDate($now);
        $building->setModificationDate($now);

        $em->persist($building);
    }

    /**
     * Add room attachments.
     *
     * @param RoomBuilding  $building
     * @param array         $roomAttachments
     * @param EntityManager $em
     */
    private function addRoomAttachments(
        $building,
        $roomAttachments,
        $em
    ) {
        // check room attachments
        if (!isset($roomAttachments['add']) || empty($roomAttachments['add'])) {
            return;
        }

        foreach ($roomAttachments['add'] as $attachment) {
            $roomAttachment = new RoomAttachment();
            $form = $this->createForm(new RoomAttachmentPostType(), $roomAttachment);
            $form->submit($attachment, true);

            $roomAttachment->setBuilding($building);
            $roomAttachment->setCreationDate(new \DateTime('now'));
            $em->persist($roomAttachment);
        }
    }

    /**
     * Add floors.
     *
     * @param RoomBuilding  $building
     * @param array         $floors
     * @param EntityManager $em
     */
    private function addFloors(
        $building,
        $floors,
        $em
    ) {
        if (!isset($floors['add']) || empty($floors['add'])) {
            return;
        }

        foreach ($floors['add'] as $floor) {
            $roomFloor = new RoomFloor();
            $roomFloor->setBuilding($building);
            $roomFloor->setFloorNumber($floor['floor_number']);

            $em->persist($roomFloor);
        }
    }

    /**
     * Add admin phones.
     *
     * @param RoomBuilding       $building
     * @param RoomBuildingPhones $phones
     * @param EntityManager      $em
     */
    private function addPhones(
        $building,
        $phones,
        $em
    ) {
        if (!isset($phones['add']) || empty($phones['add'])) {
            return;
        }

        foreach ($phones['add'] as $phone) {
            if (!is_numeric($phone['phone_number'])) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
            $adminPhones = new RoomBuildingPhones();
            $adminPhones->setBuilding($building);
            $adminPhones->setPhone($phone['phone_number']);

            $em->persist($adminPhones);
        }
    }

    /**
     * Add admin building company.
     *
     * @param RoomBuilding        $building
     * @param RoomBuildingCompany $buildingCompany
     * @param EntityManager       $em
     */
    private function addBuildingCompany(
        $building,
        $buildingCompany,
        $em
    ) {
        if (empty($buildingCompany)) {
            return;
        }

        $company = new RoomBuildingCompany();
        $form = $this->createForm(new RoomBuildingCompanyPostType(), $company);
        $form->submit($buildingCompany);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $company->setBuilding($building);

        $em->persist($company);
    }

    /**
     * Modify building company.
     *
     * @param RoomBuilding        $building
     * @param RoomBuildingCompany $buildingCompany
     * @param EntityManager       $em
     */
    private function modifyBuildingCompany(
        $building,
        $buildingCompany,
        $em
    ) {
        if (empty($buildingCompany)) {
            return;
        }

        $company = $this->getRepo('Room\RoomBuildingCompany')->findOneByBuilding($building);

        // check if building company exist
        if (is_null($company)) {
            $company = new RoomBuildingCompany();
        }
        $form = $this->createForm(new RoomBuildingCompanyPutType(), $company);
        $form->submit($buildingCompany);

        $company->setBuilding($building);
        $company->setModificationDate(new \DateTime('now'));

        $em->persist($company);
    }

    /**
     * Add building attachments.
     *
     * @param RoomBuilding           $building
     * @param RoomBuildingAttachment $buildingAttachments
     * @param EntityManager          $em
     */
    private function addBuildingAttachments(
        $building,
        $buildingAttachments,
        $em
    ) {
        if (empty($buildingAttachments)) {
            return;
        }

        foreach ($buildingAttachments as $attachment) {
            $buildingAttachment = new RoomBuildingAttachment();
            $form = $this->createForm(new RoomBuildingAttachmentPostType(), $buildingAttachment);
            $form->submit($attachment);

            $buildingAttachment->setBuilding($building);

            $em->persist($buildingAttachment);
        }
    }

    /**
     * @param RoomBuildingPhones $phones
     */
    private function modifyPhones(
        $phones
    ) {
        if (!isset($phones['modify']) || empty($phones['modify'])) {
            return;
        }

        foreach ($phones['modify'] as $phone) {
            if (!is_numeric($phone['phone_number'])) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $adminPhone = $this->getRepo('Room\RoomBuildingPhones')->find($phone['id']);
            if (!is_null($adminPhone)) {
                $adminPhone->setPhone($phone['phone_number']);
                $adminPhone->setModificationDate(new \DateTime());
            }
        }
    }

    /**
     * @param RoomBuildingPhones $phones
     * @param EntityManager      $em
     */
    private function removePhones(
        $phones,
        $em
    ) {
        if (!isset($phones['remove']) || empty($phones['remove'])) {
            return;
        }

        foreach ($phones['remove'] as $phone) {
            $adminPhone = $this->getRepo('Room\RoomBuildingPhones')->find($phone['id']);
            if (!is_null($adminPhone)) {
                $em->remove($adminPhone);
            }
        }
    }

    /**
     * @param $building
     * @param $buildingServices
     * @param $em
     */
    private function addBuildingServices(
        $building,
        $buildingServices,
        $em
    ) {
        if (empty($buildingServices)) {
            return;
        }

        foreach ($buildingServices as $service) {
            if (!isset($service['id'])) {
                continue;
            }

            $serviceObject = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuildingServices')
                ->find($service['id']);
            if (is_null($serviceObject)) {
                continue;
            }

            $buildingServiceBinding = new RoomBuildingServiceBinding();

            $buildingServiceBinding->setBuilding($building);
            $buildingServiceBinding->setService($serviceObject);

            $em->persist($buildingServiceBinding);
        }
    }

    /**
     * @param $building
     * @param $em
     */
    private function removeBuildingServices(
        $building,
        $em
    ) {
        if (empty($building)) {
            return;
        }

        $services = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingServiceBinding')
            ->findBy(array(
                'building' => $building,
            ));

        if (!empty($services)) {
            foreach ($services as $service) {
                $em->remove($service);
            }
        }

        $em->flush();
    }

    private function addCustomerService(
        $salesCompanyId,
        $building,
        $customerServices,
        $em
    ) {
        foreach ($customerServices as $key => $val) {
            $addServices = array_unique($val, SORT_REGULAR);

            foreach ($addServices as $userId) {
                $admin = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->findOneBy(['userId' => $userId]);
                if (is_null($admin)) {
                    continue;
                }

                //check user is company admin
                $positions = $this->checkUserIsAdmin(
                    $userId,
                    $building->getCompanyId()
                );
                if (empty($positions)) {
                    continue;
                }

                $customerService = $this->getServiceMemberRepo()->findOneBy(
                    array(
                        'buildingId' => $building->getId(),
                        'userId' => $admin->getId(),
                        'tag' => $key,
                    )
                );
                if (!is_null($customerService)) {
                    continue;
                }

                $customerService = new RoomBuildingServiceMember();
                $customerService->setCompanyId($salesCompanyId);
                $customerService->setBuildingId($building->getId());
                $customerService->setUserId($admin->getId());

                $em->persist($customerService);
            }
        }
    }

    /**
     * @param $userId
     * @param $companyId
     *
     * @return array
     */
    protected function checkUserIsAdmin(
        $userId,
        $companyId
    ) {
        $positions = [];

        //check user is company admin
        $admins = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->findBy(['userId' => $userId]);

        foreach ($admins as $admin) {
            $position = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPosition')
                ->findOneBy([
                    'id' => $admin->getPositionId(),
                    'platform' => AdminPosition::PLATFORM_SALES,
                    'salesCompanyId' => $companyId,
                    'isHidden' => false,
                ]);
            if (is_null($position)) {
                continue;
            }

            array_push($positions, $position);
        }

        return $positions;
    }
}
