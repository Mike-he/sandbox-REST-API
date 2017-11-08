<?php

namespace Sandbox\ClientApiBundle\Controller\Event;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Event\EventCommentController;
use Sandbox\ApiBundle\Entity\Event\EventComment;
use Sandbox\ApiBundle\Form\Event\EventCommentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ClientEventCommentController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class ClientEventCommentController extends EventCommentController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
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
     * @Route("/events/{id}/comments")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getEventCommentsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');

        $event = $this->getRepo('Event\Event')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
            'visible' => true,
        ));
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        $comments = $this->getRepo('Event\EventComment')->getEventComments(
            $id,
            $limit,
            $lastId
        );

        $commentsResponse = $this->setEventCommentsExtra($comments);

        $view = new View($commentsResponse);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_event']));

        return $view;
    }

    /**
     * Create an event comment.
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/events/{id}/comments")
     * @Method({"POST"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function postEventCommentAction(
        Request $request,
        $id
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        $event = $this->getRepo('Event\Event')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
            'visible' => true,
        ));
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        $comment = new EventComment();

        $form = $this->createForm(new EventCommentType(), $comment);
        $form->handleRequest($request);

        $payload = $comment->getPayload();

        if (!$form->isValid() || is_null($payload)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // get reply to user
        $replyToUserId = $comment->getReplyToUserId();
        $replyToUser = !is_null($replyToUserId) ? $this->getRepo('User\User')->find($replyToUserId) : null;

        // set comment
        $comment->setEvent($event);
        $comment->setAuthor($myUser);

        if (!is_null($replyToUser)) {
            $comment->setReplyToUserId($replyToUserId);
        } else {
            $comment->setReplyToUserId(null);
        }

        // save to db
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        // TODO send notification

        // set view
        $view = new View();
        $view->setData(array(
            'id' => $comment->getId(),
            'creationDate' => $comment->getCreationDate(),
        ));

        return $view;
    }

    /**
     * @param Request $request
     * @param $commentId
     *
     * @Route("/events/comments/{commentId}")
     * @Method({"DELETE"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function deleteEventCommentAction(
        Request $request,
        $commentId
    ) {
        // get comment by commentId
        $comment = $this->getRepo('Event\EventComment')->find($commentId);
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
