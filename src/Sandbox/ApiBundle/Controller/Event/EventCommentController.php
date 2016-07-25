<?php

namespace Sandbox\ApiBundle\Controller\Event;

use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\SalesApiBundle\Controller\SalesRestController;

/**
 * Event Controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class EventCommentController extends SalesRestController
{
    /**
     * @param $comments
     *
     * @return array
     */
    protected function setEventCommentsExtra(
        $comments
    ) {
        $commentsResponse = array();

        foreach ($comments as $comment) {
            $authorId = $comment->getAuthorId();
            if (is_null($authorId)) {
                continue;
            }

            $authorProfile = $this->getRepo('User\UserProfile')->findOneByUserId($authorId);
            if (is_null($authorProfile) || empty($authorProfile)) {
                continue;
            }

            $replyToUserId = $comment->getReplyToUserId();
            $replyToUser = null;
            if (!is_null($replyToUserId)) {
                $replyToUser = $this->getRepo('User\UserProfile')->findOneByUserId($replyToUserId);
            }

            $comment_array = array(
                'id' => $comment->getId(),
                'event_id' => $comment->getEventId(),
                'author' => $authorProfile,
                'payload' => $comment->getPayload(),
                'reply_to_user' => $replyToUser,
                'creation_date' => $comment->getCreationDate(),
            );

            array_push($commentsResponse, $comment_array);
        }

        return $commentsResponse;
    }
}
