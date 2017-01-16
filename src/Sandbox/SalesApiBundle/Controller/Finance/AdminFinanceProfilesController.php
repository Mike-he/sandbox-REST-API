<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfileAccount;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesFinanceProfileAccountPatchType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesFinanceProfileAccountPostType;
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
        $account = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileAccount')
            ->find($id);
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
}
