<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Sandbox\ApiBundle\Entity\Admin\AdminPositionPermissionMap;
use Sandbox\ApiBundle\Form\Admin\AdminPositionPermissionMapType;
use Sandbox\ApiBundle\Form\Admin\AdminPositionPostType;
use Sandbox\ApiBundle\Form\Admin\AdminPositionPutType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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

        // check platform permissions
        $platform = $position->getPlatform();
        $this->checkPermissionForPlatform($platform);

        // set parent position
        $this->setParentPosition($position);

        // set company for sales and shop
        $this->setSalesCompanyForPosition($platform, $position);

        // set icon
        $this->setIconForPosition($position);

        $name = $position->getName();
        if (is_null($name) || empty($name)) {
            $this->throwNotFoundIfNull($name, self::NOT_FOUND_MESSAGE);
        }

        // check for duplicate name
        $this->checkDuplicatePositionName($name, $position);

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

        // check platform permissions
        $platform = $position->getPlatform();
        $this->checkPermissionForPlatform($platform);

        // set parent position
        $this->setParentPosition($position);

        // set icon
        $this->setIconForPosition($position);

        $name = $position->getName();

        // check for duplicate name
        $this->checkDuplicatePositionName($name, $position);

        // set permissions
        $this->handleUpdatePermissions($em, $position);

        $em->flush();

        return new View();
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
        $icons = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionIcons')
            ->findAll();

        return new View($icons);
    }

    /**
     * @param $platform
     */
    private function checkPermissionForPlatform(
        $platform
    ) {
        switch ($platform) {
            case AdminPosition::PLATFORM_OFFICIAL:
                //TODO: check official permissions
                break;
            case AdminPosition::PLATFORM_SALES:
                //TODO: check sales permissions
                break;
            case AdminPosition::PLATFORM_SHOP:
                //TODO: check shop permissions
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
     * @param $position
     */
    private function setParentPosition(
        $position
    ) {
        $parent = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->find($position->getParentPositionId());
        if (is_null($parent)) {
            $position->setParentPositionId(null);
        }
    }

    /**
     * @param $name
     * @param $position
     */
    private function checkDuplicatePositionName(
        $name,
        $position
    ) {
        $existPosition = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findOneBy(array(
                'name' => $name,
                'salesCompanyId' => $position->getSalesCompanyId(),
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
     * @param $platform
     * @param $position
     */
    private function setSalesCompanyForPosition(
        $platform,
        $position
    ) {
        $companyId = $position->getSalesCompanyId();
        if ($platform == AdminPosition::PLATFORM_SALES || $platform == AdminPosition::PLATFORM_SHOP) {
            $company = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
                ->find($companyId);
            $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

            $position->setSalesCompany($company);
        }
    }

    /**
     * @param $em
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
