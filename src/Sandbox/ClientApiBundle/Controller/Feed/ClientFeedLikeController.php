<?php

namespace Sandbox\ClientApiBundle\Controller\Feed;

use Sandbox\ApiBundle\Controller\Feed\FeedLikeController;
use Sandbox\ApiBundle\Entity\Feed\FeedLike;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Manipulate the likes of a feed
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class ClientFeedLikeController extends FeedLikeController
{
    const BAD_PARAM_MESSAGE = "Bad parameters";

    const NOT_FOUND_MESSAGE = "This resource does not exist";

    const NOT_ALLOWED_MESSAGE = "You are not allowed to perform this action";

    /**
     * Get all the likes of a given feed
     *
     * @param Request $request contains request info
     * @param string  $id      id of the feed
     *
     * @Get("/feeds/{id}/likes")
     * @return array
     */
    public function getFeedLikesAction(
        Request $request,
        $id
    ) {
        $username = $this->getUsername();

        // check user's permission
        $feed = $this->getRepo('Feed')->findOneById($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $this->throwAccessDeniedIfNotCompanyMember($feed->getParentid(), $username);

        $likes = $this->getRepo('FeedLike')->findByFid($id);

        return new View($likes);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Post("/feeds/{id}/likes")
     * @return View
     * @throws BadRequestHttpException
     */
    public function postFeedLikeAction(
        Request $request,
        $id
    ) {
        $username = $this->getUsername();

        // check user's permission
        $feed = $this->getRepo('Feed')->findOneById($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $this->throwAccessDeniedIfNotCompanyMember($feed->getParentid(), $username);

        // get like
        $like = $this->getRepo('FeedLike')->findOneBy(array(
            'fid' => $id,
            'authorid' => $username,
        ));

        if (is_null($like)) {
            // create like
            $like = $this->createLike($id, $username);
        }

        // set view
        $view = new View();
        $view->setData(array(
            'id' => $like->getId(),
        ));

        return $view;
    }

    /**
     * @param Request $request the request object
     * @param int     $id      id of the feed
     *
     * @Delete("/feeds/{id}/likes")
     * @return View
     * @throws BadRequestHttpException
     */
    public function feedUnlikeAction(
        Request $request,
        $id
    ) {
        $username = $this->getUsername();

        // check user's permission
        $feed = $this->getRepo('Feed')->findOneById($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $this->throwAccessDeniedIfNotCompanyMember($feed->getParentid(), $username);

        // get like by fid and authorid
        $like = $this->getRepo('FeedLike')->findOneBy(array(
            'fid' => $id,
            'authorid' => $username,
        ));

        if (!is_null($like)) {
            // remove like
            $this->removeLike($username, $like);
        }

        return new View();
    }

    /**
     * @param Request $request the request object
     * @param int     $id      id of the feed
     * @param int     $likeId  id of the like
     *
     * @Delete("/feeds/{id}/likes/{likeId}")
     * @return View
     * @throws BadRequestHttpException
     */
    public function deleteFeedLikeAction(
        Request $request,
        $id,
        $likeId
    ) {
        $username = $this->getUsername();

        // check user's permission
        $feed = $this->getRepo('Feed')->findOneById($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $this->throwAccessDeniedIfNotCompanyMember($feed->getParentid(), $username);

        // get like by id and likeId
        $like = $this->getRepo('FeedLike')->findOneBy(array(
            'id' => $likeId,
            'fid' => $id,
        ));
        $this->throwNotFoundIfNull($like, self::NOT_FOUND_MESSAGE);

        // remove like
        $this->removeLike($username, $like);

        return new View();
    }

    /**
     * @param int    $feedId
     * @param string $username
     *
     * @return FeedLike
     */
    private function createLike(
        $feedId,
        $username
    ) {
        // set new like
        $like = new FeedLike();
        $like->setFid($feedId);
        $like->setAuthorid($username);
        $like->setCreationdate(time());

        // save to db
        $em = $this->getDoctrine()->getManager();
        $em->persist($like);
        $em->flush();

        return $like;
    }

    /**
     * @param string   $username
     * @param FeedLike $like
     */
    private function removeLike(
        $username,
        $like
    ) {
        // if user is not the author of this like
        if ($username != $like->getAuthorid()) {
            throw new BadRequestHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // delete from db
        $em = $this->getDoctrine()->getManager();
        $em->remove($like);
        $em->flush();
    }
}
