<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Advertising;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Advertising\AdvertisingController;
use Sandbox\ApiBundle\Entity\Advertising\CommnueAdvertisingScreen;
use Sandbox\ApiBundle\Entity\Advertising\CommnueScreenAttachment;
use Sandbox\ApiBundle\Entity\Material\CommnueMaterial;
use Sandbox\ApiBundle\Form\Advertising\CommnueAdvertisingScreenPatchType;
use Sandbox\ApiBundle\Form\Advertising\CommnueAdvertisingScreenType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Rs\Json\Patch;

class AdminAdvertisingScreenController extends AdvertisingController
{
    /**
     * Get Commnue Advertising Screen List
     *
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many screens to return per page"
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
     * @Route("/commercial/screens")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdvertisingScreensAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    )
    {
        // check user permission
        $this->checkAdminScreenPermission(AdminPermission::OP_LEVEL_VIEW);

        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');

        $screens = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingScreen')
            ->findAll();

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $screens,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get Commnue Advertising Screen By Id
     *
     * @param $id
     *
     * @Route("/commercial/screens/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getScreenByIdAction(
        $id
    ) {
        // check user permission
        $this->checkAdminScreenPermission(AdminPermission::OP_LEVEL_VIEW);

        $screen = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingScreen')
            ->find($id);

        $this->throwNotFoundIfNull($screen,self::NOT_FOUND_MESSAGE);

        return new View($screen);
    }

    /**
     * Create Commnue Advertising Screen
     *
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/commercial/screens")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postScreenAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    )
    {
        // check user permission
        $this->checkAdminScreenPermission(AdminPermission::OP_LEVEL_EDIT);

        $screen = new CommnueAdvertisingScreen();
        $form = $this->createForm( new CommnueAdvertisingScreenType(),$screen);
        $form->handleRequest($request);

        if(!$form->isValid()){
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleScreenPost(
            $screen
        );
    }

    /**
     * Update Commnue Advertising Screen.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/commercial/screens/{id}")
     * @Method({"PUT"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putScreenAction(
        Request $request,
        $id
    ) {
        // check user permission
        //$this->checkAdminScreenPermission(AdminPermission::OP_LEVEL_EDIT);

        // get screen
        $screen = $this->getRepo('Advertising\CommnueAdvertisingScreen')->find($id);
        $this->throwNotFoundIfNull($screen, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new CommnueAdvertisingScreenType(),
            $screen,
            array(
                'method' => 'PUT',
            ));
        $form->handleRequest($request);

        if(!$form->isValid()){
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleScreenPut(
            $screen
        );
    }

    /**
     * Patch Commnue Advertising Screen.
     *
     * @param $id
     *
     * @Route("/commercial/screens/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchScreenAction(
        Request $request,
        $id
    ) {
        // check user permission
        //$this->checkAdminScreenPermission(AdminPermission::OP_LEVEL_EDIT);

        $screen = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingScreen')
            ->find($id);

        $this->throwNotFoundIfNull($screen, self::NOT_FOUND_MESSAGE);

        $screenJson = $this->container->get('serializer')->serialize($screen, 'json');

        $patch = new Patch($screenJson, $request->getContent());
        $screenJson = $patch->apply();

        $form = $this->createForm(new CommnueAdvertisingScreenPatchType(), $screen);
        $form->submit(json_decode($screenJson, true));

        if ($screen->getVisible()) {
            $screen->setIsSaved(false);

            $this->handleUnvisible();
        } else {
            $screen->setIsSaved(true);

            $this->handleVisibleDefault();
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Delete Commnue Advertising Screen.
     *
     * @param $id
     *
     * @Route("/commercial/screens/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteScreenAction(
        $id
    ) {
        // check user permission
        $this->checkAdminScreenPermission(AdminPermission::OP_LEVEL_EDIT);
        $em = $this->getDoctrine()->getManager();

        $screen = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingScreen')
            ->find($id);

        $this->throwNotFoundIfNull($screen, self::NOT_FOUND_MESSAGE);

        if ($screen->getIsDefault() == false) {
            $attachments = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Advertising\CommnueScreenAttachment')
                ->findByScreen($screen);

            foreach ($attachments as $attachment) {
                $em->remove($attachment);
            }

            $em->remove($screen);
            $em->flush();
        }

        return new View();
    }

    /**
     * @param CommnueAdvertisingScreen $screen
     *
     * @return View
     */
    private function handleScreenPost(
        $screen
    ) {
        $em = $this->getDoctrine()->getManager();
        $attachments = $screen->getAttachments();
        $visible = $screen->getVisible();

        if ($visible == true) {
            $screen->setVisible(true);
            $screen->setIsSaved(false);

            $this->handleUnvisible();
        } else {
            $screen->setVisible(false);
            $screen->setIsSaved(true);
        }

        $em->persist($screen);

        $this->addScreenAttachments(
            $screen,
            $attachments
        );

        $em->flush();

        $response = array(
            'id' => $screen->getId(),
        );

        return new View($response);
    }

    /**
     * @param $screen
     * @param $attachments
     */
    private function addScreenAttachments(
        $screen,
        $attachments
    ) {
        $em = $this->getDoctrine()->getManager();

        if (!is_null($attachments) && !empty($attachments)) {
            foreach ($attachments as $attachment) {
                $screenAttachment = new CommnueScreenAttachment();
                $screenAttachment->setScreen($screen);
                $screenAttachment->setContent($attachment['content']);
                $screenAttachment->setAttachmentType($attachment['attachment_type']);
                $screenAttachment->setFilename($attachment['filename']);
                $screenAttachment->setPreview($attachment['preview']);
                $screenAttachment->setSize($attachment['size']);
                $screenAttachment->setHeight($attachment['height']);
                $screenAttachment->setWidth($attachment['width']);
                $em->persist($screenAttachment);
            }
        }
    }

    /**
     * @param CommnueAdvertisingScreen $screen
     *
     * @return View
     */
    private function handleScreenPut(
        $screen
    ) {
        $em = $this->getDoctrine()->getManager();
        $attachments = $screen->getAttachments();
        $visible = $screen->getVisible();

        if ($visible == true) {
            $screen->setVisible(true);
            $screen->setIsSaved(false);

            $this->handleUnvisible();
        } else {
            $screen->setVisible(false);
            $screen->setIsSaved(true);
        }

        $this->modifyScreenAttachments(
            $screen,
            $attachments
        );

        $em->flush();

        $response = array(
            'id' => $screen->getId(),
        );

        return new View($response);
    }

    /**
     * Unvisible.
     */
    private function handleUnvisible()
    {
        $em = $this->getDoctrine()->getManager();
        $advertising = $em->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingScreen')->findOneBy(array('visible' => true));
        if ($advertising) {
            $advertising->setVisible(false);
            $advertising->setIsSaved(true);
        }
    }

    private function handleVisibleDefault()
    {
        $em = $this->getDoctrine()->getManager();
        $advertising = $em->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingScreen')->findOneBy(array('isDefault' => true));
        if ($advertising) {
            $advertising->setVisible(true);
            $advertising->setIsSaved(false);
        }
    }

    /**
     * @param $screen
     * @param $attachments
     */
    private function modifyScreenAttachments(
        $screen,
        $attachments
    ) {
        $em = $this->getDoctrine()->getManager();

        $attach = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueScreenAttachment')
            ->findByScreen($screen);
        foreach ($attach as $att) {
            $em->remove($att);
        }

        $this->addScreenAttachments(
            $screen,
            $attachments
        );
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminScreenPermission($opLevel)
    {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_COMMNUE_PLATFORM_ADVERTISEMENT],
            ],
            $opLevel
        );
    }
}