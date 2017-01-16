<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyWithdrawals;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesCompanyWithdrawalPostType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Shop\ShopController;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Admin Withdrawal Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminCompanyWithdrawalController extends PaymentController
{
    /**
     * @param Request               $request
     * @param $id
     *
     * @Method({"POST"})
     * @Route("/companies/{id}/withdrawals")
     *
     * @return View
     */
    public function postSalesCompanyWithdrawalAction(
        Request $request,
        $id
    ) {
        // check user permission
        $adminId = $this->getAdminId();
        $this->checkAdminWithdrawPermission($adminId, AdminPermission::OP_LEVEL_EDIT);

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $id,
                'banned' => false
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $withdrawal = new SalesCompanyWithdrawals();
        $form = $this->createForm(new SalesCompanyWithdrawalPostType(), $withdrawal);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        //TODO: check withdrawal limit
        $amount = $withdrawal->getAmount();

        $error = $this->handleWithdrawalPost(
            $company,
            $withdrawal,
            $adminId
        );

        if (!empty($error) && !is_null($error)) {
            return $this->customErrorView(
                400,
                $error['code'],
                $error['message']
            );
        }

        // add log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_FINANCE,
            'logAction' => Log::ACTION_CREATE,
            'logObjectKey' => Log::OBJECT_WITHDRAWAL,
            'logObjectId' => $withdrawal->getId(),
        ));
        
        // set view
        $view = new View();
        $view->setStatusCode(201);
        $view->setData(array(
            'id' => $withdrawal->getId(),
        ));

        return $view;
    }

    /**
     * @param SalesCompany $company
     * @param SalesCompanyWithdrawals $withdrawal
     * @param int $adminId
     * @return array
     */
    private function handleWithdrawalPost(
        $company,
        $withdrawal,
        $adminId
    ) {
        // get bank info
        $account = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileAccount')
            ->findOneBy(['salesCompany' => $company]);
        if (is_null($account)) {
            return $this->setErrorArray(
                self::COMPANY_PROFILE_ACCOUNT_INCOMPLETE_CODE,
                self::COMPANY_PROFILE_ACCOUNT_INCOMPLETE_MESSAGE
            );
        }

        $companyName = $account->getSalesCompanyName();
        $bankName = $account->getBankAccountName();
        $accountNumber = $account->getBankAccountNumber();

        if (empty($companyName) || empty($bankName) || empty($accountNumber)) {
            return $this->setErrorArray(
                self::COMPANY_PROFILE_ACCOUNT_INCOMPLETE_CODE,
                self::COMPANY_PROFILE_ACCOUNT_INCOMPLETE_MESSAGE
            );
        }

        $withdrawal->setSalesCompany($company);
        $withdrawal->setSalesCompanyName($companyName);
        $withdrawal->setBankAccountName($bankName);
        $withdrawal->setBankAccountNumber($accountNumber);
        $withdrawal->setSalesAdminId($adminId);

        $em = $this->getDoctrine()->getManager();
        $em->persist($withdrawal);
        $em->flush();
    }

    /**
     * @param $adminId
     * @param $level
     */
    private function checkAdminWithdrawPermission(
        $adminId,
        $level
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_WITHDRAWAL,
                ),
            ),
            $level
        );
    }
}
