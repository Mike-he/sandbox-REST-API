<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfileAccount;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfileExpress;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfileInvoice;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesFinanceProfileAccountPatchType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesFinanceProfileAccountPostType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesFinanceProfileExpressPatchType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesFinanceProfileExpressPostType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesFinanceProfileInvoicePatchType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesFinanceProfileInvoicePostType;
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
     * @Route("/finance/profiles/account")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFinanceAccountAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $salesCompany = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($salesCompanyId);

        $account = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileAccount')
            ->findOneBy(array(
                'salesCompany' => $salesCompany,
            ));

        if (is_null($account)) {
            return new View();
        }

        return new View(array(
            'id' => $account->getId(),
            'sales_company_name' => $account->getSalesCompanyName(),
            'business_scope' => $account->getBusinessScope(),
            'bank_account_name' => $account->getBankAccountName(),
            'bank_account_number' => $account->getBankAccountNumber(),
            'creation_date' => $account->getCreationDate(),
            'modification_date' => $account->getModificationDate(),
        ));
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/profiles/account")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postFinanceAccountAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $account = new SalesCompanyProfileAccount();

        $form = $this->createForm(new SalesFinanceProfileAccountPostType(), $account);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // set sales company
        $salesCompany = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($salesCompanyId);
        $account->setSalesCompany($salesCompany);

        $em = $this->getDoctrine()->getManager();
        $em->beginTransaction();

        // catch exception
        try {
            $em->persist($account);
            $em->flush();

            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();

            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $view = new View(array(
            'id' => $account->getId(),
        ));
        $view->setStatusCode(201);

        return $view;
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
        $adminPlatform = $this->getAdminPlatform();
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
     *
     * @Route("/finance/profiles/express")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFinanceExpressAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $salesCompany = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($salesCompanyId);

        $express = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileExpress')
            ->findOneBy(array(
                'salesCompany' => $salesCompany,
            ));

        if (is_null($express)) {
            return new View();
        }

        return new View(array(
            'id' => $express->getId(),
            'recipient' => $express->getRecipient(),
            'phone' => $express->getPhone(),
            'address' => $express->getAddress(),
            'zip_code' => $express->getZipCode(),
            'creation_date' => $express->getCreationDate(),
            'modification_date' => $express->getModificationDate(),
        ));
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/profiles/express")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postFinanceExpressAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $express = new SalesCompanyProfileExpress();

        $form = $this->createForm(new SalesFinanceProfileExpressPostType(), $express);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // set sales company
        $salesCompany = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($salesCompanyId);
        $express->setSalesCompany($salesCompany);

        $em = $this->getDoctrine()->getManager();
        $em->beginTransaction();

        // catch exception
        try {
            $em->persist($express);
            $em->flush();

            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();

            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $view = new View(array(
            'id' => $express->getId(),
        ));
        $view->setStatusCode(201);

        return $view;
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
        $adminPlatform = $this->getAdminPlatform();
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
     *
     * @Route("/finance/profiles/invoice")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFinanceInvoiceAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $salesCompany = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($salesCompanyId);

        $invoice = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileInvoice')
            ->findOneBy(array(
                'salesCompany' => $salesCompany,
            ));

        if (is_null($invoice)) {
            return new View();
        }

        return new View(array(
            'id' => $invoice->getId(),
            'title' => $invoice->getTitle(),
            'category' => $invoice->getCategory(),
            'taxpayer_id' => $invoice->getTaxpayerId(),
            'address' => $invoice->getAddress(),
            'phone' => $invoice->getPhone(),
            'bank_account_name' => $invoice->getBankAccountName(),
            'bank_account_number' => $invoice->getBankAccountNumber(),
            'creation_date' => $invoice->getCreationDate(),
            'modification_date' => $invoice->getModificationDate(),
        ));
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/profiles/invoice")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postFinanceInvoiceAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $invoice = new SalesCompanyProfileInvoice();

        $form = $this->createForm(new SalesFinanceProfileInvoicePostType(), $invoice);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // set sales company
        $salesCompany = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($salesCompanyId);
        $invoice->setSalesCompany($salesCompany);

        $em = $this->getDoctrine()->getManager();
        $em->beginTransaction();

        // catch exception
        try {
            $em->persist($invoice);
            $em->flush();

            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();

            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $view = new View(array(
            'id' => $invoice->getId(),
        ));
        $view->setStatusCode(201);

        return $view;
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
        $adminPlatform = $this->getAdminPlatform();
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
