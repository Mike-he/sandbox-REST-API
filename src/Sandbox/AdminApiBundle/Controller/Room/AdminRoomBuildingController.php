<?php

namespace Sandbox\AdminApiBundle\Controller\Room;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Room\RoomAttachment;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Sandbox\ApiBundle\Form\Room\RoomAttachmentPostType;
use Sandbox\ApiBundle\Form\Room\RoomBuildingPostType;
use Sandbox\ApiBundle\Form\Room\RoomBuildingPutType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Form\Form;

/**
 * Class AdminRoomBuildingController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminRoomBuildingController extends SandboxRestController
{
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
     * @Route("/building")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postRoomBuildingAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminRoomBuildingPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $building = new RoomBuilding();

        $form = $this->createForm(new RoomBuildingPostType(), $building);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleRoomBuildingPost(
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
     * @Route("/building/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putRoomBuildingAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminRoomBuildingPermission(AdminPermissionMap::OP_LEVEL_EDIT);

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
        return $this->handleRoomBuildingPut(
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
    private function handleRoomBuildingPost(
        $building
    ) {
        $roomAttachments = $building->getRoomAttachments();

        // check city
        $roomCity = $this->getRepo('Room\RoomCity')->find($building->getCityId());
        if (is_null($roomCity)) {
            throw new BadRequestHttpException(self::NOT_FOUND_MESSAGE);
        }

        // add room building
        $this->addRoomBuilding(
            $building,
            $roomCity
        );

        // add room attachments
        $this->addRoomAttachments(
            $building,
            $roomAttachments
        );

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
    private function handleRoomBuildingPut(
        $building
    ) {
        $roomAttachments = $building->getRoomAttachments();

        // check city
        $roomCity = $this->getRepo('Room\RoomCity')->find($building->getCityId());
        if (is_null($roomCity)) {
            throw new BadRequestHttpException(self::NOT_FOUND_MESSAGE);
        }

        // modify room building
        $this->modifyRoomBuilding(
            $building,
            $roomCity
        );

        // add room attachments
        $this->addRoomAttachments(
            $building,
            $roomAttachments
        );

        // remove room attachments
        $this->removeRoomAttachments(
            $building,
            $roomAttachments
        );
    }

    /**
     * Modify room building.
     *
     * @param RoomBuilding $building
     * @param RoomCity     $roomCity
     */
    private function modifyRoomBuilding(
        $building,
        $roomCity
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        $building->setCity($roomCity);
        $building->setModificationDate($now);

        $em->flush();
    }

    /**
     * @param RoomBuilding $building
     * @param array        $roomAttachments
     */
    private function removeRoomAttachments(
        $building,
        $roomAttachments
    ) {
        $em = $this->getDoctrine()->getManager();

        $attachments = $roomAttachments['remove'];
        if (!is_null($attachments) && !empty($attachments)) {
            foreach ($attachments as $attachment) {
                $attachment = $this->getRepo('Room\RoomAttachment')->find($attachment['id']);
                $em->remove($attachment);
            }
            $em->flush();
        }
    }

    /**
     * Add room building.
     *
     * @param RoomBuilding $building
     * @param RoomCity     $roomCity
     */
    private function addRoomBuilding(
        $building,
        $roomCity
    ) {
        $em = $this->getDoctrine()->getManager();

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
     */
    private function addRoomAttachments(
        $building,
        $roomAttachments
    ) {
        $em = $this->getDoctrine()->getManager();

        $attachments = $roomAttachments['add'];
        if (!is_null($attachments) && !empty($attachments)) {
            foreach ($attachments as $attachment) {
                $roomAttachment = new RoomAttachment();
                $form = $this->createForm(new RoomAttachmentPostType(), $roomAttachment);
                $form->submit($attachment, true);

                $roomAttachment->setBuilding($building);
                $roomAttachment->setCreationDate(new \DateTime('now'));
                $em->persist($roomAttachment);
            }
            $em->flush();
        }
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminRoomBuildingPermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_ROOM,
            $opLevel
        );
    }
}
