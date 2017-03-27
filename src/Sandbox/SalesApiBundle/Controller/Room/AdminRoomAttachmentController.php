<?php

namespace Sandbox\SalesApiBundle\Controller\Room;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Room\RoomAttachment;
use Sandbox\ApiBundle\Form\Room\RoomAttachmentType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Room\Room;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;

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
class AdminRoomAttachmentController extends SalesRestController
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
        $attachment = new RoomAttachment();

        $form = $this->createForm(new RoomAttachmentType(), $attachment);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $roomBuilding = $this->getRepo('Room\RoomBuilding')->find($attachment->getBuildingId());
        $this->throwNotFoundIfNull($roomBuilding, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                    'building_id' => $roomBuilding->getId(),
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

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
        // get attachment
        $attachment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomAttachment')
            ->find($id);

        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                    'building_id' => $attachment->getBuildingId(),
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        $em = $this->getDoctrine()->getManager();
        $em->remove($attachment);
        $em->flush();
    }
}
