<?php

namespace Sandbox\AdminApiBundle\Controller\Room;

use Sandbox\ApiBundle\Controller\Room\RoomAttachmentController;
use Sandbox\ApiBundle\Entity\Room\RoomAttachment;
use Sandbox\ApiBundle\Form\Room\RoomAttachmentType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Room\Room;
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
     * @Route("/rooms/attachments")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getAttachmentsAction(
        Request $request
    ) {
        // get attachment
        $attachments = $this->getRepo('Room\RoomAttachment')->findAll();

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
        $attachment = $this->getRepo('Room\RoomAttachment')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($attachment);
        $em->flush();
    }
}