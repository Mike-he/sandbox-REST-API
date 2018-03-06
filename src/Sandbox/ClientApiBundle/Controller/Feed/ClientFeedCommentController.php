<?php

namespace Sandbox\ClientApiBundle\Controller\Feed;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Feed\FeedCommentController;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClientFeedCommentController extends FeedCommentController
{
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
     *    default="0",
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

        $params = array(
            'feed' => $id,
            'limit' => $limit,
            'offset' => $lastId,
        );

        $result = $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_feed'),
            'FeedCommentService.lists',
            $params
        );

        $comments = $result['result'];

        $commentsResponse = array();

        foreach ($comments as $comment) {
            $authorId = $comment['author'];
            if (is_null($authorId)) {
                continue;
            }

            $authorProfile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneByUserId($authorId);
            if (is_null($authorProfile) || empty($authorProfile)) {
                continue;
            }

            $author = array(
                'user_id' => $authorId,
                'name' => $authorProfile->getName(),
            );

            $replyToUserId = $comment['replyToUserId'];
            $replyToUser = null;
            if (!is_null($replyToUserId)) {
                $replyUser = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:User\UserProfile')
                    ->findOneByUserId($replyToUserId);

                $replyToUser = array(
                    'user_id' => $replyToUserId,
                    'name' => $replyUser->getName(),
                );
            }

            $commentsResponse[] = array(
                'id' => $comment['id'],
                'feed_id' => $comment['feed'],
                'author' => $author,
                'payload' => $comment['payload'],
                'creation_date' => $comment['creationDate'],
                'reply_to_user' => $replyToUser,
            );
        }

        $view = new View($commentsResponse);

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

        $content = json_decode($request->getContent(), true);

        $payload = $content['payload'];
        if (is_null($payload)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
        if (!is_string($payload)) {
            // in case the client send a non-string object, e.g. json
            $payload = json_encode($payload);
        }

        // get reply to user
        $replyToUser = null;
        if (isset($content['reply_to_user_id'])) {
            $replyToUserId = $content['reply_to_user_id'];
            $replyToUser = !is_null($replyToUserId) ? $this->getRepo('User\User')->find($replyToUserId) : null;
        }

        $params = array(
            'feed' => $id,
            'author' => $myUserId,
            'payload' => $payload,
            'reply' => $replyToUser,
        );

        $result = $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_feed'),
            'FeedCommentService.create',
            $params
        );

        $comment = $result['result'];

        // set view
        $view = new View();
        $view->setData($comment, 201);

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
     */
    public function deleteFeedCommentAction(
        Request $request,
        $id,
        $commentId
    ) {
        $params = array(
            'feed' => $id,
            'comment' => $commentId,
            'user' => $this->getUserId(),
        );

        $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_feed'),
            'FeedCommentService.remove',
            $params
        );
    }
}
