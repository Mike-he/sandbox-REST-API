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

        $lists = $this->getRepo('User\UserPhoneCode')->getPhoneCodeByLanguage($language);

        return new View($lists);
    }
}
