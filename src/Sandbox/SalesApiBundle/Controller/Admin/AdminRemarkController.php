<?php

namespace Sandbox\SalesApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminRemark;
use Sandbox\ApiBundle\Form\Admin\AdminRemarkType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * AdminRemark controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminRemarkController extends SandboxRestController
{
    /**
     * List admin remarks.
     *
     * @param Request $request the request object
     * @param $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="object",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    description="object name"
     * )
     *
     * @Annotations\QueryParam(
     *    name="object_id",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    description="object id"
     * )
     *
     * @Method({"GET"})
     * @Route("/remarks")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getSalesAdminRemarksAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $companyId = $adminPlatform['sales_company_id'];

        $object = $paramFetcher->get('object');
        $objectId = $paramFetcher->get('object_id');

        // get keyArray
        $keyArray = $this->getPermissionKeyArray($object);

        // check permission
        $this->checkAdminRemarkPermission(
            $keyArray,
            AdminPermission::OP_LEVEL_VIEW
        );

        $remarks = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminRemark')
            ->findBy(
                [
                    'object' => $object,
                    'objectId' => $objectId,
                    'platform' => $platform,
                    'companyId' => $companyId,
                ],
                ['creationDate' => 'ASC']
            );

        return new View($remarks);
    }

    /**
     * create admin remarks.
     *
     * @param Request $request the request object
     *
     * @Method({"POST"})
     * @Route("/remarks")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function createAdminRemarkAction(
        Request $request
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $companyId = $adminPlatform['sales_company_id'];

        $adminId = $this->getAdminId();
        $profile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserProfile')
            ->findOneBy(['userId' => $adminId]);
        if (is_null($profile)) {
            $this->throwNotFoundIfNull($profile);
        }

        $remark = new AdminRemark();
        $form = $this->createForm(new AdminRemarkType(), $remark);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // get keyArray
        $keyArray = $this->getPermissionKeyArray($remark->getObject());

        // check permission
        $this->checkAdminRemarkPermission(
            $keyArray,
            AdminPermission::OP_LEVEL_EDIT
        );

        $remark->setUserId($adminId);
        $remark->setPlatform($platform);
        $remark->setUsername($profile->getName());
        $remark->setCompanyId($companyId);

        $em = $this->getDoctrine()->getManager();
        $em->persist($remark);
        $em->flush();

        $view = new View(['id' => $remark->getId()]);
        $view->setStatusCode(201);

        return $view;
    }

    /**
     * @param $object
     *
     * @return array
     */
    private function getPermissionKeyArray(
        $object
    ) {
        switch ($object) {
            case AdminRemark::OBJECT_PRODUCT_ORDER:
                $keyArray = [
                    ['key' => AdminPermission::KEY_SALES_BUILDING_ORDER],
                    ['key' => AdminPermission::KEY_SALES_PLATFORM_INVOICE],
                    ['key' => AdminPermission::KEY_SALES_BUILDING_USER],
                    ['key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER],
                    ['key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE],
                    ['key' => AdminPermission::KEY_SALES_BUILDING_SPACE],
                ];

                break;
            case AdminRemark::OBJECT_LEASE_BILL:
                $keyArray = [
                    ['key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE],
                    ['key' => AdminPermission::KEY_SALES_PLATFORM_AUDIT],
                ];

                break;
            default: throw new NotFoundHttpException(self::NOT_FOUND_MESSAGE);
        }

        return $keyArray;
    }

    /**
     * @param $keyArray
     * @param $opLevel
     */
    private function checkAdminRemarkPermission(
        $keyArray,
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            $keyArray,
            $opLevel
        );
    }
}
