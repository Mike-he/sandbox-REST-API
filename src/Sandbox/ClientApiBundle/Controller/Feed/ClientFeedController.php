<?php

namespace Sandbox\ClientApiBundle\Controller\Feed;

use Sandbox\ApiBundle\Constants\PlatformConstants;
use Sandbox\ApiBundle\Controller\Feed\FeedController;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ClientFeedController extends FeedController
{
    /**
     * List all feed.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many feeds to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="last_id",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="last id"
     * )
     *
     * @Annotations\QueryParam(
     *     name="platform",
     *     array=false,
     *     default="official",
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Route("feeds/all")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFeedsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        // get params
        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');
        $platform = $paramFetcher->get('platform');

        $params = array(
            'platform' => $platform,
            'users' => array(),
            'limit' => $limit,
            'offset' => $lastId,
        );

        $result = $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_feed'),
            'FeedService.lists',
            $params
        );

        return $this->handleGetFeeds($result['result'], $userId);
    }

    /**
     * List all feed by buddies.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many feeds to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="last_id",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="last id"
     * )
     *
     * @Annotations\QueryParam(
     *     name="platform",
     *     array=false,
     *     default="official",
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Route("feeds/buddy")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getFeedsByBuddyAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        } else {
            throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
        }

        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');
        $platform = $paramFetcher->get('platform');

        $buddies = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Buddy\Buddy')
            ->getBuddies($userId);

        $params = array(
            'platform' => $platform,
            'users' => $buddies,
            'limit' => $limit,
            'offset' => $lastId,
        );

        $result = $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_feed'),
            'FeedService.lists',
            $params
        );

        return $this->handleGetFeeds($result['result'], $userId);
    }

    /**
     * List all feed by my building.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many feeds to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="last_id",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="last id"
     * )
     *
     * @Annotations\QueryParam(
     *     name="platform",
     *     array=false,
     *     default="official",
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Route("feeds/building")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getFeedsByBuildingAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        } else {
            throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
        }

        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');
        $platform = $paramFetcher->get('platform');

        $profile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);

        $buildingId = $profile->getBuildingId();
        if (is_null($buildingId)) {
            return new View(array());
        }

        $users = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserProfile')
            ->findUsersByBuilding($buildingId);

        $params = array(
            'platform' => $platform,
            'users' => $users,
            'limit' => $limit,
            'offset' => $lastId,
        );

        $result = $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_feed'),
            'FeedService.lists',
            $params
        );

        return $this->handleGetFeeds($result['result'], $userId);
    }

    /**
     * List all feed by my colleagues.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many feeds to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="last_id",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="last id"
     * )
     *
     * @Annotations\QueryParam(
     *     name="platform",
     *     array=false,
     *     default="official",
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Route("feeds/company")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getFeedsByColleaguesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        } else {
            throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
        }

        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');
        $platform = $paramFetcher->get('platform');

        $users = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Company\CompanyMember')
            ->getCompanyMembersByUser($userId);

        $params = array(
            'platform' => $platform,
            'users' => $users,
            'limit' => $limit,
            'offset' => $lastId,
        );

        $result = $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_feed'),
            'FeedService.lists',
            $params
        );

        return $this->handleGetFeeds($result['result'], $userId);
    }

    /**
     * List all my feeds.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many feeds to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="last_id",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="last id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="user_id",
     *    default=null,
     *    description="userId"
     * )
     *
     * @Annotations\QueryParam(
     *     name="platform",
     *     array=false,
     *     default="official",
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Route("/feeds/my")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getMyFeedsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        // request user
        $assignUserId = $paramFetcher->get('user_id');
        if (is_null($assignUserId)) {
            $assignUserId = $userId;
        }

        // get request user
        $assignUser = $this->getRepo('User\User')->find($assignUserId);
        $this->throwNotFoundIfNull($assignUser, self::NOT_FOUND_MESSAGE);

        // check the other user is banned
        if ($assignUser->isBanned()) {
            return new View();
        }

        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');
        $platform = $paramFetcher->get('platform');

        $params = array(
            'platform' => $platform,
            'users' => $assignUserId,
            'limit' => $limit,
            'offset' => $lastId,
        );

        $result = $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_feed'),
            'FeedService.lists',
            $params
        );

        return $this->handleGetFeeds($result['result'], $userId);
    }

    /**
     * Get feed by id.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("feeds/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFeedAction(
        Request $request,
        $id
    ) {
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        $result = $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_feed'),
            'FeedService.detail',
            [$id]
        );

        $data = $result['result'];
        $this->throwNotFoundIfNull($data, self::NOT_FOUND_MESSAGE);

        $feed = $this->setFeed($data, $userId);

        return new View($feed);
    }

    /**
     * Add new feed.
     *
     * @param Request $request
     *
     * @Route("/feeds")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postFeedAction(
        Request $request
    ) {
        $myUserId = $this->getUserId();

        $params = json_decode($request->getContent(), true);
        $params['owner'] = $myUserId;
        $params['platform'] = isset($params['platform']) ? $params['platform'] : PlatformConstants::PLATFORM_OFFICIAL;
        $params['attachments'] = isset($params['feed_attachments']) ? $params['feed_attachments'] : array();

        $result = $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_feed'),
            'FeedService.create',
            $params
        );

        $response = array(
            'id' => $result['result'],
        );

        return new View($response, 201);
    }

    /**
     * delete feed by id.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("feeds/{id}")
     * @Method({"DELETE"})
     */
    public function deleteFeedAction(
        Request $request,
        $id
    ) {
        $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_feed'),
            'FeedService.remove',
            [
                'id' => $id,
                'user' => $this->getUserId(),
            ]
        );
    }
}
