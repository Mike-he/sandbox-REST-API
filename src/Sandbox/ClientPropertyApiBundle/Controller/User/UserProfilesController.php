<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\User;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminProfiles;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesAdminProfilesPostType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserProfilesController extends SalesRestController
{
    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/user/profiles")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postUserProfilesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminProfiles = new SalesAdminProfiles();

        $form = $this->createForm(new SalesAdminProfilesPostType(), $adminProfiles);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $userId = $this->getUserId();
        $company = $adminProfiles->getSalesCompanyId();

        $em = $this->getDoctrine()->getManager();

        // update all admin profiles
        if ($company == 'all') {
            $profiles = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                ->findBy(['userId' => $userId]);

            foreach ($profiles as $profile) {
                $profile->setNickname($adminProfiles->getNickname());
                $profile->setAvatar($adminProfiles->getAvatar());
                $profile->setEmail($adminProfiles->getEmail());
            }
        } else {
            // update one company
            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                ->findOneBy([
                    'userId' => $userId,
                    'salesCompanyId' => $company,
                ]);

            if (is_null($profile)) {
                $profile = new SalesAdminProfiles();
                $em->persist($profile);
            }

            $profile->setNickname($adminProfiles->getNickname());
            $profile->setAvatar($adminProfiles->getAvatar());
            $profile->setEmail($adminProfiles->getEmail());
        }

        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="company",
     *     array=false,
     *     nullable=false,
     *     strict=true
     * )
     *
     * @Route("/user/profiles")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserProfileAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $companyId = $paramFetcher->get('company');

        $userId = $this->getUserId();

        $profile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
            ->findOneBy([
                'userId' => $userId,
                'salesCompanyId' => $companyId,
            ]);

        return new View($profile);
    }
}