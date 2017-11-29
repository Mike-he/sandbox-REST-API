<?php

namespace Sandbox\AdminApiBundle\Controller\Event;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Controller\Event\EventCommentController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;

/**
 * Class AdminEventCommentController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminEventCommentController extends EventCommentController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number"
     * )
     *
     * @Route("/events/{id}/comments")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getEventCommentsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permission
        $this->checkAdminEventCommentPermission(AdminPermission::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $event = $this->getRepo('Event\Event')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        $eventId = $event->getId();

        $comments = $this->getRepo('Event\EventComment')->getAdminEventComments(
            $eventId
        );

        $commentsResponse = $this->setEventCommentsExtra($comments);

        $response = $this->get('serializer')->serialize(
            $commentsResponse,
            'json',
            SerializationContext::create()->setGroups(['admin_event'])
        );
        $response = json_decode($response, true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $response,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/events/comments/{id}")
     * @Method("DELETE")
     *
     * @return View
     */
    public function deleteAdminEventCommentAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminEventCommentPermission(AdminPermission::OP_LEVEL_EDIT);

        $comment = $this->getRepo('Event\EventComment')->find($id);
        $this->throwNotFoundIfNull($comment, self::NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();

        return new View();
    }

    /**
     * @param $opLevel
     */
    private function checkAdminEventCommentPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_EVENT],
                ['key' => AdminPermission::KEY_COMMNUE_PLATFORM_EVENT],
            ],
            $opLevel
        );
    }
}
