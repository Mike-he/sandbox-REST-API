<?php

namespace Sandbox\ApiBundle\Controller\FileServer;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Class FileServerController.
 */
class FileServerController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/fileserver")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFileServerUrlAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $domain = $globals['xmpp_domain'];

        return new View(array(
            'file_server_domain' => $domain,
        ));
    }
}
