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

    const ERROR_INVALID_SALES_COMPANY_ID_CODE = 400001;
    const ERROR_INVALID_SALES_COMPANY_ID_MESSAGE = 'Invalid Sales Company Id';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/platform_set")
     * @Method({"POST"})
     *
     * @return View
     */
    public function setAdminPlatformSessionAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $topLevelDomain = $this->container->getParameter('top_level_domain');
        ini_set('session.cookie_domain', $topLevelDomain);
        
        if (!isset($_SESSION)) {
            session_start();
        }

        $data = json_decode($request->getContent(), true);

        // check data validation
        if (!isset($data['platform']) || is_null($data['platform'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // clear old sessions
        session_unset();

        // set new sessions
        $platform = $data['platform'];

        if ($platform == AdminPermission::PERMISSION_PLATFORM_OFFICIAL) {
            $salesCompanyId = null;
        } else {
            if (!isset($data['sales_company_id'])) {
                return $this->customErrorView(
                    400,
                    self::ERROR_INVALID_SALES_COMPANY_ID_CODE,
                    self::ERROR_INVALID_SALES_COMPANY_ID_MESSAGE
                );
            }

            $salesCompanyId = $data['sales_company_id'];
            $_SESSION[self::COOKIE_NAME_SALES_COMPANY] = $salesCompanyId;
        }

        // set sessions
        $_SESSION[self::COOKIE_NAME_PLATFORM] = $platform;

        return new View(array(
            'platform' => $platform,
            'sales_company_id' => $salesCompanyId,
        ));
    }
}
