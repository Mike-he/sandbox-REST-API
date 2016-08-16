<?php

namespace Sandbox\ApiBundle\Controller\Feature;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Feature\Feature;
use Sandbox\ApiBundle\Entity\User\UserClient;
use Sandbox\ClientApiBundle\Entity\Auth\ClientApiAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations;
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
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     *
     *  @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(
     *     name="app",
     *     array=false,
     *     default="sandbox",
     *     nullable=false
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
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $app = $paramFetcher->get('app');

        // get features
        $features = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Feature\Feature')
            ->findBy(array(
                'app' => $app,
            ));

        if (!$this->isAuthProvided()) {
            return new View($features);
        }

        $user = $this->getUser();
        $role = $user->getRoles();
        $clientId = $user->getClientId();

        if (array(ClientApiAuth::ROLE_CLIENT_API) != $role || is_null($clientId)) {
            return new View(array());
        }

        $client = $this->getRepo('User\UserClient')->find($clientId);
        if (is_null($client)) {
            return new View(array());
        }

        // get globals
        $globals = $this->getGlobals();

        $clientOSName = strtolower($client->getName());
        $currentClientVersion = $client->getVersion();

        // get standard version
        if (UserClient::CLIENT_IOS == $clientOSName) {
            $standardClientVersion = $globals['client_version_ios'];
        } elseif (UserClient::CLIENT_ANDROID == $clientOSName) {
            $standardClientVersion = $globals['client_version_android'];
        }

        // generate coffee url
        $coffeeUrl = $this->getFeatureURLByVersion(
            $currentClientVersion,
            $standardClientVersion
        );

        foreach ($features as $feature) {
            if (Feature::FEATURE_FOOD == $feature->getName()) {
                $feature->setUrl($coffeeUrl);
            }
        }

        return new View($features);
    }

    /**
     * @param $currentClientVersion
     * @param $standardClientVersion
     *
     * @return string
     */
    private function getFeatureURLByVersion(
        $currentClientVersion,
        $standardClientVersion
    ) {
        $currentVersion = $this->generateVersion($currentClientVersion);
        $standardVersion = $this->generateVersion($standardClientVersion);

        if ($currentVersion >= $standardVersion) {
            $feature = $this->getRepo('Feature\Feature')->findOneByName(Feature::FEATURE_COFFEE);
            if (is_null($feature)) {
                $url = null;
            }

            $url = $feature->getUrl();
        } else {
            $feature = $this->getRepo('Feature\Feature')->findOneByName(Feature::FEATURE_FORWARD);
            if (is_null($feature)) {
                $url = null;
            }

            $url = $feature->getUrl();
        }

        return $url;
    }

    /**
     * @param $version
     *
     * @return array
     */
    private function generateVersion(
        $version
    ) {
        $versionArray = explode('.', $version);
        $length = count($versionArray);

        $result = 0;
        for ($i = 0; $i < $length; ++$i) {
            $result += $versionArray[$i] * pow(10, 4 - 2 * $i);
        }

        return $result;
    }
}
