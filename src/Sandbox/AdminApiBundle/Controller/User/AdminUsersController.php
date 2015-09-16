<?php

namespace Sandbox\AdminApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Sandbox\ApiBundle\Entity\User;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Form\User\UserType;
use Rs\Json\Patch;

/**
 * Admin controller.
 *
 * @category Sandbox
 *
 * @author   Mike he <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminUsersController extends SandboxRestController
{
    const ERROR_USERNAME_INVALID_CODE = 400001;
    const ERROR_USERNAME_INVALID_MESSAGE = 'Invalid username - 无效的用户名';

    const ERROR_USERNAME_EXIST_CODE = 400002;
    const ERROR_USERNAME_EXIST_MESSAGE = 'Username already exist - 用户名已被占用';

    const ERROR_PASSWORD_INVALID_CODE = 400003;
    const ERROR_PASSWORD_INVALID_MESSAGE = 'Invalid password - 无效的密码';

    const ERROR_ADMIN_TYPE_CODE = 400004;
    const ERROR_ADMIN_TYPE_MESSAGE = 'Invalid admin type - 无效的管理员类型';

    /**
     * Search user.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many rooms to return "
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
     *    name="banned",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="user banned"
     * )
     *
     *
     * @Annotations\QueryParam(
     *    name="authorized",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="user authorized"
     * )
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    default=null,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sortBy",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Sort by id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="direction",
     *    array=false,
     *    default="DESC",
     *    nullable=true,
     *    requirements="(ASC|DESC)",
     *    strict=true,
     *    description="sort direction"
     * )
     *
     * @Route("/users/search")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getUsersSearchAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_USER,
            AdminPermissionMap::OP_LEVEL_VIEW
        );

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $banned = $paramFetcher->get('banned');
        $authorized = $paramFetcher->get('authorized');
        $query = $paramFetcher->get('query');
        $sortBy = $paramFetcher->get('sortBy');
        $direction = $paramFetcher->get('direction');

        // Another better way to search the users by query, but it needs to find out the bug and fix it yet
//        // find all users who have the query in any of their mapped fields
//        $finder = $this->container->get('fos_elastica.finder.search.user');
//
//        $multiMatchQuery = new \Elastica\Query\MultiMatch();
//
//        $multiMatchQuery->setQuery($query);
//        $multiMatchQuery->setType('phrase_prefix');
//        $multiMatchQuery->setFields(array('email', 'phone'));
//
//        $results = $finder->createPaginatorAdapter($multiMatchQuery);
//
//        $paginator = $this->get('knp_paginator');

        $results = $this->getRepo('User\UserView')->searchUser(
            $banned,
            $authorized,
            $query,
            $sortBy,
            $direction
        );

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $results,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * List all users.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="banned",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by banned"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sortBy",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Sort by id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="direction",
     *    array=false,
     *    default="DESC",
     *    nullable=true,
     *    requirements="(ASC|DESC)",
     *    strict=true,
     *    description="sort direction"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many admins to return "
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
     * @Route("/users")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getUsersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $ids = $paramFetcher->get('id');

        // check user permission
        if (!is_null($ids) || !empty($ids)) {
            $this->throwAccessDeniedIfAdminNotAllowed(
                $this->getAdminId(),
                AdminType::KEY_PLATFORM,
                array(
                    AdminPermission::KEY_PLATFORM_USER,
                    AdminPermission::KEY_PLATFORM_ORDER,
                ),
                AdminPermissionMap::OP_LEVEL_VIEW
            );
        } else {
            $this->throwAccessDeniedIfAdminNotAllowed(
                $this->getAdminId(),
                AdminType::KEY_PLATFORM,
                AdminPermission::KEY_PLATFORM_ORDER,
                AdminPermissionMap::OP_LEVEL_VIEW
            );
        }

        $banned = $paramFetcher->get('banned');

        $sortBy = $paramFetcher->get('sortBy');
        $direction = $paramFetcher->get('direction');

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        //return result according to ids
        if (is_null($ids) || empty($ids)) {
            //ids is null
            return $this->getUsersNotByIds(
                $banned,
                $sortBy,
                $direction,
                $pageLimit,
                $pageIndex
            );
        } else {
            //ids is not null
            return $this->getUsersByIds($ids);
        }
    }

    /**
     * List definite id of user.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Method({"GET"})
     * @Route("/users/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getUserAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_USER,
            AdminPermissionMap::OP_LEVEL_VIEW
        );

        // get user
        $user = $this->getRepo('User\UserView')->find($id);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        // set view
        return new View($user);
    }

    /**
     * Edit user info.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/users/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchUserAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_USER,
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        //get user Entity
        $user = $this->getRepo('User\User')->find($id);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        // bind data
        $userJson = $this->container->get('serializer')->serialize($user, 'json');
        $patch = new Patch($userJson, $request->getContent());
        $userJson = $patch->apply();

        $form = $this->createForm(new UserType(), $user);
        $form->submit(json_decode($userJson, true));

        // update to db
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Authorized a user.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/users/{id}/authorized")
     * @Method({"POST"})
     *
     * @return View
     */
    public function authorizedUserAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_USER,
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        // get user Entity
        $user = $this->getRepo('User\User')->find($id);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        // authorized user
        $user->setAuthorized(true);

        $user->setModificationDate(new \DateTime('now'));

        // update to db
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param $banned
     * @param $sortBy
     * @param $direction
     * @param $pageLimit
     * @param $pageIndex
     *
     * @return View
     */
    public function getUsersNotByIds(
        $banned,
        $sortBy,
        $direction,
        $pageLimit,
        $pageIndex
    ) {
        // get user id and name
        $users = $this->getRepo('User\UserView')->getUsers(
            $banned,
            $sortBy,
            $direction
        );
        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $users,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param $ids
     *
     * @return View
     */
    public function getUsersByIds(
        $ids
    ) {
        // get user
        $user = $this->getRepo('User\UserView')->getUsersByIds($ids);

        // set view
        return new View($user);
    }
}
