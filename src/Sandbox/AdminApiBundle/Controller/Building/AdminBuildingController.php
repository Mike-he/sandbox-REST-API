<?php

namespace Sandbox\AdminApiBundle\Controller\Building;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Room\RoomAttachment;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Sandbox\ApiBundle\Entity\Room\RoomFloor;
use Sandbox\ApiBundle\Form\Room\RoomAttachmentPostType;
use Sandbox\ApiBundle\Form\Room\RoomBuildingPostType;
use Sandbox\ApiBundle\Form\Room\RoomBuildingPutType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Form\Form;

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
class AdminBuildingController extends SandboxRestController
{
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
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $cityId = $paramFetcher->get('city');
        $query = $paramFetcher->get('query');

        $buildings = $this->getRepo('Room\RoomBuilding')->getRoomBuildings(
            $cityId,
            $query
        );
        foreach ($buildings as $building) {
            $floors = $this->getRepo('Room\RoomFloor')->findByBuilding($building);
            $building->setFloors($floors);
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
        $building = $this->getRepo('Room\RoomBuilding')->find($id);
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        // set floor numbers
        $floors = $this->getRepo('Room\RoomFloor')->findByBuilding($building);
        $building->setFloors($floors);

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
        $this->checkAdminBuildingPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $building = new RoomBuilding();

        $form = $this->createForm(new RoomBuildingPostType(), $building);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleAdminBuildingPost(
            $building
        );
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
        $this->checkAdminBuildingPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $building = $this->getRepo('Room\RoomBuilding')->find($id);
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
        return $this->handleAdminBuildingPut(
            $building
        );
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
        $em = $this->getDoctrine()->getManager();
        $roomAttachments = $building->getRoomAttachments();
        $floors = $building->getFloors();

        // check city
        $roomCity = $this->getRepo('Room\RoomCity')->find($building->getCityId());
        if (is_null($roomCity)) {
            throw new BadRequestHttpException(self::NOT_FOUND_MESSAGE);
        }

        // add room building
        $this->addAdminBuilding(
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

        // add floors
        $this->addFloors(
            $building,
            $floors,
            $em
        );

        $em->flush();

        $response = array(
            'id' => $building->getId(),
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
            $building,
            $roomAttachments,
            $em
        );

        // modify floors
        $this->modifyFloors(
            $building,
            $floors,
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

        $building->setCity($roomCity);
        $building->setModificationDate($now);

        $em->flush();
    }

    /**
     * @param RoomBuilding $building
     * @param array        $roomAttachments
     * @param              $em
     */
    private function removeRoomAttachments(
        $building,
        $roomAttachments,
        $em
    ) {
        // check room attachments
        if (!isset($roomAttachments['remove'])) {
            return;
        }

        $attachments = $roomAttachments['remove'];

        // check attachments is null
        if (is_null($attachments)) {
            return;
        }

        foreach ($attachments as $attachment) {
            $attachment = $this->getRepo('Room\RoomAttachment')->find($attachment['id']);
            $em->remove($attachment);
        }
    }

    /**
     * Modify floor numbers.
     *
     * @param RoomBuilding $building
     * @param array        $floors
     * @param              $em
     */
    private function modifyFloors(
        $building,
        $floors,
        $em
    ) {
        if (!isset($floors['modify']) || empty($floors['modify'])) {
            return;
        }

        foreach ($floors['modify'] as $floor) {
            $roomFloor = $this->getRepo('Room\RoomFloor')->find($floor['id']);
            $roomFloor->setFloorNumber($floor['floor_number']);
        }

        // add floor number
        $this->addFloors(
            $building,
            $floors,
            $em
        );
    }

    /**
     * Add room building.
     *
     * @param RoomBuilding $building
     * @param RoomCity     $roomCity
     * @param              $em
     */
    private function addAdminBuilding(
        $building,
        $roomCity,
        $em
    ) {
        $now = new \DateTime('now');

        $building->setCity($roomCity);
        $building->setCreationDate($now);
        $building->setModificationDate($now);

        $em->persist($building);
    }

    /**
     * Add room attachments.
     *
     * @param RoomBuilding $building
     * @param array        $roomAttachments
     * @param              $em
     */
    private function addRoomAttachments(
        $building,
        $roomAttachments,
        $em
    ) {
        // check room attachments
        if (!isset($roomAttachments['add'])) {
            return;
        }

        $attachments = $roomAttachments['add'];

        // check if attachments null
        if (is_null($attachments)) {
            return;
        }

        foreach ($attachments as $attachment) {
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
     * @param RoomBuilding $building
     * @param array        $floors
     * @param              $em
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
            AdminPermission::KEY_PLATFORM_BUILDING,
            $opLevel
        );
    }
}
