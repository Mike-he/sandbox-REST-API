<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Customer;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class ClientCustomerController extends SandboxRestController
{
    /**
     * Post Admin Message.
     *
     * @param Request $request
     *
     * @Route("/customers/message")
     * @Method({"POST"})
     *
     * @return View
     */
    public function PostMessageAction(
        Request $request
    ) {
        $user = $this->getUser();

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($user->getUserId());

        $xmppUser = $user->getXmppUsername();

        $content = json_decode($request->getContent(), true);

        $txt = (string) $content['txt'];

        $this->get('sandbox_api.jmessage_commnue')->sendTxtMessage($xmppUser, $txt);

        return new View(null, 201);
    }
}
