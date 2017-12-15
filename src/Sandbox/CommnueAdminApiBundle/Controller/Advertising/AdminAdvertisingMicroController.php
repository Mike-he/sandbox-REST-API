<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Advertising;

use Sandbox\ApiBundle\Controller\Advertising\AdvertisingController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\Advertising\CommnueAdvertisingMicro;
use Sandbox\ApiBundle\Form\Advertising\AdvertisingPositionType;
use Sandbox\ApiBundle\Form\Advertising\CommnueAdvertisingMicroType;
use Sandbox\CommnueAdminApiBundle\Data\Advertising\AdvertisingPosition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Rs\Json\Patch;

class AdminAdvertisingMicroController extends AdvertisingController
{
    /**
     * Get Advertising Micro List
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
     *    description="How many micros to return per page"
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
     * @Annotations\QueryParam(
     *    name="search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Route("/commercial/micros")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdvertisingMicrosAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminAdvertisementPermission(AdminPermission::OP_LEVEL_VIEW);

        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');
        $search = $paramFetcher->get('search');

        $mictos = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingMicro')
            ->getMicroList($search);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $mictos,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get Advertising Micro By Id
     *
     * @param $id
     *
     * @Route("/commercial/micros/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdvertisingMicroByIdAction(
        $id
    ) {
        // check user permission
        $this->checkAdminAdvertisementPermission(AdminPermission::OP_LEVEL_VIEW);

        $micro = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingMicro')
            ->find($id);

        $this->throwNotFoundIfNull($micro,self::NOT_FOUND_MESSAGE);

        return new View($micro);
    }

    /**
     * Create Advertising Micro
     *
     * @param Request $request
     *
     * @Route("/commercial/micros")
     * @Method({"POST"})
     *
     * @return View
     * @throws \Exception
     */
    public function postAdvertisingMicroAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminAdvertisementPermission(AdminPermission::OP_LEVEL_EDIT);

        $micro = new CommnueAdvertisingMicro();
        $form = $this->createForm(new CommnueAdvertisingMicroType(), $micro);
        $form->handleRequest($request);

        if(!$form->isValid()){
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($micro);
        $em->flush();

        return new View(array(
            'id'=>$micro->getId()
        ));
    }

    /**
     * Update Advertising Micro
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/commercial/micros/{id}")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putAdvertisingMicroAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminAdvertisementPermission(AdminPermission::OP_LEVEL_EDIT);

        $micro = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingMicro')
            ->find($id);

        $this->throwNotFoundIfNull($micro, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new CommnueAdvertisingMicroType(),
            $micro,
            array(
                'method'=>'put'
            )
        );
        $form->handleRequest($request);
        if(!$form->isValid()){
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Delete Advertising Micro
     *
     * @param $id
     *
     * @Route("/commercial/micros/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteAdvertisingMicroAction(
        $id
    ) {
        // check user permission
        $this->checkAdminAdvertisementPermission(AdminPermission::OP_LEVEL_EDIT);

        $micro = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingMicro')
            ->find($id);

        $this->throwNotFoundIfNull($micro, self::NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();
        $em->remove($micro);

        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/commercial/micros/{id}/position")
     * @Method({"POST"})
     *
     * @return View
     */
    public function changeMicroPositionAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminAdvertisementPermission(AdminPermission::OP_LEVEL_EDIT);

        $micro = $this->getRepo('Advertising\CommnueAdvertisingMicro')->find($id);
        $this->throwNotFoundIfNull($micro, self::NOT_FOUND_MESSAGE);
        $position = new AdvertisingPosition();
        $form = $this->createForm(new AdvertisingPositionType(), $position);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->updateAdvertisingMicroPosition(
            $micro,
            $position
        );
    }

    /**
     * @param CommnueAdvertisingMicro         $micro
     * @param AdvertisingPosition $position
     *
     * @return View
     */
    private function updateAdvertisingMicroPosition(
        $micro,
        $position
    ) {
        $action = $position->getAction();

        if ($action == AdvertisingPosition::ACTION_TOP) {
            $micro->setSortTime(round(microtime(true) * 1000));
        } elseif (
            $action == AdvertisingPosition::ACTION_UP ||
            $action == AdvertisingPosition::ACTION_DOWN
        ) {
            $this->swapAdvertisingMicroPosition(
                $micro,
                $action
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param CommnueAdvertisingMicro         $micro
     * @param string $action
     */
    private function swapAdvertisingMicroPosition(
        $micro,
        $action
    ) {
        $sortTime = $micro->getSortTime();
        $swapMicro = $this->getRepo('Advertising\CommnueAdvertisingMicro')->findSwapMicro(
            $sortTime,
            $action
        );

        if (!is_null($swapMicro)) {
            $swapSortTime = $swapMicro->getSortTime();
            $micro->setSortTime($swapSortTime);
            $swapMicro->setSortTime($sortTime);
        }
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminAdvertisementPermission($opLevel)
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