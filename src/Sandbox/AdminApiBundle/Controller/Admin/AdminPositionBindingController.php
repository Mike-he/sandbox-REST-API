<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPositionUserBinding;
use Sandbox\ApiBundle\Form\Admin\AdminPositionUserBindingPostType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Controller\Annotations;

class AdminPositionBindingController extends AdminRestController
{
    const ERROR_INVALID_USER_CODE = 400001;
    const ERROR_INVALID_USER_MESSAGE = 'Invalid user id';
    const ERROR_INVALID_POSITION_CODE = 400002;
    const ERROR_INVALID_POSITION_MESSAGE = 'Invalid position id';
    const ERROR_INVALID_BUILDING_CODE = 400003;
    const ERROR_INVALID_BUILDING_MESSAGE = 'Invalid building id';
    const ERROR_INVALID_SHOP_CODE = 400004;
    const ERROR_INVALID_SHOP_MESSAGE = 'Invalid shop id';
    const ERROR_OVER_LIMIT_SUPER_ADMIN_NUMBER_CODE = 400005;
    const ERROR_OVER_LIMIT_SUPER_ADMIN_NUMBER_MESSAGE = 'Over the super administrator limit number';
    const ERROR_NOT_NULL_SUPER_ADMIN_CODE = 400006;
    const ERROR_NOT_NULL_SUPER_ADMIN_MESSAGE = 'Must at least one super administrator position binding';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/position/bindings")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postPositionUserBindingAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminPositionBindingPermission(AdminPermission::OP_LEVEL_EDIT);

        $payloads = json_decode($request->getContent(), true);

        $response = array();
        foreach ($payloads as $data) {
            // bind form
            $positionUserBinding = new AdminPositionUserBinding();
            $form = $this->createForm(new AdminPositionUserBindingPostType(), $positionUserBinding);
            $form->submit($data);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            // check data valid
            $error = $this->checkDataValid($positionUserBinding);
            if (!is_null($error)) {
                return $error;
            }

            $em = $this->getDoctrine()->getManager();

            $em->persist($positionUserBinding);
            $em->flush();

            array_push($response, array(
                'id' => $positionUserBinding->getId(),
            ));
        }

        return new View($response);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="user_id",
     *     nullable=false,
     *     requirements="\d+"
     * )
     *
     * @Annotations\QueryParam(
     *     name="position_id",
     *     nullable=false,
     *     requirements="\d+"
     * )
     *
     * @Annotations\QueryParam(
     *     name="building_id",
     *     nullable=true,
     *     requirements="\d+"
     * )
     *
     * @Annotations\QueryParam(
     *     name="shop_id",
     *     nullable=true,
     *     requirements="\d+"
     * )
     *
     * @Route("/position/bindings/from_module")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deletePositionUserBindingFromModuleAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminPositionBindingPermission(AdminPermission::OP_LEVEL_EDIT);

        $positionUserBindings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->findBy(array(
                'userId' => $paramFetcher->get('user_id'),
                'positionId' => $paramFetcher->get('position_id'),
                'buildingId' => $paramFetcher->get('building_id'),
                'shopId' => $paramFetcher->get('shop_id'),
            ));

        $em = $this->getDoctrine()->getManager();
        foreach ($positionUserBindings as $binding) {
            $position = $binding->getPosition();
            if ($position->getIsSuperAdmin()) {
                $bindings = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->findBy(array(
                        'position' => $position,
                    ));
                if (count($bindings) <= 1) {
                    return $this->customErrorView(
                        400,
                        self::ERROR_NOT_NULL_SUPER_ADMIN_CODE,
                        self::ERROR_NOT_NULL_SUPER_ADMIN_MESSAGE
                    );
                }
            }

            $em->remove($binding);
        }
        $em->flush();

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="user_id",
     *     nullable=false,
     *     requirements="\d+"
     * )
     *
     * @Route("/position/bindings/from_platform")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deletePositionUserBindingFromPlatformAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminPositionBindingPermission(AdminPermission::OP_LEVEL_EDIT);

        $userId = $paramFetcher->get('user_id');

        $cookies = $this->getPlatformSessions();
        $platform = $cookies['platform'];
        $salesCompanyId = $cookies['sales_company_id'];

        $em = $this->getDoctrine()->getManager();

        $bindings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getBindingsByUser(
                $userId,
                $platform,
                $salesCompanyId
            );

        foreach ($bindings as $binding) {
            $position = $binding->getPosition();

            if ($position->getIsSuperAdmin()) {
                $superAdminBindings = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->findBy(array(
                        'position' => $position,
                    ));

                if (count($superAdminBindings) == 1) {
                    return $this->customErrorView(
                        400,
                        self::ERROR_NOT_NULL_SUPER_ADMIN_CODE,
                        self::ERROR_NOT_NULL_SUPER_ADMIN_MESSAGE
                    );
                }
            }

            $em->remove($binding);
        }
        $em->flush();

        return new View();
    }

    /**
     * @param AdminPositionUserBinding $positionUserBinding
     *
     * @return View
     */
    private function checkDataValid(
        $positionUserBinding
    ) {
        // check user validation
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($positionUserBinding->getUserId());

        if (is_null($user)) {
            return $this->customErrorView(
                400,
                self::ERROR_INVALID_USER_CODE,
                self::ERROR_INVALID_USER_MESSAGE
            );
        }
        $positionUserBinding->setUser($user);

        // check position validation
        $position = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->find($positionUserBinding->getPositionId());

        if (is_null($position)) {
            return $this->customErrorView(
                400,
                self::ERROR_INVALID_POSITION_CODE,
                self::ERROR_INVALID_POSITION_MESSAGE
            );
        }

        // check super admin limit number
        if ($position->getIsSuperAdmin()) {
            $bindings = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->findBy(array(
                    'position' => $position,
                ));
            if (count($bindings) >= 2) {
                return $this->customErrorView(
                    400,
                    self::ERROR_OVER_LIMIT_SUPER_ADMIN_NUMBER_CODE,
                    self::ERROR_OVER_LIMIT_SUPER_ADMIN_NUMBER_MESSAGE
                );
            }
        }
        $positionUserBinding->setPosition($position);

        // check building validation
        $buildingId = $positionUserBinding->getBuildingId();
        if (!is_null($buildingId)) {
            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($buildingId);

            if (is_null($building)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_INVALID_BUILDING_CODE,
                    self::ERROR_INVALID_BUILDING_MESSAGE
                );
            }

            $positionUserBinding->setBuilding($building);
        }

        // check shop validation
        $shopId = $positionUserBinding->getShopId();
        if (!is_null($shopId)) {
            $shop = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Shop\Shop')
                ->find($shopId);

            if (is_null($shop)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_INVALID_SHOP_CODE,
                    self::ERROR_INVALID_SHOP_MESSAGE
                );
            }

            $positionUserBinding->setShop($shop);
        }
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminPositionBindingPermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADMIN],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_ADMIN],
                ['key' => AdminPermission::KEY_SHOP_PLATFORM_ADMIN],
            ],
            $opLevel
        );
    }
}
