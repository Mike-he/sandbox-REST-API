<?php

namespace Sandbox\ShopApiBundle\Controller\Shop;

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
use Sandbox\ApiBundle\Form\Shop\ShopPatchOnlineType;
use Sandbox\ApiBundle\Form\Shop\ShopPatchCloseType;
use Sandbox\ApiBundle\Form\Shop\ShopPatchActiveType;
use Sandbox\ApiBundle\Form\Shop\ShopAttachmentPostType;
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
            $shop
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
            $shop
        );
    }

    /**
     * patch Online status.
     *
     * @param Request $request
     * @param $id
     *
     * @Method({"PATCH"})
     * @Route("/shops/{id}/online")
     *
     * @return Response
     */
    public function patchShopOnlineAction(
        Request $request,
        $id
    ) {
        $this->patchShop(
            $request,
            $id,
            new ShopPatchOnlineType()
        );

        return new Response();
    }

    /**
     * patch Open/Close status (temporary).
     *
     * @param Request $request
     * @param $id
     *
     * @Method({"PATCH"})
     * @Route("/shops/{id}/close")
     *
     * @return Response
     */
    public function patchShopCloseAction(
        Request $request,
        $id
    ) {
        $this->patchShop(
            $request,
            $id,
            new ShopPatchCloseType()
        );

        return new Response();
    }

    /**
     * patch active status for display and edit.
     *
     * @param Request $request
     * @param $id
     *
     * @Method({"PATCH"})
     * @Route("/shops/{id}/active")
     *
     * @return Response
     */
    public function patchShopActiveAction(
        Request $request,
        $id
    ) {
        $this->patchShop(
            $request,
            $id,
            new ShopPatchActiveType()
        );

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
        $shop
    ) {
        // check building
        $building = $this->getRepo('Room\RoomBuilding')->find($shop->getBuildingId());
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();
        $shopAttachments = $shop->getAttachments();
        $startString = $shop->getStart();
        $endString = $shop->getEnd();

        // set startHour and endHour
        $this->setHours(
            $shop,
            $startString,
            $endString
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
        $shop
    ) {
        $em = $this->getDoctrine()->getManager();
        $shopAttachments = $shop->getAttachments();
        $startString = $shop->getStart();
        $endString = $shop->getEnd();

        // set startHour and endHour
        $this->setHours(
            $shop,
            $startString,
            $endString
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
        $startString,
        $endString
    ) {
        if (
            is_null($startString) ||
            empty($startString) ||
            is_null($endString) ||
            empty($endString)
        ) {
            return;
        }

        $start = \DateTime::createFromFormat(
            'H:i:s',
            $startString
        );

        $end = \DateTime::createFromFormat(
            'H:i:s',
            $endString
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
        if (is_null($shopAttachments) || empty($shopAttachments)) {
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

    /**
     * @param Request $request
     * @param $id
     * @param $type
     *
     * @throws Patch\FailedTestException
     */
    private function patchShop(
        Request $request,
        $id,
        $type
    ) {
        $shop = $this->findShopById($id);

        // bind data
        $shopJson = $this->get('serializer')->serialize($shop, 'json');
        $patch = new Patch($shopJson, $request->getContent());
        $shopJson = $patch->apply();

        $form = $this->createForm($type, $shop);
        $form->submit(json_decode($shopJson, true));

        if (!$shop->isActive()) {
            $shop->setOnline(false);
        }
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }
}
