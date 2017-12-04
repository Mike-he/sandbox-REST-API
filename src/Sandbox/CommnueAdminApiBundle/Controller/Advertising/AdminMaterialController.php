<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Advertising;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Material\CommnueMaterial;
use Sandbox\ApiBundle\Form\Material\CommnueMaterialType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;


class AdminMaterialController extends SandboxRestController
{
    /**
     * Get Material list
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
     *    description="How many materials to return per page"
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
     *    name="category",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="material category "
     * )
     *
     * @Route("/advertising/materials")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMaterialsListAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminMaterialPermission(AdminPermission::OP_LEVEL_VIEW);

        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');
        $category = $paramFetcher->get('category');

        $materials = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Material\CommnueMaterial')
            ->getMaterials($category);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $materials,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get Material By Id
     *
     * @param $id
     *
     * @Route("/advertising/materials/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMaterialByIdAction(
        $id
    ) {
        // check user permission
        $this->checkAdminMaterialPermission(AdminPermission::OP_LEVEL_VIEW);

        $material = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Material\CommnueMaterial')
            ->find($id);

        $this->throwNotFoundIfNull($material, self::NOT_FOUND_MESSAGE);

        return new View($material);
    }

    /**
     * Create Material
     *
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/advertising/materials")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postMaterialAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminMaterialPermission(AdminPermission::OP_LEVEL_EDIT);

        $material = new CommnueMaterial();

        $form = $this->createForm(new CommnueMaterialType(), $material);
        $form->handleRequest($request);
        if(!$form->isValid()){
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($material);
        $em->flush();

        return new View(array(
            'id'=>$material->getId()
        ));
    }

    /**
     * Update Material
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/advertising/materials/{id}")
     * @Method({"PUT"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putMaterialAction(
        Request $request,
        $id
    ) {
        // check user permission
        //$this->checkAdminMaterialPermission(AdminPermission::OP_LEVEL_EDIT);

        $material = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Material\CommnueMaterial')
            ->find($id);

        $this->throwNotFoundIfNull($material,self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new CommnueMaterialType(),
            $material,
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
     * Delete Material
     *
     * @param $id
     *
     * @Route("/advertising/material/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteMaterialAction(
        $id
    ) {
        // check user permission
        $this->checkAdminMaterialPermission(AdminPermission::OP_LEVEL_EDIT);

        $material = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Material\CommnueMaterial')
            ->find($id);

        $this->throwNotFoundIfNull($material, self::NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();
        $em->remove($material);
        $em->flush();

        return new View();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminMaterialPermission($opLevel)
    {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_COMMNUE_PLATFORM_MATERIAL],
            ],
            $opLevel
        );
    }
}