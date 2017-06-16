<?php

namespace Sandbox\AdminApiBundle\Controller\Message;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Traits\OpenfireApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class AdminMessageHistoryController extends AdminMessagePushController
{
    use OpenfireApi;

    /**
     * Get History Message.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="fromJID",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="toJID",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description=""
     * )
     *
     * @Route("/messages/service_history_message")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getHistoryMessageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_VIEW);

        $fromJID = $paramFetcher->get('fromJID');
        $toJID = $paramFetcher->get('toJID');
        $type = $paramFetcher->get('type');

        $fromJID = '"'.$fromJID.'"';
        $toJID = '"'.$toJID.'"';
        $type = '"'.$type.'"';

        $message = $this->getHistoryMessage($fromJID, $toJID, $type);

        return new View($message);
    }
}