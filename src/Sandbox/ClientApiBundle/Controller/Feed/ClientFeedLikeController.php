<?php

namespace Sandbox\ClientApiBundle\Controller\Feed;

use Sandbox\ApiBundle\Controller\Feed\FeedLikeController;
use Sandbox\ApiBundle\Entity\Feed\FeedLike;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Feed\Feed;

/**
 * Manipulate the likes of a feed.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientFeedLikeController extends FeedLikeController
{
    /**
     * like a post.
     *
     * @param Request $request
     *
     * @Route("feeds/{id}/likes")
     * @Method({"POST"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function postFeedLikeAction(
        Request $request,
        $id
    ) {
        $feed = $this->getRepo('Feed\Feed')->find($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $myUserId = $this->getUserId();

        // get like
        $like = $this->getRepo('Feed\FeedLike')->findOneBy(array(
            'feedId' => $id,
            'authorId' => $myUserId,
        ));

        if (is_null($like)) {
            // create like
            $like = $this->createLike($feed, $myUserId);
        }

        $result = array(
            'id' => $like->getId(),
        );

        return new View($result);
    }

    /**
     * unlike a post.
     *
     * @param Request $request
     *
     * @Route("feeds/{id}/likes")
     * @Method({"DELETE"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function feedUnlikeAction(
        Request $request,
        $id
    ) {
        $feed = $this->getRepo('Feed\Feed')->find($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $myUserId = $this->getUserId();

        // get like
        $like = $this->getRepo('Feed\FeedLike')->findOneBy(array(
            'feedId' => $id,
            'authorId' => $myUserId,
        ));

        if (!is_null($like)) {
            // remove like
            $this->removeLike($myUserId, $like);
        }

        return new View();
    }

    /**
     * @param Feed   $feed
     * @param string $myUserId
     *
     * @return FeedLike
     */
    private function createLike(
        $feed,
        $myUserId
    ) {
        // set new like
        $like = new FeedLike();
        $like->setFeed($feed);
        $like->setAuthor($this->getRepo('User\User')->find($myUserId));
        $like->setCreationDate(new \DateTime('now'));

        // save to db
        $em = $this->getDoctrine()->getManager();
        $em->persist($like);
        $em->flush();

        return $like;
    }

    /**
     * @param int      $myUserId
     * @param FeedLike $like
     */
    private function removeLike(
        $myUserId,
        $like
    ) {
        // if user is not the author of this like
        if ($myUserId != $like->getAuthorId()) {
            throw new BadRequestHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // delete from db
        $em = $this->getDoctrine()->getManager();
        $em->remove($like);
        $em->flush();
    }
}
