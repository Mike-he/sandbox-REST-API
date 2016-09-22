<?php

namespace Sandbox\AdminApiBundle\Controller\Auth;

use Sandbox\AdminApiBundle\Controller\Traits\HandleAdminLoginDataTrait;
use Sandbox\ApiBundle\Controller\Auth\AuthController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Admin Auth controller.
 *
 * @category Sandbox
 *
 * @author   Albert Feng <albert.f@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminAuthController extends AuthController
{
    use HandleAdminLoginDataTrait;

    /**
     * Token auth.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/me")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminAuthMeAction(
        Request $request
    ) {
        $payload = json_decode($request->getContent(), true);

        if (!isset($payload['platform'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $salesCompanyId = null;
        if (isset($payload['sales_company_id'])) {
            $salesCompanyId = $payload['sales_company_id'];
        }

        $permissions = $this->getRepo('Admin\AdminPermission')
            ->findAdminPermissionsByAdminAndPositions(
                $this->getAdminId(),
                $payload['platform'],
                $salesCompanyId
            );

        // response
        return new View(
            $this->handlePermissionData($permissions)
        );
    }

    /**
     * GET positions of platform when admin refresh login page.
     *
     * @Route("/platform")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdminAuthPlatformAction()
    {
        $myAdminId = $this->getAdminId();

        $positions = $this->getRepo('Admin\AdminPositionUserBinding')
            ->findPositionByAdmin($myAdminId);

        // response
        $view = new View();

        return $view->setData(
            array(
                'platform' => $this->handlePositionData($positions),
            )
        );
    }
}
