<?php

namespace Sandbox\AdminApiBundle\Controller\Advertising;

use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\Advertising\AdvertisingController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Advertising\Advertising;
use Sandbox\ApiBundle\Entity\Advertising\AdvertisingAttachment;
use Sandbox\ApiBundle\Form\Advertising\AdvertisingPostType;
use Sandbox\ApiBundle\Form\Advertising\AdvertisingPutType;
use Sandbox\ApiBundle\Form\Advertising\AdvertisingPatchType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;

/**
 * Admin Advertising Controller.
 *
 * @category Sandbox
 *
 * @author   Feng li <feng.li@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminAdvertisingController extends AdvertisingController
{
    /**
     * Get Advertising List.
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
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many banners to return per page"
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
     *
     * @Route("/commercial")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdvertisingListAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminAdvertisingPermission(AdminPermission::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $query = $this->getDoctrine()->getRepository("SandboxApiBundle:Advertising\Advertising")->getAdvertisingList();

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $query,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Post Advertising.
     *
     * @param Request $request
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     201 = "Returned when successful created"
     *  }
     * )
     *
     * @Route("/commercial")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postAdvertisingAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminAdvertisingPermission(AdminPermission::OP_LEVEL_EDIT);

        $advertising = new Advertising();

        $form = $this->createForm(new AdvertisingPostType(), $advertising);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleAdvertisingPost(
            $advertising
        );
    }

    /**
     * Get A Advertising.
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
     * @Route("/commercial/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdvertisingAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminAdvertisingPermission(AdminPermission::OP_LEVEL_VIEW);

        $advertising = $this->getDoctrine()->getRepository('SandboxApiBundle:Advertising\Advertising')->find($id);
        $this->throwNotFoundIfNull($advertising, self::NOT_FOUND_MESSAGE);

        $attachments = $this->getDoctrine()->getRepository('SandboxApiBundle:Advertising\AdvertisingAttachment')->findByAdvertising($advertising);
        $advertising->setAttachments($attachments);

        // set view
        $view = new View($advertising);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('main'))
        );

        return $view;
    }

    /**
     * Update Advertising.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/commercial/{id}")
     * @Method({"PUT"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putAdvertisingAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminAdvertisingPermission(AdminPermission::OP_LEVEL_EDIT);

        // get banner
        $advertising = $this->getDoctrine()->getRepository('SandboxApiBundle:Advertising\Advertising')->find($id);
        $this->throwNotFoundIfNull($advertising, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new AdvertisingPutType(),
            $advertising,
            array(
                'method' => 'PUT',
            )
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleAdvertisingPut(
            $advertising
        );
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "OK"
     *  }
     * )
     *
     * @Route("/commercial/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchAdvertisingAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminAdvertisingPermission(AdminPermission::OP_LEVEL_EDIT);

        $advertising = $this->getDoctrine()->getRepository('SandboxApiBundle:Advertising\Advertising')->find($id);
        $this->throwNotFoundIfNull($advertising, self::NOT_FOUND_MESSAGE);

        $advertisingJson = $this->container->get('serializer')->serialize($advertising, 'json');
        $patch = new Patch($advertisingJson, $request->getContent());
        $advertisingJson = $patch->apply();

        $form = $this->createForm(new AdvertisingPatchType(), $advertising);
        $form->submit(json_decode($advertisingJson, true));

        if ($advertising->getVisible()) {
            $advertising->setIsSaved(false);

            $this->handleUnvisible();
        } else {
            $advertising->setIsSaved(true);

            $this->handleVisibleDefault();
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Delete Advertising.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/commercial/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteAdvertisingAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminAdvertisingPermission(AdminPermission::OP_LEVEL_EDIT);
        $em = $this->getDoctrine()->getManager();

        $advertising = $this->getDoctrine()->getRepository('SandboxApiBundle:Advertising\Advertising')->find($id);
        $this->throwNotFoundIfNull($advertising, self::NOT_FOUND_MESSAGE);

        if ($advertising->getIsDefault() == false) {
            $attachments = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Advertising\AdvertisingAttachment')
                ->findByAdvertising($advertising);

            foreach ($attachments as $attachment) {
                $em->remove($attachment);
            }

            $em->remove($advertising);
            $em->flush();
        }

        return new View();
    }

    /**
     * @param $advertising
     *
     * @return View
     */
    private function handleAdvertisingPost(
        $advertising
    ) {
        $em = $this->getDoctrine()->getManager();
        $attachments = $advertising->getAttachments();
        $visible = $advertising->getVisible();

        if ($visible == true) {
            $advertising->setVisible(true);
            $advertising->setIsSaved(false);

            $this->handleUnvisible();
        } else {
            $advertising->setVisible(false);
            $advertising->setIsSaved(true);
        }

        $em->persist($advertising);

        $this->addAdvertisingAttachments(
            $advertising,
            $attachments
        );

        $em->flush();

        $response = array(
            'id' => $advertising->getId(),
        );

        return new View($response);
    }

    /**
     * @param $advertising
     *
     * @return View
     */
    private function handleAdvertisingPut(
        $advertising
    ) {
        $em = $this->getDoctrine()->getManager();
        $attachments = $advertising->getAttachments();
        $visible = $advertising->getVisible();

        if ($visible == true) {
            $advertising->setVisible(true);
            $advertising->setIsSaved(false);

            $this->handleUnvisible();
        } else {
            $advertising->setVisible(false);
            $advertising->setIsSaved(true);
        }

        $this->modifyAdvertisingAttachments(
            $advertising,
            $attachments
        );

        $em->flush();

        $response = array(
            'id' => $advertising->getId(),
        );

        return new View($response);
    }

    /**
     * Unvisible.
     */
    private function handleUnvisible()
    {
        $em = $this->getDoctrine()->getManager();
        $advertising = $em->getRepository('SandboxApiBundle:Advertising\Advertising')->findOneBy(array('visible' => true));
        if ($advertising) {
            $advertising->setVisible(false);
            $advertising->setIsSaved(true);
        }
    }

    private function handleVisibleDefault()
    {
        $em = $this->getDoctrine()->getManager();
        $advertising = $em->getRepository('SandboxApiBundle:Advertising\Advertising')->findOneBy(array('isDefault' => true));
        if ($advertising) {
            $advertising->setVisible(true);
            $advertising->setIsSaved(false);
        }
    }

    /**
     * @param $advertising
     * @param $attachments
     */
    private function addAdvertisingAttachments(
        $advertising,
        $attachments
    ) {
        $em = $this->getDoctrine()->getManager();

        if (!is_null($attachments) && !empty($attachments)) {
            foreach ($attachments as $attachment) {
                $advertisingAttachment = new AdvertisingAttachment();
                $advertisingAttachment->setAdvertising($advertising);
                $advertisingAttachment->setContent($attachment['content']);
                $advertisingAttachment->setAttachmentType($attachment['attachment_type']);
                $advertisingAttachment->setFilename($attachment['filename']);
                $advertisingAttachment->setPreview($attachment['preview']);
                $advertisingAttachment->setSize($attachment['size']);
                $advertisingAttachment->setHeight($attachment['height']);
                $advertisingAttachment->setWidth($attachment['width']);
                $em->persist($advertisingAttachment);
            }
        }
    }

    /**
     * @param $advertising
     * @param $attachments
     */
    private function modifyAdvertisingAttachments(
        $advertising,
        $attachments
    ) {
        $em = $this->getDoctrine()->getManager();

        $attach = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\AdvertisingAttachment')
            ->findByAdvertising($advertising);
        foreach ($attach as $att) {
            $em->remove($att);
        }

        $this->addAdvertisingAttachments(
            $advertising,
            $attachments
        );
    }

    /**
     * Check user permission.
     *
     * @param int $OpLevel
     */
    private function checkAdminAdvertisingPermission(
        $OpLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADVERTISING],
            ],
            $OpLevel
        );
    }
}
