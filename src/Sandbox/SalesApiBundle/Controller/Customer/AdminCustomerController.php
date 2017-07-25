<?php

namespace Sandbox\SalesApiBundle\Controller\Customer;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\User\UserCustomer;
use Sandbox\ApiBundle\Entity\User\UserGroupHasUser;
use Sandbox\ApiBundle\Form\User\UserCustomerPatchType;
use Sandbox\ApiBundle\Form\User\UserCustomerType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminCustomerController extends SalesRestController
{
    const ERROR_CUSTOMER_EXIST_CODE = 400001;
    const ERROR_CUSTOMER_EXIST_MESSAGE = 'Customer exist';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by id"
     * )
     *
     * @Route("/open/customers")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getOpenUsersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $ids = empty($paramFetcher->get('id')) ? null : $paramFetcher->get('id');

        $customers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->searchCustomers(
                $ids
            );

        $imageUrl = $this->getParameter('image_url');
        // hide phone code
        foreach ($customers as &$customer) {
            $customer['phone'] = $customer['phone'] ? substr_replace($customer['phone'], '****', 3, 4) : $customer['phone'];

            if (!$customer['avatar'] && $customer['user_id']) {
                $customer['avatar'] = $imageUrl.'/person/'.$customer['user_id'].'/avatar_small.jpg';
            }
        }

        return new View($customers);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/customers")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postCustomerAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $customer = new UserCustomer();

        $form = $this->createForm(new UserCustomerType(), $customer);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $phoneCode = $customer->getPhoneCode();
        $phone = $customer->getPhone();

        $customerOrigin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array(
                'phoneCode' => $phoneCode,
                'phone' => $phone,
                'companyId' => $salesCompanyId,
            ));

        if ($customerOrigin) {
            return $this->customErrorView(
                400,
                self::ERROR_CUSTOMER_EXIST_CODE,
                self::ERROR_CUSTOMER_EXIST_MESSAGE
            );
        }

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy(array(
                'phoneCode' => $phoneCode,
                'phone' => $phone,
            ));

        if ($user) {
            $customer->setUserId($user->getId());
        }

        $customer->setCompanyId($salesCompanyId);

        $em = $this->getDoctrine()->getManager();
        $em->persist($customer);
        $em->flush();

        return new View(array('id' => $customer->getId()), 201);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/customers/{id}/phone")
     * @Method({"POST"})
     *
     * @return View
     */
    public function switchCustomersPhoneAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($id);
        $this->throwNotFoundIfNull($customer, self::NOT_FOUND_MESSAGE);

        $data = json_decode($request->getContent(), true);
        $phoneCode = $data['phone_code'];
        $phone = $data['phone'];

        if (!$phoneCode || !$phone) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $customerOrigin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array(
                'phoneCode' => $phoneCode,
                'phone' => $phone,
                'companyId' => $salesCompanyId,
            ));
        if ($customerOrigin) {
            return $this->customErrorView(
                400,
                self::ERROR_CUSTOMER_EXIST_CODE,
                self::ERROR_CUSTOMER_EXIST_MESSAGE
            );
        }
        $em = $this->getDoctrine()->getManager();

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy(array(
                'phoneCode' => $phoneCode,
                'phone' => $phone,
            ));

        $userId = $user ? $user->getId() : null;

        // update user groups
        $userGroupUsers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
            ->findBy(array(
                'customerId' => $customer->getId(),
            ));
        foreach ($userGroupUsers as $user) {
            $user->setUserId($userId);
        }

        $customer->setPhoneCode($phoneCode);
        $customer->setPhone($phone);
        $customer->setUserId($userId);
        $em->flush();

        return new View(array(
            'id' => $customer->getId(),
        ));
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/customers/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchCustomerAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($id);
        $this->throwNotFoundIfNull($customer, self::NOT_FOUND_MESSAGE);

        $customerJson = $this->container->get('serializer')->serialize($customer, 'json');
        $patch = new Patch($customerJson, $request->getContent());
        $customerJson = $patch->apply();

        $form = $this->createForm(new UserCustomerPatchType(), $customer);
        $form->submit(json_decode($customerJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many admins to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Annotations\QueryParam(
     *     name="query",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="group_id",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     requirements="\d+",
     *     strict=true
     * )
     *
     * @Route("/customers")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getCustomersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $query = $paramFetcher->get('query');
        $groupId = $paramFetcher->get('group_id');

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $customers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->getSalesAdminCustomers(
                $salesCompanyId,
                $query,
                $groupId,
                $pageLimit,
                $pageIndex
            );

        foreach ($customers as $customer) {
            $this->generateCustomer($customer);
        }

        if ($pageIndex && $pageLimit) {
            $count = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->getSalesAdminCustomers(
                    $salesCompanyId,
                    $query,
                    $groupId,
                    null,
                    null,
                    true
                );

            $customers = [
                'items' => $customers,
                'total_count' => $count,
            ];
        }

        return new View($customers);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/customers/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getCustomerAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array(
                'id' => $id,
                'companyId' => $salesCompanyId,
            ));
        $this->throwNotFoundIfNull($customer, self::NOT_FOUND_MESSAGE);

        $this->generateCustomer($customer);

        $userId = $customer->getUserId();
        if ($userId) {
            $user = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->find($userId);

            $customer->setCardNo($user->getCardNo());
        }

        return new View($customer);
    }

    /**
     * @param $customer
     */
    private function generateCustomer(
        $customer
    ) {
        /** @var UserCustomer $customer */
        $groupBinds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
            ->findBy(array(
                'customerId' => $customer->getId(),
            ));

        $customerGroupArray = [];
        foreach ($groupBinds as $bind) {
            /** @var UserGroupHasUser $bind */
            $group = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserGroup')
                ->find($bind->getGroupId());

            array_push($customerGroupArray, [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'type' => $group->getType(),
            ]);
        }

        $customer->setGroups($customerGroupArray);
    }
}
