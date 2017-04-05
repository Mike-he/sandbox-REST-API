<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\AdminApiBundle\Data\Position\PositionUserBindingChange;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPositionUserBinding;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Form\Admin\AdminPositionUserBindingChangePostType;
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

        $positionBindingChange = new PositionUserBindingChange();
        $form = $this->createForm(new AdminPositionUserBindingChangePostType(), $positionBindingChange);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $addPositionBindings = $positionBindingChange->getAdd();
        $deletePositionBindings = $positionBindingChange->getDelete();

        $this->handleAddPositionUserBindings(
            $addPositionBindings
        );

        $this->handleDeletePositionUserBindings(
            $deletePositionBindings
        );

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

        $error = $this->checkSuperAdminPositionValidToDelete($positionUserBindings);
        if (!is_null($error)) {
            return $error;
        }

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

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $bindings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getBindingsByUser(
                $userId,
                $platform,
                $salesCompanyId
            );

        $error = $this->checkSuperAdminPositionValidToDelete($bindings);
        if (!is_null($error)) {
            return $error;
        }

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="user_id",
     *     nullable=false,
     *     requirements="\d+",
     *     strict=true
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
     * @Route("/position/bindings/from_community")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deletePositionUserBindingFromCommunityAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminPositionBindingPermission(AdminPermission::OP_LEVEL_EDIT);

        $userId = $paramFetcher->get('user_id');
        $buildingId = $paramFetcher->get('building_id');
        $shopId = $paramFetcher->get('shop_id');

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $salesCompanyId = $adminPlatform['sales_company_id'];

        if ($platform == AdminPermission::PERMISSION_PLATFORM_SALES) {
            $this->throwNotFoundIfNull($buildingId, self::BAD_PARAM_MESSAGE);
        } elseif ($platform == AdminPermission::PERMISSION_PLATFORM_SHOP) {
            $this->throwNotFoundIfNull($shopId, self::BAD_PARAM_MESSAGE);
        } else {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $bindings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getBindingsByCommunity(
                $userId,
                $platform,
                $salesCompanyId,
                $buildingId,
                $shopId
            );

        $error = $this->checkSuperAdminPositionValidToDelete($bindings);
        if (!is_null($error)) {
            return $error;
        }

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="user_id",
     *     nullable=false,
     *     requirements="\d+",
     *     strict=true
     * )
     *
     * @Route("/position/bindings/from_global")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deletePositionUserBindingFromGlobalPositionAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $paramFetcher->get('user_id');

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $bindings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getBindingsBySpecifyAdminId(
                $userId,
                AdminPermission::PERMISSION_LEVEL_GLOBAL,
                $platform,
                $salesCompanyId
            );

        $bindingsArray = array();
        foreach ($bindings as $binding) {
            if (!isset($binding['id'])) {
                continue;
            }

            $positionBinding = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->find($binding['id']);

            array_push($bindingsArray, $positionBinding);
        }

        $error = $this->checkSuperAdminPositionValidToDelete($bindingsArray);
        if (!is_null($error)) {
            return $error;
        }

        return new View();
    }

    /**
     * @param $bindings
     *
     * @return View
     */
    private function checkSuperAdminPositionValidToDelete(
        $bindings
    ) {
        $em = $this->getDoctrine()->getManager();
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
     * @param array $addPositionBindings
     *
     * @return array|View
     */
    private function handleAddPositionUserBindings(
        $addPositionBindings
    ) {
        $em = $this->getDoctrine()->getManager();
        $userIds = array();

        foreach ($addPositionBindings as $data) {
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
                continue;
            }

            $userId = $positionUserBinding->getUserId();

            // have exist position binding
            $binding = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->findOneBy(array(
                    'userId' => $userId,
                    'positionId' => $positionUserBinding->getPositionId(),
                    'buildingId' => $positionUserBinding->getBuildingId(),
                    'shopId' => $positionUserBinding->getShopId(),
                ));

            if (!is_null($binding)) {
                continue;
            }

            array_push($userIds, $userId);
            $userIds = array_unique($userIds);

            $em->persist($positionUserBinding);
            $em->flush();
        }

        // save log
        foreach ($userIds as $userId) {
            $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();

            $bindings = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->getBindingsBySpecifyAdminId(
                    $userId,
                    null,
                    $adminPlatform['platform'],
                    $adminPlatform['sales_company_id']
                );
            if (!is_null($bindings)) {
                $action = Log::ACTION_EDIT;
            } else {
                $action = Log::ACTION_CREATE;
            }

            $this->generateAdminLogs(array(
                'logModule' => Log::MODULE_ADMIN,
                'logAction' => $action,
                'logObjectKey' => Log::OBJECT_ADMIN,
                'logObjectId' => $userId,
            ));
        }

        return;
    }

    /**
     * @param array $deletePositionBindings
     *
     * @return array
     */
    private function handleDeletePositionUserBindings(
        $deletePositionBindings
    ) {
        $em = $this->getDoctrine()->getManager();
        $bindingIds = isset($deletePositionBindings['position_binding_ids']) ? $deletePositionBindings['position_binding_ids'] : null;

        if (is_null($bindingIds) || empty($bindingIds)) {
            return;
        }

        foreach ($bindingIds as $bindingId) {
            $binding = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->find($bindingId);

            if (is_null($binding)) {
                continue;
            }

            $em->remove($binding);
        }

        $em->flush();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminPositionBindingPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
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
