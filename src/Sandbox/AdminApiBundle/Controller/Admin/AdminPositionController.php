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
     * @param Request $request
     * @param $id
     *
     * @Route("positions/{id}/change_position")
     * @Method({"POST"})
     *
     * @return View
     */
    public function changePositionSortAction(
        Request $request,
        $id
    ) {
        // get position
        $adminPosition = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->find($id);
        $this->throwNotFoundIfNull($adminPosition, self::NOT_FOUND_MESSAGE);

        $sort = new Position();
        $form = $this->createForm(new PositionType(), $sort);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->updatePositionSort(
            $adminPosition,
            $sort
        );
    }

    /**
     * @param AdminPosition $adminPosition
     * @param Position      $sort
     *
     * @return View
     */
    private function updatePositionSort(
        $adminPosition,
        $sort
    ) {
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
                $action
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
     */
    private function swapAdminPositionSort(
        $adminPosition,
        $action
    ) {
        // get platform cookies
        $cookies = $this->getPlatformCookies();
        $platform = $cookies['platform'];
        $salesCompanyId = $cookies['sales_company_id'];

        $sortTime = $adminPosition->getSortTime();
        $swapPosition = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findSwapPosition(
                $platform,
                $salesCompanyId,
                $sortTime,
                $action
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
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
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
        $cookies = $this->getPlatformCookies();
        $platform = $cookies['platform'];
        $companyId = $cookies['sales_company_id'];

        $allPositions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->getAdminPositions(
                $platform,
                null,
                $companyId
            );

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

        $allPositions = count($allPositions);
        $globalPositions = count($globalPositions);
        $specifyPositions = count($specifyPositions);

        $response = array(
            'all_positions' => $allPositions,
            'global_positions' => $globalPositions,
            'specify_positions' => $specifyPositions,
            'super_administrators' => $allPositions - ($globalPositions + $specifyPositions),
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
     *    name="platform",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="platform"
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
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="company id"
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
        $platform = $paramFetcher->get('platform');
        $type = $paramFetcher->get('type');
        $companyId = $paramFetcher->get('company');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $this->checkPermissionForPlatform(
            $platform,
            null
        );

        $positions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->getAdminPositions(
                $platform,
                $type,
                $companyId
            );

        $global_image_url = $this->container->getParameter('image_url');
        foreach ($positions as $position) {
            $icon = $position->getIcon();
            $icon->setUrl($global_image_url.$icon->getIcon());
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
        $global_image_url = $this->container->getParameter('image_url');

        $icons = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionIcons')
            ->findAll();

        foreach ($icons as $icon) {
            $icon->setUrl($global_image_url.$icon->getIcon());
        }

        return new View($icons);
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

        if ($platform == AdminPosition::PLATFORM_OFFICIAL && is_null($companyId)) {
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
}
