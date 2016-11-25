<?php

namespace Sandbox\ApiBundle\Controller\User;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;

class UserPhoneCodeController extends SandboxRestController
{
    const LANGUAGE_ZH = 'zh';
    const LANGUAGE_EN = 'en';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/phonecode/lists")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getPhoneCodeListAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $language = $request->getPreferredLanguage(array(
            self::LANGUAGE_ZH,
            self::LANGUAGE_EN,
        ));

        $lists = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserPhoneCode')
            ->getPhoneCodeByLanguage($language);

        return new View($lists);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/phonecode/admin_login")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getPhoneCodeAdminLoginAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $codes = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->getPhoneCodes();

        return new View($codes);
    }
}
