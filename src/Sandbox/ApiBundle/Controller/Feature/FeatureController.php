<?php

namespace Sandbox\ApiBundle\Controller\Feature;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

/**
 * Feature Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class FeatureController extends SandboxRestController
{
    /**
     * List all features.
     *
     * @param Request $request the request object
     *
     *  @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Method({"GET"})
     * @Route("/features")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getFeaturesAction(
        Request $request
    ) {
        $features = $this->getRepo('Feature\Feature')->findAll();

        return new View($features);
    }
}
