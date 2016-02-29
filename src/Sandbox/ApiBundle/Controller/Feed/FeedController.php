<?php

namespace Sandbox\ApiBundle\Controller\Feed;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Feed\Feed;
use Sandbox\ApiBundle\Entity\Feed\FeedView;
use FOS\RestBundle\View\View;

/**
 * Feed Controller.
 *
 * @category Sandbox
 *
 * @author   Josh Yang <josh.yang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
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
        $view->setSerializationContext(SerializationContext::create()->setGroups(['feed']));

        return $view;
    }

    /**
     * @param FeedView $feed
     * @param int      $userId
     */
    protected function setFeed(
        $feed,
        $userId = null
    ) {
        $profile = $this->getRepo('User\UserProfile')->findOneByUserId($feed->getOwnerId());
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);
        $feed->setOwner($profile);

        if (is_null($userId)) {
            return;
        }

        $like = $this->getRepo('Feed\FeedLike')->findOneBy(array(
            'feedId' => $feed->getId(),
            'authorId' => $userId,
        ));

        if (!is_null($like)) {
            $feed->setMyLikeId($like->getId());
        }
    }
}
