<?php

namespace Sandbox\AdminApiBundle\Controller\SalesAdmin;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;
use Sandbox\ApiBundle\Entity\Shop\ShopAdmin;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminType;
use Sandbox\ApiBundle\Form\Shop\ShopAdminPostType;
use Sandbox\ApiBundle\Form\Shop\ShopAdminPutType;
use Sandbox\ApiBundle\Traits\AdminTrait;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;

/**
 * SalesAdmin controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminShopAdminsController extends SandboxRestController
{
    use AdminTrait;

    const ERROR_USERNAME_INVALID_CODE = 400001;
    const ERROR_USERNAME_INVALID_MESSAGE = 'Invalid username - 无效的用户名';

    const ERROR_USERNAME_EXIST_CODE = 400002;
    const ERROR_USERNAME_EXIST_MESSAGE = 'Username already exist - 用户名已被占用';

    const ERROR_PASSWORD_INVALID_CODE = 400003;
    const ERROR_PASSWORD_INVALID_MESSAGE = 'Invalid password - 无效的密码';

    const ERROR_ADMIN_TYPE_CODE = 400004;
    const ERROR_ADMIN_TYPE_MESSAGE = 'Invalid admin type - 无效的管理员类型';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     201 = "Returned when successful created"
     *  }
     * )
     *
     * @Annotations\QueryParam(
     *    name="username",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="sales admin username"
     * )
     *
     * @Route("/admins/check")
     * @Method({"GET"})
     *
     * @return View
     */
    public function checkAdminUsernameValidAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES,
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        $shopAdminUsername = $paramFetcher->get('username');

        $shopAdmin = $this->getRepo('Shop\ShopAdmin')->findOneByUsername($shopAdminUsername);

        if (!is_null($shopAdmin)) {
            return $this->customErrorView(
                400,
                self::ERROR_USERNAME_EXIST_CODE,
                self::ERROR_USERNAME_EXIST_MESSAGE
            );
        }

        return new View();
    }

    /**
     * List definite id of admin.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Method({"GET"})
     * @Route("/admins/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES,
            AdminPermissionMap::OP_LEVEL_VIEW
        );

        // get sales admin
        $salesAdmin = $this->getRepo('SalesAdmin\SalesAdmin')->find($id);
        $this->throwNotFoundIfNull($salesAdmin, self::NOT_FOUND_MESSAGE);

        // get admin
        $admins = $this->getRepo('Shop\ShopAdmin')->findOneByCompanyId($salesAdmin->getCompanyId());

        // set view
        $view = new View($admins);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('admin'))
        );

        return $view;
    }

    /**
     * Create admin.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Method({"POST"})
     * @Route("/admins")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postAdminsAction(
        Request $request
    ) {
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES,
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        // bind admin data
        $admin = new ShopAdmin();
        $form = $this->createForm(new ShopAdminPostType(), $admin);
        $form->handleRequest($request);

        $type_key = $form['type_key']->getData();
        $companyId = $form['company_id']->getData();

        $company = $this->getRepo('SalesAdmin\SalesCompany')->find($companyId);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        if ($form->isValid()) {
            return $this->handleAdminCreate(
                $admin,
                $type_key,
                $company
            );
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Update Admin.
     *
     * @param Request $request the request object
     * @param int     $id      the admin ID
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     201 = "Returned when successful created"
     *  }
     * )
     *
     *
     * @Route("/admins/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchAdminAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES,
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        $admin = $this->getRepo('Shop\ShopAdmin')->find($id);
        $this->throwNotFoundIfNull($admin, self::NOT_FOUND_MESSAGE);

        $passwordOld = $admin->getPassword();

        // get origin admin hash string
        $adminOriginHash = $this->getHashResult($admin);

        // bind data
        $adminJson = $this->container->get('serializer')->serialize($admin, 'json');
        $patch = new Patch($adminJson, $request->getContent());
        $adminJson = $patch->apply();

        $form = $this->createForm(new ShopAdminPutType(), $admin);
        $form->submit(json_decode($adminJson, true));

        $passwordNew = $admin->getPassword();
        if ($passwordOld != $passwordNew) {
            $admin->setDefaultPasswordChanged(false);
        }

        $type_key = $form['type_key']->getData();

        return $this->handleAdminPatch(
            $admin,
            $type_key,
            $adminOriginHash
        );
    }

    /**
     * @param ShopAdmin     $admin
     * @param ShopAdminType $typeKey
     * @param string        $adminOriginHash
     *
     * @return View
     */
    private function handleAdminPatch(
        $admin,
        $typeKey,
        $adminOriginHash
    ) {
        $em = $this->getDoctrine()->getManager();
        if (!is_null($typeKey)) {
            $type = $this->getRepo('Shop\ShopAdminType')->findOneByKey($typeKey);
            $admin->setTypeId($type->getId());
        }
        $em->persist($admin);

        //save data
        $em->flush();

        $adminNewHash = $this->getHashResult($admin);
        if ($adminOriginHash != $adminNewHash) {
            // logout this admin
            $this->getRepo('ShopAdmin\ShopAdminToken')->deleteShopAdminToken(
                $admin->getId()
            );
        }

        return new View();
    }

    /**
     * @param ShopAdmin     $admin
     * @param ShopAdminType $typeKey
     * @param SalesCompany  $company
     *
     * @return View
     */
    private function handleAdminCreate(
        $admin,
        $typeKey,
        $company
    ) {
        $type = $this->getRepo('Shop\ShopAdminType')->findOneByKey($typeKey);
        $admin->setType($type);
        $admin->setTypeId($type->getId());

        $checkAdminValid = $this->checkAdminValid($admin);
        if (!is_null($checkAdminValid)) {
            return $checkAdminValid;
        }

        // save admin to db
        $admin = $this->saveAdmin($admin, $company);

        // set view
        $view = new View();
        $view->setData(array(
            'id' => $admin->getId(),
        ));

        return $view;
    }

    /**
     * @param ShopAdmin    $admin
     * @param SalesCompany $company
     *
     * @return ShopAdmin
     */
    private function saveAdmin(
        $admin,
        $company
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        // set sales company
        $admin->setSalesCompany($company);

        // set dates
        $admin->setCreationDate($now);
        $admin->setModificationDate($now);

        // save admin
        $em->persist($admin);
        $em->flush();

        return $admin;
    }

    /**
     * @param ShopAdmin $admin
     *
     * @return View
     */
    private function checkAdminValid(
        $admin
    ) {
        // check username
        if (is_null($admin->getUsername())) {
            return $this->customErrorView(
                400,
                self::ERROR_USERNAME_INVALID_CODE,
                self::ERROR_PASSWORD_INVALID_MESSAGE);
        }

        // check username exist
        $adminExist = $this->getRepo('Shop\ShopAdmin')->findOneByUsername($admin->getUsername());
        if (!is_null($adminExist)) {
            return $this->customErrorView(
                400,
                self::ERROR_USERNAME_EXIST_CODE,
                self::ERROR_USERNAME_EXIST_MESSAGE);
        }

        // check password
        if (is_null($admin->getPassword())) {
            return $this->customErrorView(
                400,
                self::ERROR_PASSWORD_INVALID_CODE,
                self::ERROR_PASSWORD_INVALID_MESSAGE);
        }

        // check admin type
        if (is_null($admin->getTypeId())) {
            return $this->customErrorView(
                400,
                self::ERROR_ADMIN_TYPE_CODE,
                self::ERROR_ADMIN_TYPE_MESSAGE);
        } else {
            $type = $this->getRepo('Shop\ShopAdminType')->find($admin->getTypeId());
            if (is_null($type)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_ADMIN_TYPE_CODE,
                    self::ERROR_ADMIN_TYPE_MESSAGE);
            }
        }
    }
}
