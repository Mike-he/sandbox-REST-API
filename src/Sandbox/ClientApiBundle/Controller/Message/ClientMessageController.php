<?php

namespace Sandbox\ClientApiBundle\Controller\Message;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class ClientMessageController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/messages/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMessageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $message = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Message\Message')
            ->findOneBy(array(
                'id' => $id,
                'visible' => true,
            ));
        $this->throwNotFoundIfNull($message, self::NOT_FOUND_MESSAGE);

        return new View($message);
    }
}
