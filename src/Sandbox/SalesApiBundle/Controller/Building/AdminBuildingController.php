<?php

namespace Sandbox\SalesApiBundle\Controller\Building;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\Location\LocationController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\Room\RoomAttachment;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingAttachment;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingCompany;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingPhones;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingServiceBinding;
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
    const ROOM_TYPE = 'room.type.';

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
        $this->throwAccessDeniedIfAdminNotAllowed(
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

        $orderControls = $this->getRepo('Door\DoorAccess')->getAccessByBuilding($id);
        if (is_null($orderControls) || empty($orderControls)) {
            return new Response();
        }

        $base = $building->getServer();
        foreach ($orderControls as $orderControl) {
            $orderId = $orderControl['orderId'];

            // check if order exists
            $order = $this->getRepo('Order\ProductOrder')->find($orderId);
            if (is_null($order)) {
                continue;
            }

            $this->syncAccessByOrder($base, $order);
        }

        return new Response();
    }

    /**
     * get buildings menu.
     *
     * @param Request $request the request object
     *
     * @Method({"GET"})
     * @Route("/buildings/menu")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBuildingsMenuAction(
        Request $request
    ) {
        $adminPlatform = $this->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $companyId = $adminPlatform['sales_company_id'];

        if ($platform != AdminPermission::PERMISSION_PLATFORM_SALES) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
        $this->throwNotFoundIfNull($companyId, self::NOT_FOUND_MESSAGE);

        $using = $this->getbuildingInfo($companyId, RoomBuilding::STATUS_ACCEPT, true);
        $invisible = $this->getbuildingInfo($companyId, RoomBuilding::STATUS_ACCEPT, false);
        $banned = $this->getbuildingInfo($companyId, RoomBuilding::STATUS_BANNED);
        $pending = $this->getbuildingInfo($companyId, RoomBuilding::STATUS_PENDING);

        $result = array(
            'using' => $using,
            'invisible' => $invisible,
            'banned' => $banned,
            'pending' => $pending,
        );

        return new View($result);
    }

    /**
     * Get Room Buildings.
     *
     * @param Request $request
     *
     * @Route("/buildings/{id}/roomtypes")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBuildingRoomTypesAction(
        Request $request,
        $id
    ) {
        $adminPlatform = $this->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $companyId = $adminPlatform['sales_company_id'];

        if ($platform != AdminPermission::PERMISSION_PLATFORM_SALES) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $building = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($id);
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        if ($building->getCompanyId() != $companyId) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $roomTypes = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomTypes')->findAll();

        $result = array();
        foreach ($roomTypes as $roomType) {
            $using_number = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->countsProductByType(
                    $id,
                    $roomType->getName(),
                    true
                );

            $all_number = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->countsProductByType(
                    $id,
                    $roomType->getName()
                );

            if ($all_number > 0) {
                $result[] = array(
                    'id' => $roomType->getId(),
                    'name' => $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$roomType->getName()),
                    'icon' => $roomType->getIcon(),
                    'using_number' => (int) $using_number,
                    'all_number' => (int) $all_number,
                );
            }
        }

        return new View($result);
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
        $this->throwAccessDeniedIfAdminNotAllowed(
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
            $this->setRoomBuildingMoreInformation($building);
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
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                [
                    'key' => AdminPermission::KEY_SALES_BUILDING_BUILDING,
                    'building_id' => $id,
                ],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        // get a building
        $building = $this->getRepo('Room\RoomBuilding')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        // set more information
        $this->setRoomBuildingMoreInformation($building);

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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING,
                ),
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
        $this->throwAccessDeniedIfAdminNotAllowed(
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
        $this->throwAccessDeniedIfAdminNotAllowed(
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
        $this->throwAccessDeniedIfAdminNotAllowed(
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
        $adminPlatform = $this->getAdminPlatform();

        $em = $this->getDoctrine()->getManager();
        $roomAttachments = $building->getRoomAttachments();
        $floors = $building->getFloors();
        $phones = $building->getPhones();
        $buildingAttachments = $building->getBuildingAttachments();
        $buildingCompany = $building->getBuildingCompany();
        $salesCompany = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($adminPlatform['sales_company_id']);
        $buildingServices = $building->getBuildingServices();

        // check city
        $roomCity = $this->getRepo('Room\RoomCity')->find($building->getCityId());
        if (is_null($roomCity)) {
            throw new BadRequestHttpException(self::NOT_FOUND_MESSAGE);
        }

        // add room building
        $this->addAdminBuilding(
            $building,
            $roomCity,
            $salesCompany,
            $em
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

        $em->flush();

        $buildingId = $building->getId();

        $response = array(
            'id' => $buildingId,
        );

        return new View($response);
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

        // check city
        $roomCity = $this->getRepo('Room\RoomCity')->find($building->getCityId());
        if (is_null($roomCity)) {
            throw new BadRequestHttpException(self::NOT_FOUND_MESSAGE);
        }

        // modify room building
        $this->modifyAdminBuilding(
            $building,
            $roomCity,
            $em
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

        $em->flush();

        return new View();
    }

    /**
     * Modify room building.
     *
     * @param RoomBuilding $building
     * @param RoomCity     $roomCity
     * @param              $em
     */
    private function modifyAdminBuilding(
        $building,
        $roomCity,
        $em
    ) {
        $now = new \DateTime('now');

        // change building status into pending while refused
        $status = $building->getStatus();
        if ($status == RoomBuilding::STATUS_REFUSE) {
            $building->setStatus(RoomBuilding::STATUS_PENDING);
        }

        $building->setCity($roomCity);
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
     */
    private function addAdminBuilding(
        $building,
        $roomCity,
        $salesCompany,
        $em
    ) {
        $now = new \DateTime('now');

        $building->setCompany($salesCompany);
        $building->setCity($roomCity);
        $building->setStatus(RoomBuilding::STATUS_PENDING);
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

        if (!empty($buildingCompany['phone']) && !is_numeric($buildingCompany['phone'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
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

        if (!empty($buildingCompany['phone']) && !is_numeric($buildingCompany['phone'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
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

    /**
     * @param $company
     * @param $status
     * @param null $visible
     *
     * @return array
     */
    private function getbuildingInfo(
        $company,
        $status,
        $visible = null
    ) {
        $buildings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getLocationRoomBuildings(
                null,
                null,
                $company,
                $status,
                $visible
            );

        $result = array();
        foreach ($buildings as $building) {
            $allNumber = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->countsProductByBuilding($building);

            $usingNumber = 0;
            if ($visible == true) {
                $usingNumber = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Product\Product')
                    ->countsProductByBuilding($building, $visible);
            }

            $result[] = array(
                'id' => $building->getId(),
                'name' => $building->getName(),
                'using_number' => (int) $usingNumber,
                'all_number' => (int) $allNumber,
            );
        }

        return $result;
    }
}
