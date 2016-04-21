<?php
namespace Sandbox\ClientApiBundle\Controller\User;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Form\User\UserClientType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;

class ClientUserClientController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/client")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchUserClientAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $now = new \DateTime('now');

        // get client id by token
        $clientId = $this->getUser()->getClientId();

        $client = $this->getRepo('User\UserClient')->find($clientId);
        $this->throwNotFoundIfNull($client, self::NOT_FOUND_MESSAGE);

        $clientJson = $this->container->get('serializer')->serialize($client, 'json');
        $patch = new Patch($clientJson, $request->getContent());
        $clientJson = $patch->apply();

        // bind form
        $form = $this->createForm(new UserClientType(), $client);
        $form->submit(json_decode($clientJson, true));

        $client->setIpAddress($request->getClientIp());
        $client->setModificationDate($now);

        // insert data
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/client")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserClientAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // get client id by token
        $clientId = $this->getUser()->getClientId();

        $client = $this->getRepo('User\UserClient')->find($clientId);
        $this->throwNotFoundIfNull($client, self::NOT_FOUND_MESSAGE);

        return new View($client);
    }
}
