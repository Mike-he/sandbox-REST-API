<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfileAccount;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfileExpress;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfileInvoice;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfileLessor;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfiles;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesFinanceProfileAccountPatchType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesFinanceProfileExpressPatchType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesFinanceProfileInvoicePatchType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesFinanceProfilesPatchType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesFinanceProfilesPostType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminFinanceProfilesController extends SalesRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/profiles")
     * @Method("POST")
     *
     * @return View
     */
    public function postFinanceProfilesAccount(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $profile = new SalesCompanyProfiles();

        $form = $this->createForm(new SalesFinanceProfilesPostType(), $profile);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // set sales company
        $profile->setSalesCompanyId($salesCompanyId);

        $em = $this->getDoctrine()->getManager();
        $em->beginTransaction();

        // catch exception
        try {
            $em->persist($profile);
            $em->flush();

            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();

            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        /** @var SalesCompanyProfileAccount $account */
        $account = $profile->getAccount();
        $account->setProfileId($profile->getId());
        $account->setSalesCompanyId($salesCompanyId);
        $em->persist($account);

        /** @var SalesCompanyProfileExpress $express */
        $express = $profile->getExpress();
        $express->setProfileId($profile->getId());
        $express->setSalesCompanyId($salesCompanyId);
        $em->persist($express);

        /** @var SalesCompanyProfileInvoice $invoice */
        $invoice = $profile->getInvoice();
        $invoice->setProfileId($profile->getId());
        $invoice->setSalesCompanyId($salesCompanyId);
        $em->persist($invoice);

        /** @var SalesCompanyProfileLessor $lessor */
        $lessor = $profile->getLessor();
        $lessor->setProfileId($profile->getId());
        $lessor->setSalesCompanyId($salesCompanyId);
        $em->persist($lessor);

        $em->flush();

        return new View(array(
            'id' => $profile->getId(),
        ),
            201
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/profiles")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFinanceProfilesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $profiles = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfiles')
            ->findBy(array(
                'salesCompanyId' => $salesCompanyId,
            ));

        foreach ($profiles as $profile) {
            $account = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileAccount')
                ->findOneBy(array(
                    'profileId' => $profile->getId(),
                ));
            $profile->setAccount($account);

            $express = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileExpress')
                ->findOneBy(array(
                    'profileId' => $profile->getId(),
                ));
            $profile->setExpress($express);

            $invoice = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileInvoice')
                ->findOneBy(array(
                    'profileId' => $profile->getId(),
                ));
            $profile->setInvoice($invoice);

            $lessor = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileLessor')
                ->findOneBy(array(
                    'profileId' => $profile->getId(),
                ));
            $profile->setLessor($lessor);
        }

        return new View($profiles);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/profiles/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFinanceProfileAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $profile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfiles')
            ->findOneBy(array(
                'id' => $id,
                'salesCompanyId' => $salesCompanyId,
            ));
        if (is_null($profile)) {
            return new View();
        }

        $account = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileAccount')
            ->findOneBy(array(
                'profileId' => $profile->getId(),
            ));
        $profile->setAccount($account);

        $express = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileExpress')
            ->findOneBy(array(
                'profileId' => $profile->getId(),
            ));
        $profile->setExpress($express);

        $invoice = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileInvoice')
            ->findOneBy(array(
                'profileId' => $profile->getId(),
            ));
        $profile->setInvoice($invoice);

        return new View($profile);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/finance/profiles/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchFinanceProfileAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $profile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfiles')
            ->findOneBy(array(
                'id' => $id,
                'salesCompanyId' => $salesCompanyId,
            ));
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);

        $profileJson = $this->container->get('serializer')->serialize($profile, 'json');
        $patch = new Patch($profileJson, $request->getContent());
        $profileJson = $patch->apply();

        $form = $this->createForm(new SalesFinanceProfilesPatchType(), $profile);
        $form->submit(json_decode($profileJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->beginTransaction();

        // catch exception
        try {
            $em->flush();

            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();

            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/finance/profiles/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteFinanceProfileAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $profile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfiles')
            ->findOneBy(array(
                'id' => $id,
                'salesCompanyId' => $salesCompanyId,
            ));
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();
        $em->beginTransaction();

        $em->remove($profile);

        $account = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileAccount')
            ->findOneBy(array(
                'profileId' => $profile->getId(),
            ));
        if ($account) {
            $em->remove($account);
        }

        $express = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileExpress')
            ->findOneBy(array(
                'profileId' => $profile->getId(),
            ));
        if ($express) {
            $em->remove($express);
        }

        $invoice = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileInvoice')
            ->findOneBy(array(
                'profileId' => $profile->getId(),
            ));
        if ($invoice) {
            $em->remove($invoice);
        }

        // catch exception
        try {
            $em->flush();

            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();

            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/finance/profiles/account/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchFinanceAccountAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $account = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileAccount')
            ->findOneBy(array(
                'id' => $id,
                'salesCompanyId' => $salesCompanyId,
            ));
        $this->throwNotFoundIfNull($account, self::NOT_FOUND_MESSAGE);

        $accountJson = $this->container->get('serializer')->serialize($account, 'json');
        $patch = new Patch($accountJson, $request->getContent());
        $accountJson = $patch->apply();

        $form = $this->createForm(new SalesFinanceProfileAccountPatchType(), $account);
        $form->submit(json_decode($accountJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->beginTransaction();

        // catch exception
        try {
            $em->flush();

            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();

            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/finance/profiles/express/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchFinanceExpressAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $express = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileExpress')
            ->findOneBy(array(
                'id' => $id,
                'salesCompanyId' => $salesCompanyId,
            ));
        $this->throwNotFoundIfNull($express, self::NOT_FOUND_MESSAGE);

        $expressJson = $this->container->get('serializer')->serialize($express, 'json');
        $patch = new Patch($expressJson, $request->getContent());
        $expressJson = $patch->apply();

        $form = $this->createForm(new SalesFinanceProfileExpressPatchType(), $express);
        $form->submit(json_decode($expressJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->beginTransaction();

        // catch exception
        try {
            $em->flush();

            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();

            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/finance/profiles/invoice/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchFinanceInvoiceAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $invoice = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileInvoice')
            ->findOneBy(array(
                'id' => $id,
                'salesCompanyId' => $salesCompanyId,
            ));
        $this->throwNotFoundIfNull($invoice, self::NOT_FOUND_MESSAGE);

        $invoiceJson = $this->container->get('serializer')->serialize($invoice, 'json');
        $patch = new Patch($invoiceJson, $request->getContent());
        $invoiceJson = $patch->apply();

        $form = $this->createForm(new SalesFinanceProfileInvoicePatchType(), $invoice);
        $form->submit(json_decode($invoiceJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->beginTransaction();

        // catch exception
        try {
            $em->flush();

            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();

            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return new View();
    }
}
