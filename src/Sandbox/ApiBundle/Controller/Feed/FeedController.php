<?php

namespace Sandbox\ApiBundle\Controller\Feed;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Feed\Feed;
use FOS\RestBundle\View\View;

class FeedController extends SandboxRestController
{
    /**
     * @param $feeds
     * @param $userId
     *
     * @return array
     */
    protected function handleGetVerifyFeeds(
        $feeds,
        $userId
    ) {
        foreach ($feeds as $feed) {
            $this->setFeed($feed, $userId);
        }

        return $feeds;
    }

    /**
     * @param array $feeds
     * @param int   $userId
     *
     * @return View
     */
    protected function handleGetFeeds(
        $feeds,
        $userId = null
    ) {
        foreach ($feeds as $feed) {
            $this->setFeed($feed, $userId);
        }

        $view = new View($feeds);

        return $view;
    }

    /**
     * @param $feed
     * @param null $userId
     *
     * @return array
     */
    protected function setFeed(
        $feed,
        $userId = null
    ) {
        $userProfile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserProfile')
            ->findOneBy(array('userId' => $feed['owner']));

        $likeId = null;
        if ($userId) {
            $likeIdResult = $this->get('sandbox_rpc.client')->callRpcServer(
                $this->getParameter('rpc_server_feed'),
                'FeedLikeService.getId',
                ['feed' => $feed['id'], 'user' => $userId]
            );

            $likeId = $likeIdResult['result'];
        }

        $feed = array(
            'id' => $feed['id'],
            'content' => $feed['content'],
            'owner' => array(
                'user_id' => $feed['owner'],
                'name' => $userProfile->getName(),
            ),
            'creation_date' => $feed['creationDate'],
            'likes_count' => $feed['likesCount'],
            'comments_count' => $feed['commentsCount'],
            'attachments' => $feed['attachments'],
            'platform' => $feed['platform'],
            'location' => $feed['location'],
            'my_like_id' => $likeId,
        );

        return $feed;
    }
}
