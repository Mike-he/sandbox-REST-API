<?php

namespace Sandbox\AdminApiBundle\Controller\Room;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Room\RoomAttachmentController;
use Sandbox\ApiBundle\Entity\Room\RoomAttachment;
use Sandbox\ApiBundle\Form\Room\RoomAttachmentType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Room\Room;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;

/**
 * Admin Room attachment controller.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminRoomAttachmentController extends RoomAttachmentController
{
    /**
     * Get attachments.
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
     * @Annotations\QueryParam(
     *    name="type",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by room type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Route("/rooms/attachments")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getAttachmentsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminRoomAttachmentPermission(AdminPermission::OP_LEVEL_VIEW);

        $types = $paramFetcher->get('type');
        $buildingId = $paramFetcher->get('building');

        // get attachment
        $attachments = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomAttachment')
            ->getAttachmentsByTypes(
                $types,
                $buildingId
            );

        return new View($attachments);
    }

    /**
     * Get an attachment.
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
     * @Route("/rooms/attachments/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getAttachmentAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminRoomAttachmentPermission(AdminPermission::OP_LEVEL_VIEW);

        // get attachment
        $attachments = $this->getRepo('Room\RoomAttachment')->find($id);

        return new View($attachments);
    }

    /**
     * Add an attachment.
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
     * @Route("/rooms/attachments")
     * @Method({"POST"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function postAttachmentAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminRoomAttachmentPermission(AdminPermission::OP_LEVEL_EDIT);

        $attachment = new RoomAttachment();

        $form = $this->createForm(new RoomAttachmentType(), $attachment);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $roomBuilding = $this->getRepo('Room\RoomBuilding')->find($attachment->getBuildingId());
        $this->throwNotFoundIfNull($roomBuilding, self::NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();
        $em->persist($attachment);
        $em->flush();

        $response = array(
            'id' => $attachment->getId(),
        );

        return new View($response);
    }

    /**
     * Delete an attachment.
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
     * @Route("/rooms/attachments/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteAttachmentAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminRoomAttachmentPermission(AdminPermission::OP_LEVEL_EDIT);

        // get attachment
        $attachment = $this->getRepo('Room\RoomAttachment')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($attachment);
        $em->flush();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminRoomAttachmentPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ROOM],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SALES],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SPACE],
            ],
            $opLevel
        );
    }
}
