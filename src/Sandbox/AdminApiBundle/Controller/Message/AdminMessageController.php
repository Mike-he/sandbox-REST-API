<?php

namespace Sandbox\AdminApiBundle\Controller\Message;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Message\Message;
use Sandbox\ApiBundle\Form\Message\MessageType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\View\View;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Sandbox\ApiBundle\Traits\MessageNotification;

/**
 * Admin Message Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminMessageController extends SandboxRestController
{
    use MessageNotification;

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
     * @Route("/messages")
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
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $messages = $this->getRepo('Message\Message')->getMessageList();

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $messages,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Post Message.
     *
     * @param Request $request
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     201 = "Returned when successful created"
     *  }
     * )
     *
     * @Route("/messages")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postMessageAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_EDIT);

        $message = new Message();

        $form = $this->createForm(new MessageType(), $message);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($message);
        $em->flush();

        // send message to all users using sandbox service account
        $this->sendXmppMessageNotification($message->getContent());

        return new View();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    protected function checkAdminMessagePermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_MESSAGE],
            ],
            $opLevel
        );
    }
}
