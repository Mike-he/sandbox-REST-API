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
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Traits\FeedNotification;

class ClientFeedLikeController extends FeedLikeController
{
    use FeedNotification;

    /**
     * Get all likes of a given feed.
     *
     * @param Request $request the request object
     * @param int     $id      the feed id
     *
     * @Route("/feeds/{id}/likes")
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
        $em = $this->getDoctrine()->getManager();

        $feed = $em->getRepository('SandboxApiBundle:Feed\Feed')->find($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $likes = $em->getRepository('SandboxApiBundle:Feed\FeedLike')
            ->getLikes($id);

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
     * @throws \Exception
     *
     * @return View
     */
    public function postFeedLikeAction(
        Request $request,
        $id
    ) {
        $myUserId = $this->getUserId();

        $feed = $this->getRepo('Feed\Feed')->find($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        // get like
        $like = $this->getRepo('Feed\FeedLike')->findOneBy(array(
            'feedId' => $id,
            'authorId' => $myUserId,
        ));

        if (is_null($like)) {
            // create like
            $like = $this->createLike($id, $myUserId);

//            if ($myUser != $feed->getOwner()) {
//                // send notification
//                $this->sendXmppFeedNotification(
//                    $feed, $myUser, array($feed->getOwner()), 'like'
//                );
//            }
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
     * @param int     $id
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
     * @param Feed $feed
     * @param User $myUser
     *
     * @return FeedLike
     */
    private function createLike(
        $feed,
        $myUser
    ) {
        // set new like
        $like = new FeedLike();
        $like->setFeed($feed);
        $like->setAuthor($myUser);
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
