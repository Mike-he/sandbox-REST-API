<?php

namespace Sandbox\ClientApiBundle\Controller\Feed;

use Sandbox\ApiBundle\Controller\Feed\FeedLikeController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class ClientFeedLikeController extends FeedLikeController
{
    /**
     * Get all likes of a given feed.
     *
     * @param Request $request the request object
     * @param int     $id      the feed id
     *
     * @Route("/feeds/{id}/likes")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFeedCommentsAction(
        Request $request,
        $id
    ) {
        $result = $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_feed'),
            'FeedLikeService.getAuthor',
            ['feed' => $id]
        );

        $likes = $result['result'];

        $view = new View($likes);

        return $view;
    }

    /**
     * like a post.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("feeds/{id}/likes")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postFeedLikeAction(
        Request $request,
        $id
    ) {
        $myUserId = $this->getUserId();

        $params = array(
            'feed' => $id,
            'user' => $myUserId,
        );

        $result = $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_feed'),
            'FeedLikeService.create',
            $params
        );

        $data = array(
            'id' => $result['result'],
        );

        return new View($data, 201);
    }

    /**
     * unlike a post.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("feeds/{id}/likes")
     * @Method({"DELETE"})
     */
    public function feedUnlikeAction(
        Request $request,
        $id
    ) {
        $myUserId = $this->getUserId();

        $params = array(
            'feed' => $id,
            'user' => $myUserId,
        );

        $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_feed'),
            'FeedLikeService.remove',
            $params
        );
    }
}
