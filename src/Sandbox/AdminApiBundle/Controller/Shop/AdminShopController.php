<?php

namespace Sandbox\AdminApiBundle\Controller\Shop;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Shop\ShopController;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use Sandbox\ApiBundle\Entity\Shop\ShopAttachment;
use Sandbox\ApiBundle\Form\Shop\ShopPostType;
use Sandbox\ApiBundle\Form\Shop\ShopPutType;
use Sandbox\ApiBundle\Form\Shop\ShopPatchType;
use Sandbox\ApiBundle\Form\Shop\ShopAttachmentPostType;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Rs\Json\Patch;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * Admin Shop Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xue <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminShopController extends ShopController
{
    /**
     * @param Request $request
     *
     * @Method({"POST"})
     * @Route("/shops")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postShopAction(
        Request $request
    ) {
        $shop = new Shop();

        $form = $this->createForm(new ShopPostType(), $shop);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleShopPost(
            $shop,
            $form
        );
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"PUT"})
     * @Route("/shops/{id}")
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function putShopAction(
        Request $request,
        $id
    ) {
        $shop = $this->findShopById($id);

        $form = $this->createForm(
            new ShopPutType(),
            $shop,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleShopPut(
            $shop,
            $form
        );
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"PATCH"})
     * @Route("/shops/{id}")
     *
     * @return Response
     */
    public function patchShopOnlineAction(
        Request $request,
        $id
    ) {
        $shop = $this->findShopById($id);

        // bind data
        $shopJson = $this->get('serializer')->serialize($shop, 'json');
        $patch = new Patch($shopJson, $request->getContent());
        $shopJson = $patch->apply();

        $form = $this->createForm(new ShopPatchType(), $shop);
        $form->submit(json_decode($shopJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new Response();
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"GET"})
     * @Route("/shops/{id}")
     *
     * @return View
     */
    public function getShopByIdAction(
        Request $request,
        $id
    ) {
        $shop = $this->getRepo('Shop\Shop')->getShopById($id);
        if (empty($shop) || is_null($shop)) {
            throw new NotFoundHttpException(self::NOT_FOUND_MESSAGE);
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($shop);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="Filter by building"
     * )
     *
     * @Method({"GET"})
     * @Route("/shops")
     *
     * @return View
     */
    public function getShopByBuildingAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $buildingId = $paramFetcher->get('building');
        $shop = $this->getRepo('Shop\Shop')->getShopByBuilding($buildingId);
        if (empty($shop) || is_null($shop)) {
            throw new NotFoundHttpException(self::NOT_FOUND_MESSAGE);
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($shop);

        return $view;
    }

    /**
     * @param $shop
     * @param $form
     *
     * @return View
     */
    private function handleShopPost(
        $shop,
        $form
    ) {
        // check building
        $building = $this->getRepo('Room\RoomBuilding')->find($shop->getBuildingId());
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        $existShop = $this->getRepo('Shop\Shop')->findOneByBuilding($building);
        if (!is_null($existShop)) {
            throw new ConflictHttpException(Shop::SHOP_CONFLICT);
        }

        $em = $this->getDoctrine()->getManager();
        $shopAttachments = $form['shop_attachments']->getData();

        // set startHour and endHour
        $this->setHours(
            $shop,
            $form
        );

        // add building
        $shop->setBuilding($building);

        // add shop attachments
        $this->addShopAttachments(
            $shop,
            $shopAttachments,
            $em
        );

        $em->persist($shop);
        $em->flush();

        $view = new View();
        $view->setData(['id' => $shop->getId()]);

        return $view;
    }

    /**
     * @param $shop
     *
     * @return Response
     */
    private function handleShopPut(
        $shop,
        $form
    ) {
        $em = $this->getDoctrine()->getManager();
        $shopAttachments = $form['shop_attachments']->getData();

        // set startHour and endHour
        $this->setHours(
            $shop,
            $form
        );

        // delete shop attachments
        $this->deleteShopAttachments(
            $shop,
            $em
        );

        // add shop attachments
        $this->addShopAttachments(
            $shop,
            $shopAttachments,
            $em
        );

        $shop->setModificationDate(new \DateTime());
        $em->flush();

        return new Response();
    }

    private function deleteShopAttachments(
        $shop,
        $em
    ) {
        $shopAttachments = $this->getRepo('Shop\ShopAttachment')->findByShop($shop);
        if (is_null($shopAttachments) || empty($shopAttachments)) {
            return;
        }

        foreach ($shopAttachments as $shopAttachment) {
            $em->remove($shopAttachment);
        }
    }

    /**
     * @param $shop
     * @param $form
     */
    private function setHours(
        $shop,
        $form
    ) {
        $start = \DateTime::createFromFormat(
            'H:i:s',
            $form['start_hour']->getData()
        );

        $end = \DateTime::createFromFormat(
            'H:i:s',
            $form['end_hour']->getData()
        );

        $shop->setStartHour($start);
        $shop->setEndHour($end);
    }

    /**
     * @param $shop
     * @param $shopAttachments
     * @param $em
     */
    private function addShopAttachments(
        $shop,
        $shopAttachments,
        $em
    ) {
        if (is_null($shopAttachments)) {
            return;
        }

        foreach ($shopAttachments as $attachment) {
            $shopAttachment = new ShopAttachment();
            $form = $this->createForm(new ShopAttachmentPostType(), $shopAttachment);
            $form->submit($attachment, true);

            $shopAttachment->setShop($shop);
            $em->persist($shopAttachment);
        }
    }
}
