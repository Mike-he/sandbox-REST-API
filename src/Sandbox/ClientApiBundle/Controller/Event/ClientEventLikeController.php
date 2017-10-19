<?php

namespace Sandbox\ClientApiBundle\Controller\Event;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Event\EventLike;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ClientEventLikeController.
 *
 * @category Sandbox
 *
 * @author   Mike He
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class ClientEventLikeController extends SandboxRestController
{
    /**
     * @param Request $request
     * @param $id
     *
     * @Route("events/{id}/likes")
     * @Method({"POST"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function postEventLikeAction(
        Request $request,
        $id
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        $event = $this->getRepo('Event\Event')->find($id);
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        // get like
        $like = $this->getRepo('Event\EventLike')->findOneBy(array(
            'eventId' => $id,
            'authorId' => $myUserId,
        ));

        if (is_null($like)) {
            // create like
            $eventLike = new EventLike();

            $eventLike->setEvent($event);
            $eventLike->setAuthor($myUser);

            // save to db
            $em = $this->getDoctrine()->getManager();
            $em->persist($eventLike);
            $em->flush();

            return $like;
        }

        $result = array(
            'id' => $like->getId(),
        );

        return new View($result);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("events/{id}/likes")
     * @Method({"DELETE"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function eventUnlikeAction(
        Request $request,
        $id
    ) {
        $myUserId = $this->getUserId();

        $event = $this->getRepo('Event\Event')->find($id);
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        // get like
        $like = $this->getRepo('Event\EventLike')->findOneBy(array(
            'eventId' => $id,
            'authorId' => $myUserId,
        ));

        if (!is_null($like)) {
            // if user is not the author of this like
            if ($myUserId != $like->getAuthorId()) {
                throw new BadRequestHttpException(self::NOT_ALLOWED_MESSAGE);
            }

            // remove like
            $em = $this->getDoctrine()->getManager();
            $em->remove($like);
            $em->flush();
        }

        return new View();
    }
}
