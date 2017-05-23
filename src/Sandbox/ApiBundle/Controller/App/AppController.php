<?php

namespace Sandbox\ApiBundle\Controller\App;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * APP Controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AppController extends SandboxRestController
{
    const LANGUAGE_ZH = 'zh';
    const LANGUAGE_EN = 'en';

    const DEVICE_IPHONE = 'iphone';
    const DEVICE_ANDROID = 'android';

    const VERSION_STRING = '{{version}}';

    /**
     * List all APP Info.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     *  @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(
     *    name="version",
     *    default=null,
     *    nullable=true,
     *    description="app version"
     * )
     *
     * @Annotations\QueryParam(
     *    name="app",
     *    default="sandbox",
     *    nullable=true,
     *    description="app key"
     * )
     *
     * @Method({"GET"})
     * @Route("/apps")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAppsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $version = $paramFetcher->get('version');
        $app = $paramFetcher->get('app');

        if (!is_null($version) && !empty($version)) {
            $apps = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:App\AppInfo')
                ->findBy(array(
                    'version' => $version,
                    'app' => $app,
                ));
        } else {
            $apps = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:App\AppInfo')
                ->findBy(array(
                    'app' => $app,
                ));
        }

        return new View($apps);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="version",
     *     nullable=false,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="device",
     *     nullable=false,
     *     strict=true
     * )
     *
     * @Route("app_version_check")
     * @Method({"GET"})
     *
     * @return View
     */
    public function checkAppVersionAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $version = $paramFetcher->get('version');
        $device = $paramFetcher->get('device');

        $versionCheck = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:App\AppVersionCheck')
            ->findOneBy(array(
                'visible' => true,
            ));

        $checkCurrentVersion = $versionCheck->getCurrentVersion();

        if (version_compare($checkCurrentVersion, $version, '<=')) {
            return new View();
        }

        $isForce = $versionCheck->getIsForce();
        $zhNotification = $versionCheck->getZhNotification();
        $zhForceNotification = $versionCheck->getZhForceNotification();
        $enNotification = $versionCheck->getEnNotification();
        $enForceNotification = $versionCheck->getEnForceNotification();
        $iosUrl = $versionCheck->getIosUrl();
        $androidUrl = $versionCheck->getAndroidUrl();

        $zhNotification = preg_replace('/'.self::VERSION_STRING.'/', "$checkCurrentVersion", $zhNotification);
        $zhForceNotification = preg_replace('/'.self::VERSION_STRING.'/', "$checkCurrentVersion", $zhForceNotification);
        $enNotification = preg_replace('/'.self::VERSION_STRING.'/', "$checkCurrentVersion", $enNotification);
        $enForceNotification = preg_replace('/'.self::VERSION_STRING.'/', "$checkCurrentVersion", $enForceNotification);

        $language = $request->getPreferredLanguage(array(
            self::LANGUAGE_ZH,
            self::LANGUAGE_EN,
        ));

        $response = array();
        if ($isForce) {
            $response['is_force'] = $isForce;

            switch ($language) {
                case self::LANGUAGE_ZH:
                    $response['notification'] = $zhForceNotification;
                    break;
                case self::LANGUAGE_EN:
                    $response['notification'] = $enForceNotification;
                    break;
            }
        } else {
            $response['is_force'] = $isForce;

            switch ($language) {
                case self::LANGUAGE_ZH:
                    $response['notification'] = $zhNotification;
                    break;
                case self::LANGUAGE_EN:
                    $response['notification'] = $enNotification;
                    break;
            }
        }

        switch ($device) {
            case self::DEVICE_IPHONE:
                $response['download_url'] = $iosUrl;
                break;
            case self::DEVICE_ANDROID:
                $response['download_url'] = $androidUrl;
                break;
        }

        return new View($response);
    }
}
