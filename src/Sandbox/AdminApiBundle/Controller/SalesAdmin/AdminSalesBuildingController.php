<?php

namespace Sandbox\AdminApiBundle\Controller\SalesAdmin;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\Location\LocationController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingTagBinding;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingTypeBinding;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesBuildingPatchType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
class AdminSalesBuildingController extends LocationController
{
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
        $this->checkAdminBuildingPermission(AdminPermissionMap::OP_LEVEL_VIEW);

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
     *    name="admin",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="sales admin id"
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
        $this->checkAdminBuildingPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // filters
        $adminId = $paramFetcher->get('admin');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $cityId = $paramFetcher->get('city');
        $query = $paramFetcher->get('query');

        $salesBuildingIds = null;
        if (!is_null($adminId)) {
            // get sales buildings
            $salesBuildingIds = $this->getSalesSuperAdminBuildingIds($adminId);
        }

        $buildings = $this->getRepo('Room\RoomBuilding')->getSalesRoomBuildings(
            $cityId,
            $query,
            $salesBuildingIds
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
        $this->checkAdminBuildingPermission(AdminPermissionMap::OP_LEVEL_VIEW);

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
     * Modify a building.
     *
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
    public function patchAdminBuildingAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminBuildingPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $building = $this->getRepo('Room\RoomBuilding')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        $statusOld = $building->getStatus();

        // not allow change building that is refused
        if ($statusOld == RoomBuilding::STATUS_REFUSE) {
            return new View();
        }

        // bind data
        $buildingJson = $this->container->get('serializer')->serialize($building, 'json');
        $patch = new Patch($buildingJson, $request->getContent());
        $buildingJson = $patch->apply();

        $form = $this->createForm(new SalesBuildingPatchType(), $building);
        $form->submit(json_decode($buildingJson, true));

        $em = $this->getDoctrine()->getManager();

        // handle building status
        $this->handleBuildingStatus(
            $statusOld,
            $building
        );

        // add building tags
        $this->addBuildingTags($building, $em);

        // add building room types
        $this->addBuildingRoomTypes($building, $em);

        $em->flush();

        return new View();
    }

    /**
     * @param string       $statusOld
     * @param RoomBuilding $building
     */
    private function handleBuildingStatus(
        $statusOld,
        $building
    ) {
        $status = $building->getStatus();

        if ($statusOld == $status) {
            return;
        }

        if (!in_array($status, array(
            RoomBuilding::STATUS_ACCEPT,
            RoomBuilding::STATUS_REFUSE,
            RoomBuilding::STATUS_BANNED,
        ))) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        if ($status == RoomBuilding::STATUS_BANNED) {
            // banned all staff under this building
            $this->bannedAllUnderBuilding(
                $building
            );
        } elseif (
            $statusOld == RoomBuilding::STATUS_PENDING &&
            $status == RoomBuilding::STATUS_ACCEPT
        ) {
            $building->setVisible(true);
        }
    }

    /**
     * @param RoomBuilding  $building
     * @param EntityManager $em
     */
    private function addBuildingTags(
        $building,
        $em
    ) {
        $tags = $building->getBuildingTags();

        if (is_null($tags)) {
            return;
        }

        // remove old tags
        $tagBindings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingTagBinding')
            ->findBy(array(
                'building' => $building,
            ));

        foreach ($tagBindings as $binding) {
            $em->remove($binding);
        }

        $em->flush();

        if (empty($tags)) {
            return;
        }

        // add new tags
        foreach ($tags as $tag) {
            if (!isset($tag['id'])) {
                continue;
            }

            $tagEntity = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuildingTag')
                ->find($tag['id']);
            if (is_null($tagEntity)) {
                continue;
            }

            $tagBindingObject = new RoomBuildingTagBinding();
            $tagBindingObject->setBuilding($building);
            $tagBindingObject->setTag($tagEntity);

            $em->persist($tagBindingObject);
        }
    }

    /**
     * @param RoomBuilding  $building
     * @param EntityManager $em
     */
    private function addBuildingRoomTypes(
        $building,
        $em
    ) {
        $types = $building->getBuildingRoomTypes();

        if (is_null($types)) {
            return;
        }

        // remove old types
        $typeBindings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingTypeBinding')
            ->findBy(array(
                'building' => $building,
            ));

        foreach ($typeBindings as $binding) {
            $em->remove($binding);
        }

        $em->flush();

        if (empty($types)) {
            return;
        }

        // add new types
        foreach ($types as $type) {
            if (!isset($type['id'])) {
                continue;
            }

            $typeEntity = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomTypes')
                ->find($type['id']);
            if (is_null($typeEntity)) {
                continue;
            }

            $typeBindingObject = new RoomBuildingTypeBinding();
            $typeBindingObject->setBuilding($building);
            $typeBindingObject->setType($typeEntity);

            $em->persist($typeBindingObject);
        }
    }

    /**
     * @param $adminId
     *
     * @return array
     */
    private function getSalesSuperAdminBuildingIds(
        $adminId
    ) {
        $salesAdmin = $this->getRepo('SalesAdmin\SalesAdmin')->find($adminId);
        $this->throwNotFoundIfNull($salesAdmin, self::NOT_FOUND_MESSAGE);

        $buildings = $this->getRepo('Room\RoomBuilding')->findByCompanyId($salesAdmin->getCompanyId());

        if (empty($buildings)) {
            return array();
        }

        $ids = array();
        foreach ($buildings as $building) {
            array_push($ids, $building->getId());
        }

        return $ids;
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminBuildingPermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES,
            $opLevel
        );
    }

    /**
     * @param RoomBuilding $building
     */
    private function bannedAllUnderBuilding(
        $building
    ) {
        // check user permission
        $this->checkAdminBuildingPermission(AdminPermissionMap::OP_LEVEL_USER_BANNED);

        $building->setVisible(false);

        // hide all shops
        $this->getRepo('Shop\Shop')->setShopOffline($building);

        // hide all of the products
        $products = $this->getRepo('Product\Product')->getSalesProductsByBuilding($building);

        if (empty($products)) {
            return;
        }

        foreach ($products as $product) {
            $product->setVisible(false);
        }

        // set shops
        $shops = $this->getRepo('Shop\Shop')->findByBuilding($building);

        if (empty($shops)) {
            return;
        }

        foreach ($shops as $shop) {
            if (is_null($shop)) {
                return;
            }

            // set shop & shop products offline
            if (!$shop->isOnline()) {
                $shop->setClose(true);

                // set shop products offline
                $this->getRepo('Shop\ShopProduct')->setShopProductsOfflineByShopId(
                    $shop->getId()
                );
            }
        }
    }
}
