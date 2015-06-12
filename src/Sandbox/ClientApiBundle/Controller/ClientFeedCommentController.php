<?php
/**
 * API for comments of feed
 *
 * PHP version 5.3
 *
 * @category Sandbox
 * @package  ApiBundle
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 *
 */
namespace Sandbox\ClientApiBundle\Controller;

use Sandbox\ApiBundle\Controller\FeedCommentController;
use Sandbox\ApiBundle\Entity\FeedComment;
use Sandbox\ApiBundle\Form\FeedCommentType;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Manipulate the comments of a feed
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class ClientFeedCommentController extends FeedCommentController
{
    const BAD_PARAM_MESSAGE = "Bad parameters";

    const NOT_FOUND_MESSAGE = "This resource does not exist";

    const NOT_ALLOWED_MESSAGE = "You are not allowed to perform this action";

    /**
     * Get all the comments of a given feed
     *
     * @param Request $request contains request info
     * @param string  $id      id of the feed
     *
     * @Get("/feeds/{id}/comments")
     * @return array
     */
    public function getFeedCommentsAction(
        Request $request,
        $id
    ) {
        $username = $this->getUsername();

        // check user's permission
        $feed = $this->getRepo('Feed')->findOneById($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $this->throwAccessDeniedIfNotCompanyMember($feed->getParentid(), $username);

        $comments = $this->getRepo('FeedComment')->findByFid($id);

        return new View($comments);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Post("/feeds/{id}/comments")
     * @return View
     * @throws BadRequestHttpException
     */
    public function postFeedCommentAction(
        Request $request,
        $id
    ) {
        $username = $this->getUsername();

        // check user's permission
        $feed = $this->getRepo('Feed')->findOneById($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $this->throwAccessDeniedIfNotCompanyMember($feed->getParentid(), $username);

        // get request payload
        $comment = new FeedComment();

        $form = $this->createForm(new FeedCommentType(), $comment);
        $form->submit(json_decode($request->getContent(), true));
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
        $comment->setFid($id);
        $comment->setAuthorid($username);
        $comment->setPayload($payload);
        $comment->setCreationdate(time());

        // save to db
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        // set view
        $view = new View();
        $view->setData(array(
            'id' => $comment->getId(),
            'creationdate' => $comment->getCreationdate(),
        ));

        return $view;
    }

    /**
     * @param Request $request   the request object
     * @param int     $id        id of the feed
     * @param int     $commentId id of the comment
     *
     * @Delete("/feeds/{id}/comments/{commentId}")
     * @return View
     * @throws BadRequestHttpException
     */
    public function deleteFeedCommentAction(
        Request $request,
        $id,
        $commentId
    ) {
        $username = $this->getUsername();

        // check user's permission
        $feed = $this->getRepo('Feed')->findOneById($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $this->throwAccessDeniedIfNotCompanyMember($feed->getParentid(), $username);

        // get comment by id and commentId
        $comment = $this->getRepo('FeedComment')->findOneBy(array(
            'id' => $commentId,
            'fid' => $id,
        ));
        $this->throwNotFoundIfNull($comment, self::NOT_FOUND_MESSAGE);

        // if user is not the author of this comment
        if ($username != $comment->getAuthorid()) {
            throw new BadRequestHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // delete from db
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();

        return new View();
    }
}
