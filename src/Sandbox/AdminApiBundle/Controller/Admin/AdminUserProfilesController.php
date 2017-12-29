<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminProfiles;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesAdminProfilesPostType;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminUserProfilesController extends AdminRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/admin_profiles")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postUserProfilesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminProfile = new SalesAdminProfiles();

        $form = $this->createForm(new SalesAdminProfilesPostType(), $adminProfile);
        $form->handleRequest($request);

        if (!$form->isValid() || is_null($adminProfile->getNickname())) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $userId = $this->getUserId();
        $em = $this->getDoctrine()->getManager();

        $adminProfileOrigin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
            ->findOneBy([
                'userId' => $userId,
                'salesCompanyId' => null,
            ]);

        if (!is_null($adminProfileOrigin)) {
            $adminProfileOrigin->setAvatar($adminProfile->getAvatar());
            $adminProfileOrigin->setNickname($adminProfile->getNickname());

            $adminProfile = $adminProfileOrigin;
        }

        $adminProfile->setUserId($userId);

        $em->persist($adminProfile);

        $em->flush();

        return new View([
            'id' => $adminProfile->getId(),
        ], '201');
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/admin_profiles")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserProfileAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $adminProfile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
            ->findOneBy([
                'userId' => $userId,
                'salesCompanyId' => null,
            ]);

        return new View($adminProfile);
    }
}
