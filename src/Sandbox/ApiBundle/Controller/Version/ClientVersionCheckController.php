<?php

namespace Sandbox\ApiBundle\Controller\Version;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class ClientVersionCheckController extends SandboxRestController
{
    const ALERT_MESSAGE = '';
    const UPDATE_REQUIRED_LAST_VERSION = 'update_required_last_version';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="version",
     *     array=false,
     *     nullable=false,
     *     strict=true
     * )
     *
     * @Route("/version_check")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getClientVersionCheckAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $currentClientVersion = $paramFetcher->get('version');

        $defaultResponse = array(
            'update_required' => false,
            'alert_message' => '',
        );

        $updateRequiredLastVersionParameter = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array(
                'key' => self::UPDATE_REQUIRED_LAST_VERSION,
            ));

        if (is_null($updateRequiredLastVersionParameter) || empty($updateRequiredLastVersionParameter->getValue())) {
            return new View($defaultResponse);
        }

        $updateRequiredLastVersion = $updateRequiredLastVersionParameter->getValue();

        if (version_compare($updateRequiredLastVersion, $currentClientVersion, '<=')) {
            return new View(array(
                'update_required' => true,
                'alert_message' => self::ALERT_MESSAGE,
            ));
        }

        return new View($defaultResponse);
    }
}
