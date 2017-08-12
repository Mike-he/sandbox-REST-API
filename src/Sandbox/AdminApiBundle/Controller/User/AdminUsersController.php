<?php

namespace Sandbox\AdminApiBundle\Controller\User;

use Elastica\Param;
use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesUser;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
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
use Sandbox\ApiBundle\Constants\DoorAccessConstants;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Sandbox\ApiBundle\Traits\StringUtil;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
class AdminUsersController extends DoorController
{
    use DoorAccessTrait;
    use StringUtil;

    const ERROR_USERNAME_INVALID_CODE = 400001;
    const ERROR_USERNAME_INVALID_MESSAGE = 'Invalid username - 无效的用户名';

    const ERROR_USERNAME_EXIST_CODE = 400002;
    const ERROR_USERNAME_EXIST_MESSAGE = 'Username already exist - 用户名已被占用';

    const ERROR_PASSWORD_INVALID_CODE = 400003;
    const ERROR_PASSWORD_INVALID_MESSAGE = 'Invalid password - 无效的密码';

    const ERROR_ADMIN_TYPE_CODE = 400004;
    const ERROR_ADMIN_TYPE_MESSAGE = 'Invalid admin type - 无效的管理员类型';

    /**
     * @param Request $request
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
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="building id"
     * )
     *
     * @Route("/users/sync")
     * @Method({"GET"})
     *
     * @return View
     */
    public function syncSalesUsersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime('now');

        $adminCompanyId = $paramFetcher->get('company');
        $adminBuildingId = $paramFetcher->get('building');

        if (is_null($adminBuildingId) || is_null($adminCompanyId)) {
            throw new BadRequestHttpException();
        }

        // sync users by orders
        $orders = $this->getRepo('Order\ProductOrder')->findAll();

        foreach ($orders as $order) {
            if (is_null($order)) {
                continue;
            }

            $product = $this->getRepo('Product\Product')->find($order->getProductId());
            $companyId = $product->getRoom()->getBuilding()->getCompanyId();
            $buildingId = $product->getRoom()->getBuildingId();

            $userId = $order->getUserId();

            $salesUser = $this->checkSalesUser(
                $em,
                $userId,
                $companyId,
                $buildingId
            );

            $salesUser->setIsOrdered(true);
            $salesUser->setModificationDate($now);
        }

        // sync users by authorization
        $users = $this->getRepo('User\User')->findByAuthorized(true);

        foreach ($users as $user) {
            $userId = $user->getId();
            $companyId = $adminCompanyId;

            $salesUser = $this->checkSalesUser(
                $em,
                $userId,
                $companyId,
                $adminBuildingId
            );

            $salesUser->setIsAuthorized(true);
            $salesUser->setModificationDate($now);
        }

        $em->flush();

        return new View();
    }

    /**
     * @param $em
     * @param $userId
     * @param $companyId
     * @param $buildingId
     *
     * @return SalesUser
     */
    private function checkSalesUser(
        $em,
        $userId,
        $companyId,
        $buildingId
    ) {
        $salesUser = $this->getRepo('SalesAdmin\SalesUser')->findOneBy(array(
            'userId' => $userId,
            'companyId' => $companyId,
            'buildingId' => $buildingId,
        ));

        if (is_null($salesUser)) {
            $salesUser = new SalesUser();

            $salesUser->setUserId($userId);
            $salesUser->setCompanyId($companyId);
            $salesUser->setBuildingId($buildingId);

            $em->persist($salesUser);
        }

        return $salesUser;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    default=null,
     *    description="search query"
     * )
     *
     * @Route("/users/by_phone")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserByPhoneAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $query = $paramFetcher->get('query');

        $results = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserView')
            ->searchUserByPhone(
                $query
            );

        return new View($results);
    }

    /**
     * Search user.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many rooms to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default=null,
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
     *     name="card",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="dateType",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="startDate",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="endDate",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="name",
     *    default=null,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="phone",
     *    default=null
     * )
     *
     * @Annotations\QueryParam(
     *    name="email",
     *    default=null
     * )
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    default=null
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
     * @Annotations\QueryParam(
     *     name="pendingAuth",
     *     nullable=true,
     *     strict=true
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
        $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissions(
                $this->getAdminId(),
                [
                    ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_USER],
                    ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_RESERVE],
                    ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_PREORDER],
                ],
                AdminPermission::OP_LEVEL_VIEW
            );

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $banned = $paramFetcher->get('banned');
        $authorized = $paramFetcher->get('authorized');
        $sortBy = $paramFetcher->get('sortBy');
        $direction = $paramFetcher->get('direction');
        $pendingAuth = (bool) $paramFetcher->get('pendingAuth');
        $bindCard = $paramFetcher->get('card');
        $dateType = $paramFetcher->get('dateType');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $name = $paramFetcher->get('name');
        $phone = $paramFetcher->get('phone');
        $email = $paramFetcher->get('email');
        $id = $paramFetcher->get('id');
        $search = $paramFetcher->get('query');

        $userIds = null;
        if ($pendingAuth) {
            $userIds = $this->getPendingAuthUserIds();
        }

        if (!is_null($dateType)) {
            $userIds = $this->getUserIdByDate(
                $dateType,
                $startDate,
                $endDate
            );
        }

        $results = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserView')
            ->searchUser(
                $banned,
                $authorized,
                $sortBy,
                $direction,
                $offset,
                $pageLimit,
                $userIds,
                $bindCard,
                $dateType,
                $startDate,
                $endDate,
                $name,
                $phone,
                $email,
                $id,
                $search
            );

        // get total count
        $usersCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserView')
            ->countUsers(
                $banned,
                $authorized,
                $sortBy,
                $direction,
                $offset,
                $pageLimit,
                $userIds,
                $bindCard,
                $dateType,
                $startDate,
                $endDate,
                $name,
                $phone,
                $email,
                $id,
                $search
            );

        foreach ($results as $user) {
            // set authorized building
            $salesUser = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesUser')
                ->findOneBy(array(
                    'userId' => $user->getId(),
                    'isAuthorized' => true,
                ));

            if (!is_null($salesUser)) {
                $building = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->find($salesUser->getBuildingId());

                if (!is_null($building)) {
                    $user->setBuilding($building->getName());
                }
            }

            // set sales invoice amount
            $amount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Order\ProductOrder')
                ->getInvoiceOrdersAmount($user->getId());

            $amount = is_null($amount) ? 0 : $amount;
            $user->setSalesInvoiceAmount($amount);
        }

        if (!is_null($pageIndex) && !is_null($pageLimit)) {
            $return = array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => $pageLimit,
                'items' => $results,
                'total_count' => $usersCount,
            );

            return new View($return);
        }

        return new View($results);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/users/pending_auth_count")
     * @Method({"GET"})
     *
     * @return View
     */
    public function pendingAuthUsersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userIds = $this->getPendingAuthUserIds();

        return new View(array(
            'pending_auth_users_count' => count($userIds),
        ));
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
        $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SPACE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_USER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_INVOICE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_RESERVE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_PREORDER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT_APPOINTMENT_VERIFY],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LOG],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADVERTISING],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_DASHBOARD],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_REFUND],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        $banned = $paramFetcher->get('banned');

        $sortBy = $paramFetcher->get('sortBy');
        $direction = $paramFetcher->get('direction');

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        // return result according to ids
        if (is_null($ids) || empty($ids)) {
            // ids is null
            return $this->getUsersNotByIds(
                $banned,
                $sortBy,
                $direction,
                $pageLimit,
                $pageIndex
            );
        } else {
            // ids is not null
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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SPACE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_USER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_INVOICE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_RESERVE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_PREORDER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT_APPOINTMENT_VERIFY],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LOG],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADVERTISING],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_DASHBOARD],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_REFUND],
            ],
            AdminPermission::OP_LEVEL_VIEW
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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_USER],
            ],
            AdminPermission::OP_LEVEL_EDIT
        );

        // get user
        $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')->find($id);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);
        $banned = $user->isBanned();

        // bind data
        $userJson = $this->container->get('serializer')->serialize($user, 'json');
        $patch = new Patch($userJson, $request->getContent());
        $userJson = $patch->apply();

        $form = $this->createForm(new UserType(), $user);
        $form->submit(json_decode($userJson, true));
        $updateBanned = $user->isBanned();

        // check if user banned status changed
        if ($banned !== $updateBanned) {
            $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
                $this->getAdminId(),
                [
                    ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_USER],
                ],
                AdminPermission::OP_LEVEL_USER_BANNED
            );
        }

        if ($updateBanned) {
            $user->setBannedDate(new \DateTime('now'));
        }

        // update to db
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // update door access status
        $result = $this->getCardNoByUser($id);
        if (
            is_null($result) ||
            $result['status'] === DoorController::STATUS_UNAUTHED
        ) {
            return new View();
        }

        // set card
        $cardNo = $result['card_no'];
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($id);
        $userName = $userProfile->getName();
        $this->updateEmployeeCardStatus(
            $id,
            $userName,
            $cardNo,
            DoorAccessConstants::METHOD_ADD
        );
        sleep(1);

        // update card
        $method = DoorAccessConstants::METHOD_UNLOST;
        if ($user->isBanned()) {
            $method = DoorAccessConstants::METHOD_LOST;
        }
        $this->updateEmployeeCardStatus(
            $id,
            '',
            $cardNo,
            $method
        );

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
        // get user Entity
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($id);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        // authorized user
        $user->setModificationDate(new \DateTime('now'));

        // set authorized admin
        $adminUsername = $this->getUser()->getUserId();
        $user->setAuthorizedPlatform(User::AUTHORIZED_PLATFORM_OFFICIAL);
        $user->setAuthorizedAdminUsername($adminUsername);

        if (!is_null($user->getCredentialNo())) {
            $user->setAuthorized(true);
        }

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

    /**
     * @param Request $request
     *
     * @Route("/users/buddy/set")
     * @Method({"POST"})
     *
     * @return View
     */
    public function setServiceAsBuddyAction(
        Request $request
    ) {
        $users = $this->getRepo('User\User')->getNonServiceUsers();

        if (!empty($users)) {
            $this->addBuddyToUser($users);
        }

        return new View();
    }

    /**
     * @param Request $request
     *
     * @Route("/users/openfire/set")
     * @Method({"POST"})
     *
     * @return View
     */
    public function setUserInfoToOpenfireAction(
        Request $request
    ) {
        $users = $this->getRepo('User\User')->findAll();

        foreach ($users as $user) {
            try {
                $profile = $this->getRepo('User\UserProfile')->findOneByUser($user);

                $this->get('sandbox_api.jpush_im')
                    ->updateNickname(
                        $user->getXmppUsername(),
                        $profile->getName()
                    );
            } catch (\Exception $e) {
                error_log('Sync user went wrong. User ID: '.$user->getId());
                continue;
            }
        }

        return new View();
    }

    /**
     * @param Request $request
     *
     * @Route("/users/profiles/set")
     * @Method({"POST"})
     *
     * @return View
     */
    public function setUserProfileAction(
        Request $request
    ) {
        $profiles = $this->getRepo('User\UserProfile')->findByName('');

        foreach ($profiles as $profile) {
            try {
                $name = $profile->getName();
                if (!empty($name)) {
                    // just to make sure name is not set
                    continue;
                }

                $user = $profile->getUser();
                $phone = $user->getPhone();
                $email = $user->getEmail();

                if (!is_null($phone)) {
                    $name = substr($phone, strlen($phone) - 6);
                } elseif (!is_null($email)) {
                    $name = $this->before('@', $email);
                }

                $profile->setName($name);
            } catch (\Exception $e) {
                error_log('Sync user profile went wrong. User profile ID: '.$profile->getId());
                continue;
            }
        }

        // update to db
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @Route("/open/users")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getOpenUsersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $ids = $paramFetcher->get('id');

        return $this->getUsersByIds($ids);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $userId
     *
     * @Route("/open/users/{userId}/extra_info")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getOpenUsersLastLoginAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $userId
    ) {
        $userToken = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserToken')
            ->getLastLoginUser($userId);

        $lastLoginDate = !is_null($userToken) ? $userToken->getModificationDate() : null;

        $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')->find($userId);
        $bannedDate = !is_null($user) ? $user->getBannedDate() : null;

        return new View(array(
            'last_login_date' => $lastLoginDate,
            'banned_date' => $bannedDate,
        ));
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="name",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by name"
     * )
     *
     * @Annotations\QueryParam(
     *    name="account",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by phone or email"
     * )
     *
     * @Route("/users/ids/search")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSearchUserIdsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $name = $paramFetcher->get('name');
        $account = $paramFetcher->get('account');

        $userIds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserView')
            ->getUserIds($name, $account);

        return new View($userIds);
    }

    /**
     * @return mixed
     */
    private function getPendingAuthUserIds()
    {
        $crmUrl = $this->container->getParameter('crm_api_url');
        $url = $crmUrl.'/admin/user/ids/search?pendingAuth=1';
        $ch = curl_init($url);

        $result = $this->callAPI($ch, 'GET');
        $userIds = json_decode($result, true);

        return $userIds;
    }
}
