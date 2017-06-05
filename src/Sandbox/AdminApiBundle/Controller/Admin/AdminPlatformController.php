<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPlatform;
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
     * @Route("/platform")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdminPlatformAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();

        return new View($adminPlatform);
    }

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
        $user = $this->getUser()->getMyUser();
        $clientId = $this->getUser()->getClientId();
        $client = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserClient')->find($clientId);

        $data = json_decode($request->getContent(), true);

        // check data validation
        if (!isset($data['platform']) || is_null($data['platform'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $platform = $data['platform'];

        $adminPlatform = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPlatform')
            ->findOneBy(array(
                'user' => $user,
                'client' => $client,
            ));

        if (is_null($adminPlatform)) {
            $adminPlatform = new AdminPlatform();
        }

        $adminPlatform->setUser($user);
        $adminPlatform->setClient($client);
        $adminPlatform->setPlatform($platform);
        $adminPlatform->setCreationDate(new \DateTime('now'));

        if ($platform == AdminPermission::PERMISSION_PLATFORM_OFFICIAL) {
            $salesCompany = null;
        } else {
            if (!isset($data['sales_company_id'])) {
                return $this->customErrorView(
                    400,
                    self::ERROR_INVALID_SALES_COMPANY_ID_CODE,
                    self::ERROR_INVALID_SALES_COMPANY_ID_MESSAGE
                );
            }

            $salesCompanyId = $data['sales_company_id'];
            $salesCompany = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
                ->find($salesCompanyId);
            $this->throwNotFoundIfNull($salesCompany, self::NOT_FOUND_MESSAGE);
        }

        $adminPlatform->setSalesCompany($salesCompany);

        $em = $this->getDoctrine()->getManager();
        $em->persist($adminPlatform);
        $em->flush();

        return new View(array(
            'id' => $adminPlatform->getId(),
        ));
    }
}
