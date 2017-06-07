<?php

namespace Sandbox\SalesApiBundle\Controller\User;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\User\UserInfo;
use Sandbox\ApiBundle\Form\User\UserInfoType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminUserInfoController extends SalesRestController
{
    /**
     * Get user's info.
     *
     * @param Request $request the request object
     * @param int     $userId
     *
     * @Route("/users/{userId}/info")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserInfoAction(
        Request $request,
        $userId
    ) {
        // check user permission
        $this->checkUserPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $userInfo = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserInfo')
            ->findOneBy([
                'companyId' => $salesCompanyId,
                'userId' => $userId,
            ]);

        return new View($userInfo);
    }

    /**
     * create user's info.
     *
     * @param Request $request the request object
     * @param int     $userId
     *
     * @Route("/users/{userId}/info")
     * @Method({"POST"})
     *
     * @return View
     */
    public function createUserInfoAction(
        Request $request,
        $userId
    ) {
        // check user permission
        $this->checkUserPermission(AdminPermission::OP_LEVEL_EDIT);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        // check if info exist
        $userInfo = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserInfo')
            ->findOneBy([
                'companyId' => $salesCompanyId,
                'userId' => $userId,
            ]);
        if (!is_null($userInfo)) {
            return new View($userInfo);
        }

        // check user exist
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        // check if user within sales company
        $salesUser = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesUser')
            ->findOneBy([
                'companyId' => $salesCompanyId,
                'userId' => $userId,
            ]);
        $this->throwNotFoundIfNull($salesUser, self::NOT_FOUND_MESSAGE);

        return $this->postUserInfo(
            $request,
            $userId,
            $salesCompanyId
        );
    }

    /**
     * update user's info.
     *
     * @param Request $request the request object
     * @param int     $userId
     *
     * @Route("/users/{userId}/info")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function updateUserInfoAction(
        Request $request,
        $userId
    ) {
        // check user permission
        $this->checkUserPermission(AdminPermission::OP_LEVEL_EDIT);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        // check if info exist
        $userInfo = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserInfo')
            ->findOneBy([
                'companyId' => $salesCompanyId,
                'userId' => $userId,
            ]);
        $this->throwNotFoundIfNull($userInfo, self::NOT_FOUND_MESSAGE);

        return $this->putUserInfo(
            $request,
            $userInfo
        );
    }

    /**
     * @param $request
     * @param $userInfo
     *
     * @return View
     */
    private function putUserInfo(
        $request,
        $userInfo
    ) {
        $form = $this->createForm(
            new UserInfoType(),
            $userInfo,
            ['method' => 'PUT']
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param $request
     * @param $userId
     * @param $companyId
     *
     * @return View
     */
    private function postUserInfo(
        $request,
        $userId,
        $companyId
    ) {
        $userInfo = new UserInfo();
        $form = $this->createForm(new UserInfoType(), $userInfo);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $userInfo->setCompanyId($companyId);
        $userInfo->setUserId($userId);

        $em = $this->getDoctrine()->getManager();
        $em->persist($userInfo);
        $em->flush();

        // set view
        $view = new View();
        $view->setStatusCode(201);
        $view->setData(array(
            'id' => $userInfo->getId(),
        ));

        return $view;
    }

    /**
     * @param $opLevel
     */
    private function checkUserPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_USER,
                ),
            ),
            $opLevel
        );
    }
}
