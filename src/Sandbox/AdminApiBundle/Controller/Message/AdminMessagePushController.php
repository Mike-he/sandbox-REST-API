<?php

namespace Sandbox\AdminApiBundle\Controller\Message;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Message\MessagePush;
use Sandbox\ApiBundle\Form\Message\MessagePushType;
use Sandbox\ApiBundle\Traits\MessagePushNotification;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminMessagePushController extends AdminMessageController
{
    use MessagePushNotification;

    /**
     * Get Message List.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many messages to return per page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Route("/push/messages")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getMessagesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $messages = $this->getRepo('Message\MessagePush')->getMessageList();

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $messages,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/push/messages")
     * @Method("POST")
     *
     * @return View
     */
    public function postMessagePushAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $messagePush = new MessagePush();

        $form = $this->createForm(new MessagePushType(), $messagePush);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $messagePush->setAdminId($this->getAdminId());

        $em = $this->getDoctrine()->getManager();
        $em->persist($messagePush);
        $em->flush();

        // send message to all client
        $this->sendXmppMessagePushNotification($messagePush->getContent());

        return new View();
    }
}
