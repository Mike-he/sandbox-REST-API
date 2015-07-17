<?php

namespace Sandbox\ClientApiBundle\Controller\Feed;

use Sandbox\ApiBundle\Controller\Feed\FeedCommentController;
use Sandbox\ApiBundle\Entity\Feed\FeedComment;
use Sandbox\ApiBundle\Form\Feed\FeedCommentType;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Manipulate the comments of a feed.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientFeedCommentController extends FeedCommentController
{
    /**
     * Get all comments of a given feed.
     *
     * @param Request $request
     *
     * @Route("feeds/{id}/comments")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getFeedCommentsAction(
        Request $request,
        $id
    ) {
        $feed = $this->getRepo('Feed\Feed')->find($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $comments = $this->getRepo('Feed\FeedComment')->findByFeedId($id);

        return new View($comments);
    }

    /**
     * post a comment for a given feed.
     *
     * @param Request $request
     *
     * @Route("feeds/{id}/comments")
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

        // set comment
        $comment->setFeedId($id);
        $comment->setAuthorId($this->getUserId());
        $comment->setPayload($payload);
        $comment->setCreationdate(new \DateTime('now'));

        // save to db
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

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
     * @Route("feeds/{id}/comments/{commentId}")
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
