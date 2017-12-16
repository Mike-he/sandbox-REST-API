<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Advertising;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Advertising\AdvertisingController;
use Sandbox\ApiBundle\Entity\Advertising\CommnueAdvertisingScreen;
use Sandbox\ApiBundle\Entity\Material\CommnueMaterial;
use Sandbox\ApiBundle\Form\Advertising\CommnueAdvertisingScreenType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;

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

        $url = $form['url']->getData();

        return $this->handleScreenPost(
            $screen,
            $url
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
        $this->checkAdminScreenPermission(AdminPermission::OP_LEVEL_EDIT);

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

        $url = $form['url']->getData();

        return $this->handleScreenPost(
            $screen,
            $url
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
        $id
    ) {
        // check user permission
        $this->checkAdminScreenPermission(AdminPermission::OP_LEVEL_EDIT);

        $screen = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingScreen')
            ->find($id);

        $this->throwNotFoundIfNull($screen, self::NOT_FOUND_MESSAGE);

        if($screen->getVisible()){
            $screen->setVisible(false);
        }else{
            $screen->setVisible(true);
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

        $screen = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingScreen')
            ->find($id);

        $this->throwNotFoundIfNull($screen, self::NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();
        $em->remove($screen);
        $em->flush();

        return new View();
    }

    /**
     * @param CommnueAdvertisingScreen $screen
     * @param $url
     * @return View
     */
    private function handleScreenPost(
        $screen,
        $url
    ) {
        $em = $this->getDoctrine()->getManager();

        $source = $screen->getSource();
        $sourceId = $screen->getSourceId();

        switch ($source) {
            case CommnueAdvertisingScreen::SOURCE_URL:
                if (is_null($url) || empty($url)) {
                    return $this->customErrorView(
                        400,
                        self::URL_NULL_CODE,
                        self::URL_NULL_MESSAGE
                    );
                }
                $screen->setContent($url);

                break;
            case CommnueAdvertisingScreen::SOURCE_MATERIAL:
                $this->setScreenContentForMaterial($screen,$sourceId);

                break;
            case CommnueAdvertisingScreen::SOURCE_EVENT:
                $this->setScreenContentForEvent($screen,$sourceId);

                break;
            default:
                return $this->customErrorView(
                    400,
                    self::WRONG_SOURCE_CODE,
                    self::WRONG_SOURCE_MESSAGE
                );

                break;
        }

        // check if screen already exists
        if(is_null($screen->getId())){
                $existScreen = $this->getExistingScreen(
                    $source,
                    $sourceId,
                    $url
                );

                if (!is_null($existScreen)) {
                    return $this->customErrorView(
                        400,
                        self::ADVERTISEMENT_ALREADY_EXIST_CODE,
                        self::ADVERTISEMENT_ALREADY_EXIST_MESSAGE
                    );
                }
            }

        $em->persist($screen);
        $em->flush();

        return new View(array(
            'id' => $screen->getId(),
        ));
    }

    /**
     * set screen content for event.
     *
     * @param CommnueAdvertisingScreen $screen
     * @param int    $sourceId
     */
    private function setScreenContentForEvent(
        $screen,
        $sourceId
    ) {
        $event = $this->getRepo('Event\Event')->find($sourceId);
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        $screen->setContent($event->getName());
    }

    /**
     * set screen content for news.
     *
     * @param CommnueAdvertisingScreen $screen
     * @param int    $sourceId
     */
    private function setScreenContentForMaterial(
        $screen,
        $sourceId
    ) {
        $material = $this->getRepo('Material\CommnueMaterial')->find($sourceId);
        $this->throwNotFoundIfNull($material, self::NOT_FOUND_MESSAGE);

        $screen->setContent($material->getTitle());
    }

    /**
     * @param $source
     * @param $sourceId
     * @param $url
     * @return object
     */
    private function getExistingScreen(
        $source,
        $sourceId,
        $url
    ) {
        if (!is_null($url)) {
            $existScreen = $this->getRepo('Advertising\CommnueAdvertisingScreen')->findOneBy(
                [
                    'source' =>  $source,
                    'content' => $url,
                ]
            );
        } else {
            $existScreen = $this->getRepo('Advertising\CommnueAdvertisingScreen')->findOneBy(
                [
                    'source' => $source,
                    'sourceId' => $sourceId,
                ]
            );
        }

        return $existScreen;
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