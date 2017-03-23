<?php

namespace Sandbox\SalesApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesUser;
use Sandbox\ApiBundle\Entity\User\UserView;
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
     * @Annotations\QueryParam(
     *     name="card",
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
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_SPACE,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_USER,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_DASHBOARD,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $banned = $paramFetcher->get('banned');
        $authorized = $paramFetcher->get('authorized');
        $sortBy = $paramFetcher->get('sortBy');
        $direction = $paramFetcher->get('direction');
        $bindCard = $paramFetcher->get('card');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $name = $paramFetcher->get('name');

        // get sales users
        $userIds = $this->getMySalesUserIds();

        if (!is_null($startDate) || !is_null($endDate)) {
            $dateType = UserView::DATE_TYPE_BIND_CARD;

            $bindCardUserIds = $this->getUserIdByDate(
                $dateType,
                $startDate,
                $endDate
            );

            $userIds = array_intersect($userIds, $bindCardUserIds);
        }

        $results = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserView')
            ->searchSalesUser(
                $banned,
                $authorized,
                $name,
                $sortBy,
                $direction,
                $userIds,
                $offset,
                $pageLimit,
                $bindCard
            );

        // get total count
        $countSalesUsers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserView')
            ->countSalesUsers(
                $banned,
                $authorized,
                $name,
                $sortBy,
                $direction,
                $userIds,
                $offset,
                $pageLimit,
                $bindCard
            );

        return new View(array(
            'current_page_number' => $pageIndex,
            'num_items_per_page' => $pageLimit,
            'items' => $results,
            'total_count' => $countSalesUsers,
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_SPACE,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_USER,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_INVOICE,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        $banned = $paramFetcher->get('banned');

        $sortBy = $paramFetcher->get('sortBy');
        $direction = $paramFetcher->get('direction');

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        // get sales users
        $userIds = $this->getMySalesUserIds();

        // return result according to ids
        if (is_null($ids) || empty($ids)) {
            // ids is null
            return $this->getUsersNotByIds(
                $banned,
                $sortBy,
                $direction,
                $pageLimit,
                $pageIndex,
                $userIds
            );
        } else {
            // get valid user ids
            $validIds = array();
            foreach ($ids as $id) {
                if (in_array($id, $userIds)) {
                    array_push($validIds, $id);
                }
            }

            // ids is not null
            return $this->getUsersByIds($validIds);
        }
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
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_SPACE,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_USER,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        // get user
        $user = $this->getRepo('User\User')->getUserInfo($id);
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
        // get user
        $user = $this->getRepo('User\User')->find($id);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        // get sales users
        $userIds = $this->getMySalesUserIds();
        if (!in_array($user->getId(), $userIds)) {
            return new View();
        }

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
            $this->throwAccessDeniedIfAdminNotAllowed(
                $this->getAdminId(),
                array(
                    array(
                        'key' => AdminPermission::KEY_SALES_BUILDING_USER,
                    ),
                ),
                AdminPermission::OP_LEVEL_EDIT
            );
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
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_USER,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        $data = json_decode($request->getContent(), true);
        $em = $this->getDoctrine()->getManager();

        // check building id
        if (!array_key_exists('building_id', $data)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $building = $this->getRepo('Room\RoomBuilding')->find($data['building_id']);
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        // get user Entity
        $user = $this->getRepo('User\User')->find($id);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        // authorized user
        $user->setAuthorized(true);
        $user->setModificationDate(new \DateTime('now'));

        // check sales user record
        $adminPlatform = $this->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];
        $buildingId = $building->getId();

        $salesUserId = $user->getId();
        $salesUser = $this->getRepo('SalesAdmin\SalesUser')->findOneBy(array(
            'userId' => $salesUserId,
            'buildingId' => $buildingId,
        ));

        if (is_null($salesUser)) {
            $salesUser = new SalesUser();

            $salesUser->setUserId($salesUserId);
            $salesUser->setCompanyId($companyId);
            $salesUser->setBuildingId($buildingId);
        }

        $salesUser->setIsAuthorized(true);
        $salesUser->setModificationDate(new \DateTime('now'));

        // set authorized admin
        $adminUsername = $this->getUser()->getUserId();
        $user->setAuthorizedPlatform(User::AUTHORIZED_PLATFORM_SALES);
        $user->setAuthorizedAdminUsername($adminUsername);

        $em->persist($salesUser);

        // update to db
        $em->flush();

        return new View();
    }

    /**
     * @param $banned
     * @param $sortBy
     * @param $direction
     * @param $pageLimit
     * @param $pageIndex
     * @param $userIds
     *
     * @return View
     */
    public function getUsersNotByIds(
        $banned,
        $sortBy,
        $direction,
        $pageLimit,
        $pageIndex,
        $userIds
    ) {
        // get user id and name
        $users = $this->getRepo('User\UserView')->getSalesUsers(
            $banned,
            $sortBy,
            $direction,
            $userIds
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

                $this->updateXmppUser($user->getXmppUsername(), null, $profile->getName());
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

    private function getMySalesUserIds()
    {
        $adminId = $this->getAdminId();

        $buildingIds = $this->getMySalesBuildingIds(
            $adminId,
            array(
                AdminPermission::KEY_SALES_BUILDING_USER,
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        $userIds = $this->getRepo('SalesAdmin\SalesUser')->getSalesUsers($buildingIds);

        $ids = array();
        foreach ($userIds as $user) {
            array_push($ids, $user['userId']);
        }

        return $ids;
    }
}
