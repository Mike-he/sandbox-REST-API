<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\AdminApiBundle\Data\Position\Position;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Sandbox\ApiBundle\Entity\Admin\AdminPositionGroupBinding;
use Sandbox\ApiBundle\Entity\Admin\AdminPositionPermissionMap;
use Sandbox\ApiBundle\Form\Admin\AdminPositionPermissionMapType;
use Sandbox\ApiBundle\Form\Admin\AdminPositionPostType;
use Sandbox\ApiBundle\Form\Admin\AdminPositionPutType;
use Sandbox\ApiBundle\Form\Position\PositionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use FOS\RestBundle\Controller\Annotations;

/**
 * Admin Position Controller.
 *
 * @category Sandbox
 *
 * @author  Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminPositionController extends PaymentController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="level"
     * )
     *
     * @Route("positions/{id}/change_position")
     * @Method({"POST"})
     *
     * @return View
     */
    public function changePositionSortAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permissions
        $this->checkAdminPositionPermission(AdminPermission::OP_LEVEL_EDIT);

        $type = $paramFetcher->get('type');

        // get position
        $adminPosition = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findOneBy(array(
                'id' => $id,
                'isSuperAdmin' => false,
            ));
        if (is_null($adminPosition)) {
            return new View();
        }

        $sort = new Position();
        $form = $this->createForm(new PositionType(), $sort);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->updatePositionSort(
            $adminPosition,
            $sort,
            $type
        );
    }

    /**
     * @param AdminPosition $adminPosition
     * @param Position      $sort
     * @param               $type
     *
     * @return View
     */
    private function updatePositionSort(
        $adminPosition,
        $sort,
        $type
    ) {
        // check user permissions
        $this->checkAdminPositionPermission(AdminPermission::OP_LEVEL_EDIT);

        $action = $sort->getAction();

        // change banner position
        if ($action == Position::ACTION_TOP) {
            $adminPosition->setSortTime(round(microtime(true) * 1000));
        } elseif (
            $action == Position::ACTION_UP ||
            $action == Position::ACTION_DOWN
        ) {
            $this->swapAdminPositionSort(
                $adminPosition,
                $action,
                $type
            );
        }

        // save
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param AdminPosition $adminPosition
     * @param string        $action
     * @param               $type
     */
    private function swapAdminPositionSort(
        $adminPosition,
        $action,
        $type
    ) {
        // get platform cookies
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $sortTime = $adminPosition->getSortTime();
        $swapPosition = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findSwapPosition(
                $platform,
                $salesCompanyId,
                $sortTime,
                $action,
                $type
            );

        // swap banner sort time
        if (!is_null($swapPosition)) {
            $swapSortTime = $swapPosition->getSortTime();
            $adminPosition->setSortTime($swapSortTime);
            $swapPosition->setSortTime($sortTime);
        }
    }

    /**
     * create admin position.
     *
     * @param Request $request the request object
     *
     * @Method({"POST"})
     * @Route("/positions")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function createAdminPositionAction(
        Request $request
    ) {
        // check user permissions
        $this->checkAdminPositionPermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();

        $position = new AdminPosition();
        $form = $this->createForm(new AdminPositionPostType(), $position);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return $this->customErrorView(
                400,
                self::INVALID_FORM_CODE,
                self::INVALID_FORM_MESSAGE
            );
        }

        $currentPlatform = $position->getCurrentPlatform();
        $this->throwNotFoundIfNull($currentPlatform, self::NOT_FOUND_MESSAGE);

        $platform = $position->getPlatform();
        $this->throwNotFoundIfNull($platform, self::NOT_FOUND_MESSAGE);

        // check platform permissions
        $this->checkPermissionForPlatform(
            $platform,
            $position,
            $currentPlatform,
            'POST'
        );

        // set parent position
        $this->setParentPosition($position);

        // set company for sales and shop
        $this->setSalesCompanyForPosition($position);

        // set icon
        $this->setIconForPosition($position);

        $name = $position->getName();
        if (is_null($name) || empty($name)) {
            $this->throwNotFoundIfNull($name, self::NOT_FOUND_MESSAGE);
        }

        // check for duplicate name
        $this->checkDuplicatePositionName(
            $name,
            $position
        );

        $em->persist($position);

        $permissions = $position->getPermissions();
        $this->addPermissions(
            $em,
            $position,
            $permissions
        );

        // add groups
        $this->addPermissionGroups(
            $em,
            $position,
            $position->getPermissionGroups()
        );

        $em->flush();

        return new View(array('id' => $position->getId()));
    }

    /**
     * update admin position.
     *
     * @param Request $request the request object
     * @param $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Method({"PUT"})
     * @Route("/positions/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function updateAdminPositionAction(
        Request $request,
        $id
    ) {
        // check user permissions
        $this->checkAdminPositionPermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();

        $position = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->find($id);
        $this->throwNotFoundIfNull($position, self::NOT_FOUND_MESSAGE);

        $oldName = $position->getName();

        $form = $this->createForm(
            new AdminPositionPutType(),
            $position,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return $this->customErrorView(
                400,
                self::INVALID_FORM_CODE,
                self::INVALID_FORM_MESSAGE
            );
        }

        $platform = $position->getPlatform();

        // check platform permissions
        $this->checkPermissionForPlatform(
            $platform,
            $position
        );

        // set parent position
        $this->setParentPosition($position);

        // set icon
        $this->setIconForPosition($position);

        $name = $position->getName();

        // check for duplicate name
        if ($oldName != $name) {
            $this->checkDuplicatePositionName($name, $position);
        }

        // set permissions
        $this->handleUpdatePermissions($em, $position);

        // add groups
        $this->addPermissionGroups(
            $em,
            $position,
            $position->getPermissionGroups()
        );

        $em->flush();

        return new View();
    }

    /**
     * delete admin position.
     *
     * @param Request $request the request object
     * @param $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Method({"DELETE"})
     * @Route("/positions/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteAdminPositionAction(
        Request $request,
        $id
    ) {
        // check user permissions
        $this->checkAdminPositionPermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();

        $position = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->find($id);
        $this->throwNotFoundIfNull($position, self::NOT_FOUND_MESSAGE);

        $platform = $position->getPlatform();

        // check platform permissions
        $this->checkPermissionForPlatform(
            $platform,
            $position
        );

        $em->remove($position);
        $em->flush();

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/positions/counts")
     * @Method({"GET"})
     *
     * @return View
     */
    public function countAdminPositionsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permissions
        $this->checkAdminPositionPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $companyId = $adminPlatform['sales_company_id'];

        $superAdminPositions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findOneBy(array(
                'isHidden' => false,
                'platform' => $platform,
                'salesCompanyId' => $companyId,
                'isSuperAdmin' => true,
            ));

        $globalPositions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->getAdminPositions(
                $platform,
                AdminPermission::PERMISSION_LEVEL_GLOBAL,
                $companyId
            );

        $specifyPositions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->getAdminPositions(
                $platform,
                AdminPermission::PERMISSION_LEVEL_SPECIFY,
                $companyId
            );

        $superAdminPositions = count($superAdminPositions);
        $globalPositions = count($globalPositions);
        $specifyPositions = count($specifyPositions);

        $response = array(
            'all_positions' => $superAdminPositions + $globalPositions + $specifyPositions,
            'global_positions' => $globalPositions,
            'specify_positions' => $specifyPositions,
            'super_administrators' => $superAdminPositions,
        );

        return new View($response);
    }

    /**
     * get admin positions.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="level"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many to return "
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
     * @Method({"GET"})
     * @Route("/positions")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminPositionsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permissions
        $this->checkAdminPositionPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $companyId = $adminPlatform['sales_company_id'];
        $type = $paramFetcher->get('type');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $this->checkPermissionForPlatform(
            $platform,
            null
        );

        $positions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->getPositions(
                $platform,
                $companyId,
                false
            );

        $superAdminPosition = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findOneBy(array(
                'isSuperAdmin' => true,
                'isHidden' => false,
                'platform' => $platform,
                'salesCompanyId' => $companyId,
            ));

        array_unshift($positions, $superAdminPosition);

        // set position extra info
        $global_image_url = $this->container->getParameter('image_url');
        foreach ($positions as $position) {
            $icon = $position->getIcon();
            $icon->setUrl($global_image_url.$icon->getIcon());

            // set groups
            $this->setPositionGroups(
                $position,
                $platform
            );
        }

        $positions = $this->get('serializer')->serialize(
            $positions,
            'json',
            SerializationContext::create()->setGroups(['admin'])
        );
        $positions = json_decode($positions, true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $positions,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param $position
     * @param $platform
     */
    private function setPositionGroups(
        $position,
        $platform
    ) {
        $groupArray = array();

        if ($position->getIsSuperAdmin()) {
            $groups = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
                ->findBy(array(
                    'platform' => $platform,
                ));

            foreach ($groups as $group) {
                array_push($groupArray, array(
                    'id' => $group->getId(),
                    'name' => $group->getGroupName(),
                ));
            }
        } else {
            $groups = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionGroupBinding')
                ->findBy(array(
                    'position' => $position,
                ));

            foreach ($groups as $group) {
                array_push($groupArray, array(
                    'id' => $group->getGroup()->getId(),
                    'name' => $group->getGroup()->getGroupName(),
                ));
            }
        }

        $position->setPermissionGroups($groupArray);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="level"
     * )
     *
     * @Annotations\QueryParam(
     *     name="admin_id",
     *     nullable=false,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="building_id",
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="shop_id",
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Route("/positions/specify_admin")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSpecifyAdminPositionsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $type = $paramFetcher->get('type');
        $adminId = $paramFetcher->get('admin_id');
        $buildingId = $paramFetcher->get('building_id');
        $shopId = $paramFetcher->get('shop_id');

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();

        $positions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getBindingsBySpecifyAdminId(
                $adminId,
                $type,
                $adminPlatform['platform'],
                $adminPlatform['sales_company_id'],
                $buildingId,
                $shopId
            );

        return new View($positions);
    }

    /**
     * List all admin position icons.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Method({"GET"})
     * @Route("/positions/icons")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminPositionIconsAction(
        Request $request
    ) {
        // check user permissions
        $this->checkAdminPositionPermission(AdminPermission::OP_LEVEL_VIEW);

        $global_image_url = $this->container->getParameter('image_url');

        $icons = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionIcons')
            ->findAll();

        foreach ($icons as $icon) {
            $icon->setUrl($global_image_url.$icon->getIcon());
            $icon->setSelectedUrl($global_image_url.$icon->getSelectedIcon());
        }

        $view = new View($icons);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin']));

        return $view;
    }

    /**
     * @param $platform
     * @param $position
     * @param null $currentPlatform
     * @param null $method
     */
    private function checkPermissionForPlatform(
        $platform,
        $position,
        $currentPlatform = null,
        $method = null
    ) {
        switch ($platform) {
            case AdminPosition::PLATFORM_OFFICIAL:
                //TODO: check official permissions

                break;
            case AdminPosition::PLATFORM_SALES:
                //TODO: check sales permissions

                if ($method == 'POST' && $currentPlatform == AdminPosition::PLATFORM_OFFICIAL) {
                    //TODO: check official permissions

                    $position->setIsSuperAdmin(true);
                }

                break;
            case AdminPosition::PLATFORM_SHOP:
                //TODO: check shop permissions

                if ($method == 'POST' && $currentPlatform == AdminPosition::PLATFORM_OFFICIAL) {
                    //TODO: check official permissions

                    $position->setIsSuperAdmin(true);
                }

                break;
            case AdminPosition::PLATFORM_COMMNUE:
                //TODO: check official permissions

                break;
            default:
                throw new AccessDeniedHttpException();

                break;
        }
    }

    /**
     * @param $em
     * @param $position
     */
    private function handleUpdatePermissions(
        $em,
        $position
    ) {
        $permissions = $position->getPermissions();

        if (is_null($permissions) || empty($permissions)) {
            return;
        }

        if (array_key_exists('add', $permissions) && !empty($permissions['add'])) {
            $this->addPermissions(
                $em,
                $position,
                $permissions['add']
            );
        }

        if (array_key_exists('modify', $permissions) && !empty($permissions['modify'])) {
            $this->modifyPermissions(
                $position,
                $permissions['modify']
            );
        }

        if (array_key_exists('remove', $permissions) && !empty($permissions['remove'])) {
            $this->removePermissions(
                $em,
                $position,
                $permissions['remove']
            );
        }
    }

    /**
     * @param AdminPosition $position
     */
    private function setParentPosition(
        $position
    ) {
        $parentId = $position->getParentPositionId();

        if (!is_null($parentId) && !empty($parentId)) {
            $parent = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPosition')
                ->find($parentId);

            $this->throwNotFoundIfNull($parent, self::NOT_FOUND_MESSAGE);

            $position->setParentPosition($parent);
        }
    }

    /**
     * @param $name
     * @param AdminPosition $position
     */
    private function checkDuplicatePositionName(
        $name,
        $position
    ) {
        $platform = $position->getPlatform();
        $salesCompanyId = $position->getSalesCompanyId();

        $existPosition = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findOneBy(array(
                'name' => $name,
                'platform' => $platform,
                'salesCompanyId' => $salesCompanyId,
            ));

        if (!is_null($existPosition)) {
            throw new ConflictHttpException();
        }
    }

    /**
     * @param $position
     */
    private function setIconForPosition(
        $position
    ) {
        $iconId = $position->getIconId();

        $icon = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionIcons')
            ->find($iconId);

        $this->throwNotFoundIfNull($icon, self::NOT_FOUND_MESSAGE);

        $position->setIcon($icon);
    }

    /**
     * @param $position
     */
    private function setSalesCompanyForPosition(
        $position
    ) {
        $companyId = $position->getSalesCompanyId();
        $platform = $position->getPlatform();

        if (($platform == AdminPosition::PLATFORM_OFFICIAL || $platform == AdminPosition::PLATFORM_COMMNUE) && is_null($companyId)) {
            return;
        } else {
            $company = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
                ->findOneBy(array(
                    'id' => $companyId,
                    'banned' => false,
                ));

            $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

            $position->setSalesCompany($company);
        }
    }

    /**
     * @param $em
     * @param $position
     * @param $permissions
     */
    private function addPermissions(
        $em,
        $position,
        $permissions
    ) {
        if (is_null($permissions) || empty($permissions)) {
            $this->throwNotFoundIfNull($permissions, self::NOT_FOUND_MESSAGE);
        }

        foreach ($permissions as $permission) {
            if (!array_key_exists('permissionId', $permission) ||
                !array_key_exists('opLevel', $permission) ||
                empty($permission['permissionId']) ||
                empty($permission['opLevel'])
            ) {
                continue;
            }

            $map = new AdminPositionPermissionMap();

            $form = $this->createForm(new AdminPositionPermissionMapType(), $map);
            $form->submit($permission);

            $adminPermissionId = $map->getPermissionId();
            $adminPermission = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPermission')
                ->find($adminPermissionId);

            if (is_null($adminPermission)) {
                continue;
            }

            $existPermission = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionPermissionMap')
                ->findOneBy(array(
                    'permissionId' => $adminPermissionId,
                    'position' => $position,
                ));
            if (!is_null($existPermission)) {
                continue;
            }

            $map->setPermission($adminPermission);
            $map->setPosition($position);

            $em->persist($map);
        }
    }

    /**
     * @param $position
     * @param $permissions
     */
    private function modifyPermissions(
        $position,
        $permissions
    ) {
        foreach ($permissions as $permission) {
            if (!array_key_exists('permissionId', $permission) ||
                !array_key_exists('opLevel', $permission) ||
                empty($permission['permissionId']) ||
                empty($permission['opLevel'])
            ) {
                continue;
            }

            $existPermission = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionPermissionMap')
                ->findOneBy(array(
                    'permissionId' => $permission['permissionId'],
                    'position' => $position,
                ));
            if (is_null($existPermission)) {
                continue;
            }

            $existPermission->setOpLevel($permission['opLevel']);
        }
    }

    /**
     * @param $em
     * @param $position
     * @param $permissions
     */
    private function removePermissions(
        $em,
        $position,
        $permissions
    ) {
        foreach ($permissions as $permission) {
            if (!array_key_exists('permissionId', $permission) ||
                empty($permission['permissionId'])
            ) {
                continue;
            }

            $existPermission = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionPermissionMap')
                ->findOneBy(array(
                    'permissionId' => $permission['permissionId'],
                    'position' => $position,
                ));
            if (is_null($existPermission)) {
                continue;
            }

            $em->remove($existPermission);
        }
    }

    /**
     * @param $em
     * @param $position
     * @param $groups
     */
    private function addPermissionGroups(
        $em,
        $position,
        $groups
    ) {
        $bindingsOld = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionGroupBinding')
            ->findBy(array(
                'position' => $position,
            ));

        if (!empty($bindingsOld)) {
            foreach ($bindingsOld as $item) {
                $em->remove($item);
            }
            $em->flush();
        }

        foreach ($groups as $group) {
            $groupEntity = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
                ->find($group['id']);

            if (is_null($groupEntity)) {
                continue;
            }

            $binding = new AdminPositionGroupBinding();
            $binding->setPosition($position);
            $binding->setGroup($groupEntity);

            $em->persist($binding);
        }
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminPositionPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADMIN],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_ADMIN],
                ['key' => AdminPermission::KEY_SHOP_PLATFORM_ADMIN],
                ['key' => AdminPermission::KEY_COMMNUE_PLATFORM_ADMIN],
            ],
            $opLevel
        );
    }
}
