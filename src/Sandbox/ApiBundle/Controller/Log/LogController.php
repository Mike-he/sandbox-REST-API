<?php

namespace Sandbox\ApiBundle\Controller\Log;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Form\Log\LogType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class LogController.
 */
class LogController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/logs")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postLogAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $log = new Log();

        $form = $this->createForm(new LogType(), $log);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($log);
        $em->flush();

        return new View(array(
            'id' => $log->getId(),
        ));
    }
}
