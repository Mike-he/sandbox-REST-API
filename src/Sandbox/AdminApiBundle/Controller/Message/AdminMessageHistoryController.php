<?php

namespace Sandbox\AdminApiBundle\Controller\Message;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;

class AdminMessageHistoryController extends AdminMessagePushController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/messages/service_authorization")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getServiceAuthorizationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_VIEW);

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy(array(
                'xmppUsername' => 'service',
            ));

        return new View(array(
            'xmpp_username' => 'service',
            'xmpp_code' => $user->getPassword(),
        ));
    }

    /**
     * @param Request $request the request object
     *
     * @Annotations\QueryParam(
     *    name="media_id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="search by tag"
     * )
     *
     * @Route("/messages/media")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMediaAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $mediaId = $paramFetcher->get('media_id');

        $media = $this->get('sandbox_api.jmessage')->getMedia($mediaId);

        $result = $media['body'];

        return new View($result);
    }
}
