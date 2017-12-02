<?php

namespace Sandbox\ClientApiBundle\Controller\Feed;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Feed\FeedCommentController;
use Sandbox\ApiBundle\Entity\Feed\FeedComment;
use Sandbox\ApiBundle\Form\Feed\FeedCommentType;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Traits\FeedNotification;

/**
 * Manipulate the comments of a feed.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class ClientFeedCommentController extends FeedCommentController
{
    use FeedNotification;

    /**
     * Get all comments of a given feed.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * @param int                   $id           the feed id
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many feeds to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="last_id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="last id"
     * )
     *
     * @Route("/feeds/{id}/comments")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getFeedCommentsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');

        $feed = $this->getRepo('Feed\Feed')->find($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $comments = $this->getRepo('Feed\FeedComment')->getComments(
            $id,
            $limit,
            $lastId
        );

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
                'feed_id' => $comment->getFeedId(),
                'author' => $authorProfile,
                'payload' => $comment->getPayload(),
                'creation_date' => $comment->getCreationDate(),
                'reply_to_user' => $replyToUser,
            );

            array_push($commentsResponse, $comment_array);
        }

        $view = new View($commentsResponse);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['feed']));

        return $view;
    }

    /**
     * post a comment for a given feed.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/feeds/{id}/comments")
     * @Method({"POST"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function postFeedCommentAction(
        Request $request,
        $id
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        $feed = $this->getRepo('Feed\Feed')->find($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        // get request payload
        $comment = new FeedComment();

        $form = $this->createForm(new FeedCommentType(), $comment);
        $form->handleRequest($request);
        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $payload = $comment->getPayload();
        if (is_null($payload)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
        if (!is_string($payload)) {
            // in case the client send a non-string object, e.g. json
            $payload = json_encode($payload);
        }

        // get reply to user
        $replyToUserId = $comment->getReplyToUserId();
        $replyToUser = !is_null($replyToUserId) ? $this->getRepo('User\User')->find($replyToUserId) : null;

        // set comment
        $comment->setFeed($feed);
        $comment->setAuthor($myUser);
        $comment->setCreationdate(new \DateTime('now'));

        if (!is_null($replyToUser)) {
            $comment->setReplyToUserId($replyToUserId);
        } else {
            $comment->setReplyToUserId(null);
        }

        // save to db
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        // send notification
        $recvUsers = array();

        $owner = $feed->getOwner();
        if ($myUser != $owner) {
            $recvUsers[] = $owner;
        }

        if (!is_null($replyToUser) && $replyToUser != $owner) {
            $recvUsers[] = $replyToUser;
        }

        if (!empty($recvUsers)) {
            $this->sendXmppFeedNotification(
                $feed, $myUser, $recvUsers, 'comment', $comment
            );
        }

        // set view
        $view = new View();
        $view->setData(array(
            'id' => $comment->getId(),
            'creationDate' => $comment->getCreationDate(),
        ));

        return $view;
    }

    /**
     * delete comment.
     *
     * @param Request $request   the request object
     * @param int     $id        id of the feed
     * @param int     $commentId id of the comment
     *
     * @Route("/feeds/{id}/comments/{commentId}")
     * @Method({"DELETE"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function deleteFeedCommentAction(
        Request $request,
        $id,
        $commentId
    ) {
        $feed = $this->getRepo('Feed\Feed')->find($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        // get comment by id and commentId
        $comment = $this->getRepo('Feed\FeedComment')->findOneBy(array(
            'id' => $commentId,
            'feedId' => $id,
        ));
        $this->throwNotFoundIfNull($comment, self::NOT_FOUND_MESSAGE);

        // if user is not the author of this comment
        if ($this->getUserId() != $comment->getAuthorId()) {
            throw new BadRequestHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // delete from db
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();

        return new View();
    }
}
