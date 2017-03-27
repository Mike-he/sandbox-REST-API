<?php

namespace Sandbox\AdminShopApiBundle\Controller\Shop;

use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use Sandbox\ApiBundle\Entity\Shop\ShopSpec;
use Sandbox\ApiBundle\Entity\Shop\ShopSpecItem;
use Sandbox\ApiBundle\Form\Shop\ShopSpecPostType;
use Sandbox\ApiBundle\Form\Shop\ShopSpecPutType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Shop\SpecController;
use Sandbox\AdminShopApiBundle\Data\Shop\ShopMenuData;
use Sandbox\AdminShopApiBundle\Data\Shop\ShopSpecItemData;
use Sandbox\ApiBundle\Form\Shop\ShopSpecItemPostType;
use Sandbox\ApiBundle\Form\Shop\ShopMenuType;
use Sandbox\ApiBundle\Form\Shop\ShopSpecItemModifyType;
use Symfony\Component\HttpFoundation\Response;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Admin Spec Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminShopSpecController extends SpecController
{
    /**
     * @param Request $request
     *
     * @Method({"GET"})
     * @Route("/specs/dropdown")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getShopSpecDropDownAction(
        Request $request
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_PLATFORM_SPEC,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_PRODUCT,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_KITCHEN,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        $specs = $this->getRepo('Shop\ShopSpec')->findBy(
            [
                'companyId' => $this->getCompanyId(),
                'invisible' => false,
            ],
            ['id' => 'ASC']
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($specs);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Method({"GET"})
     * @Route("/specs")
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return "
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
     *    name="search",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="search spec by name"
     * )
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getShopSpecAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_PLATFORM_SPEC,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_PRODUCT,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_KITCHEN,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        $search = $paramFetcher->get('search');

        $specs = $this->getRepo('Shop\ShopSpec')->getSpecsByCompany(
            $this->getCompanyId(),
            $search
        );

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $specs = $this->get('serializer')->serialize(
            $specs,
            'json',
            SerializationContext::create()->setGroups(['admin_shop'])
        );
        $specs = json_decode($specs, true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $specs,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"GET"})
     * @Route("/specs/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getSpecByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_PLATFORM_SPEC,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_PRODUCT,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_KITCHEN,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        $spec = $this->getRepo('Shop\ShopSpec')->findOneBy(
            [
                'id' => $id,
                'companyId' => $this->getCompanyId(),
                'invisible' => false,
                'auto' => false,
            ]
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($spec);

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"DELETE"})
     * @Route("/specs/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteSpecAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_PLATFORM_SPEC,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        $spec = $this->getRepo('Shop\ShopSpec')->findOneBy(
            [
                'id' => $id,
                'companyId' => $this->getCompanyId(),
                'invisible' => false,
                'auto' => false,
            ]
        );
        $this->throwNotFoundIfNull($spec, self::NOT_FOUND_MESSAGE);

        $spec->setInvisible(true);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     *
     * @Method({"POST"})
     * @Route("/specs")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postSpecAction(
        Request $request
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_PLATFORM_SPEC,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        $spec = new ShopSpec();

        $form = $this->createForm(new ShopSpecPostType(), $spec);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleSpecPost(
            $spec
        );
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"PUT"})
     * @Route("/specs/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putSpecAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_PLATFORM_SPEC,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        $companyId = $this->getCompanyId();

        $spec = $this->getRepo('Shop\ShopSpec')->findOneBy(
            [
                'id' => $id,
                'companyId' => $companyId,
                'auto' => false,
            ]
        );
        $this->throwNotFoundIfNull($spec, self::NOT_FOUND_MESSAGE);

        $oldName = $spec->getName();

        $form = $this->createForm(
            new ShopSpecPutType(),
            $spec,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // check conflict shop spec
        if ($oldName !== $spec->getName()) {
            $this->findConflictCompanySpec($spec, $companyId);
        }

        $em = $this->getDoctrine()->getManager();

        $this->handleSpecItemPut(
            $spec,
            $id,
            $em
        );

        $em->flush();

        return new View();
    }

    /**
     * @param $spec
     * @param $id
     * @param $em
     */
    private function handleSpecItemPut(
        $spec,
        $id,
        $em
    ) {
        $items = $spec->getItems();

        if (is_null($items) || empty($items)) {
            return;
        }

        $menuData = new ShopMenuData();

        $form = $this->createForm(new ShopMenuType(), $menuData);
        $form->submit($items, true);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $addData = $menuData->getAdd();
        $modifyData = $menuData->getModify();
        $removeData = $menuData->getRemove();

        // add spec items
        $this->addSpecItem(
            $addData,
            $spec,
            $em
        );

        // modify spec items
        $this->modifySpecItem(
            $modifyData,
            $id
        );

        // remove spec items
        $this->removeSpecItem(
            $removeData,
            $id,
            $em
        );
    }

    /**
     * @param $addData
     * @param $spec
     * @param $em
     */
    private function addSpecItem(
        $addData,
        $spec,
        $em
    ) {
        if (is_null($addData)) {
            return;
        }

        foreach ($addData as $item) {
            $specItem = new ShopSpecItem();

            $form = $this->createForm(new ShopSpecItemPostType(), $specItem);
            $form->submit($item, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $specItem->setSpec($spec);

            $em->persist($specItem);
        }
    }

    /**
     * @param $modifyData
     * @param $specId
     */
    private function modifySpecItem(
        $modifyData,
        $specId
    ) {
        if (is_null($modifyData)) {
            return;
        }

        foreach ($modifyData as $item) {
            $specData = new ShopSpecItemData();

            $form = $this->createForm(new ShopSpecItemModifyType(), $specData);
            $form->submit($item, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            // check if item exists
            $specItem = $this->getRepo('Shop\ShopSpecItem')->findOneBy(
                [
                    'id' => $specData->getId(),
                    'specId' => $specId,
                ]
            );

            // check menu belongs to current spec
            if (is_null($specItem)) {
                continue;
            }

            $specItem->setName($specData->getName());
        }
    }

    /**
     * @param $removeData
     * @param $shopId
     * @param $em
     */
    private function removeSpecItem(
        $removeData,
        $specId,
        $em
    ) {
        if (is_null($removeData)) {
            return;
        }

        foreach ($removeData as $item) {
            $specData = new ShopSpecItemData();

            $form = $this->createForm(new ShopSpecItemModifyType(), $specData);
            $form->submit($item, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            // check if item exists
            $specItem = $this->getRepo('Shop\ShopSpecItem')->findOneBy(
                [
                    'id' => $specData->getId(),
                    'specId' => $specId,
                ]
            );

            // check menu belongs to current spec
            if (is_null($specItem)) {
                continue;
            }

            $em->remove($specItem);
        }
    }

    /**
     * @param $spec
     *
     * @return Response
     */
    private function handleSpecPost(
        $spec
    ) {
        $items = $spec->getItems();

        if (is_null($items)) {
            return;
        }

        $companyId = $this->getCompanyId();
        $spec->setCompanyId($companyId);

        $em = $this->getDoctrine()->getManager();
        $em->persist($spec);

        // check conflict shop spec
        $this->findConflictCompanySpec($spec, $companyId);

        foreach ($items as $item) {
            $specItem = new ShopSpecItem();

            $form = $this->createForm(new ShopSpecItemPostType(), $specItem);
            $form->submit($item, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $specItem->setSpec($spec);

            $em->persist($specItem);
        }

        $em->flush();

        return new View();
    }

    /**
     * @param $spec
     */
    private function findConflictCompanySpec(
        $spec,
        $companyId
    ) {
        $sameSpec = $this->getRepo('Shop\ShopSpec')->findOneBy(
            [
                'companyId' => $companyId,
                'name' => $spec->getName(),
                'invisible' => false,
            ]
        );

        if (!is_null($sameSpec)) {
            throw new ConflictHttpException(ShopSpec::SHOP_SPEC_CONFLICT_MESSAGE);
        }
    }

    /**
     * @return int
     */
    private function getCompanyId()
    {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();

        return $adminPlatform['sales_company_id'];
    }
}
