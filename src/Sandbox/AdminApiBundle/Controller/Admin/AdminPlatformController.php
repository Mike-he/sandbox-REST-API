<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminPlatformController extends AdminRestController
{
    const COOKIE_NAME_PLATFORM = 'platform';
    const COOKIE_NAME_SALES_COMPANY = 'sales_company_id';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/platform_set")
     * @Method({"POST"})
     *
     * @return View
     */
    public function setAdminPlatformCookieAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = json_decode($request->getContent(), true);

        // check data validation
        if (!isset($data['platform']) || is_null($data['platform'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $platform = $data['platform'];
        if ($platform != AdminPermission::PERMISSION_PLATFORM_OFFICIAL
            && !isset($data['sales_company_id'])
        ) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $salesCompanyId = $data['sales_company_id'];

        // set cookies
        setrawcookie(self::COOKIE_NAME_PLATFORM, $platform, null, '/', $request->getHost());
        setrawcookie(self::COOKIE_NAME_SALES_COMPANY, $salesCompanyId, null, '/', $request->getHost());

        return new View(array(
            'platform' => $platform,
            'sales_company_id' => $salesCompanyId,
        ));
    }
}
