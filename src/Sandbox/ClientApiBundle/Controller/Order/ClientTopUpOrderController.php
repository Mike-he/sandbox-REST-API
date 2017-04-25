<?php

namespace Sandbox\ClientApiBundle\Controller\Order;

use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Offline\OfflineTransfer;
use Sandbox\ApiBundle\Entity\Offline\OfflineTransferAttachment;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Order\TopUpOrder;
use Sandbox\ApiBundle\Form\Offline\OfflineTransferPost;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Rest controller for Client TopUpOrders.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientTopUpOrderController extends PaymentController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="status",
     *     array=true,
     *     default=null,
     *     strict=true
     * )
     *
     * @Route("/topup/transfers")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMyTopUpOrderTransfersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $status = $paramFetcher->get('status');
        $status = !empty($status) ? $status : null;

        $transfers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Offline\OfflineTransfer')
            ->getTopupTransfersForClient(
                $userId,
                $status
            );

        return new View($transfers);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/topup/transfers/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMyTopUpOrderTransferAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $userId = $this->getUserId();
        $transfer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Offline\OfflineTransfer')
            ->findOneBy(array(
                'id' => $id,
                'userId' => $userId,
                'type' => OfflineTransfer::TYPE_TOPUP,
            ));

        return new View($transfer);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/topup/transfers/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteMyTopUpOrderTransferAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $userId = $this->getUserId();
        $transfer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Offline\OfflineTransfer')
            ->findOneBy(array(
                'id' => $id,
                'userId' => $userId,
                'type' => OfflineTransfer::TYPE_TOPUP,
            ));

        if (is_null($transfer)) {
            return new View();
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($transfer);
        $em->flush();

        return new View();
    }

    /**
     * Get all orders for current user.
     *
     * @Get("/topup/orders/my")
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Offset of page"
     * )
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getUserTopUpOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $orders = $this->getRepo('Order\TopUpOrder')->findBy(
            ['userId' => $userId],
            ['creationDate' => 'DESC'],
            $limit,
            $offset
        );

        $view = new View($orders);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_order']));

        return $view;
    }

    /**
     * @Post("/topup/orders")
     *
     * @Annotations\QueryParam(
     *    name="price",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="top up price"
     * )
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     */
    public function payTopUpAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $requestContent = json_decode($request->getContent(), true);
        $channel = $requestContent['channel'];

        if (!array_key_exists('price', $requestContent)) {
            $price = $paramFetcher->get('price');
        } else {
            $price = $requestContent['price'];
        }

        $token = '';
        $smsId = '';
        $smsCode = '';
        $openId = null;

        if (is_null($price) || empty($price)) {
            return $this->customErrorView(
                400,
                self::NO_PRICE_CODE,
                self::NO_PRICE_MESSAGE
            );
        }

        if ($channel == ProductOrder::CHANNEL_OFFLINE) {
            $letter = self::TOPUP_ORDER_LETTER_HEAD;
            $date = round(microtime(true) * 1000);
            $serverId = $this->getGlobal('server_order_id');
            $orderNumber = $letter.$date.rand(0, 9).$serverId;

            return $this->payByOffline(
                $orderNumber,
                $price
            );
        }

        $orderNumber = $this->getOrderNumber(self::TOPUP_ORDER_LETTER_HEAD);

        if ($channel == ProductOrder::CHANNEL_WECHAT_PUB) {
            $wechat = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:ThirdParty\WeChat')
                ->findOneBy(
                    [
                        'userId' => $this->getUserId(),
                        'loginFrom' => ThirdPartyOAuthWeChatData::DATA_FROM_WEBSITE,
                    ]
                );
            $this->throwNotFoundIfNull($wechat, self::NOT_FOUND_MESSAGE);

            $openId = $wechat->getOpenId();
        }

        $charge = $this->payForOrder(
            $token,
            $smsId,
            $smsCode,
            $orderNumber,
            $price,
            $channel,
            TopUpOrder::PAYMENT_SUBJECT,
            $this->getUserId(),
            $openId
        );

        $charge = json_decode($charge, true);

        return new View($charge);
    }

    /**
     * Update Transfe.
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/topup/{id}/transfer")
     * @Method({"PUT"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function updateTransferAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();

        $transfer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Offline\OfflineTransfer')
            ->findOneBy(array('id' => $id, 'userId' => $userId));

        if (is_null($transfer)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }

        $transferStatus = $transfer->getTransferStatus();
        if ($transferStatus != OfflineTransfer::STATUS_UNPAID &&
            $transferStatus != OfflineTransfer::STATUS_RETURNED
        ) {
            return $this->customErrorView(
                400,
                self::WRONG_ORDER_STATUS_CODE,
                self::WRONG_ORDER_STATUS_MESSAGE
            );
        }

        $form = $this->createForm(new OfflineTransferPost(), $transfer);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            return $this->customErrorView(
                400,
                self::INVALID_FORM_CODE,
                self::INVALID_FORM_MESSAGE
            );
        }

        $attachmentArray = $transfer->getAttachments();
        if (empty($attachmentArray)) {
            return new View();
        }

        $em = $this->getDoctrine()->getManager();

        $transferAttachments = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Offline\OfflineTransferAttachment')
            ->findBy(array('transfer' => $transfer));

        foreach ($transferAttachments as $transferAttachment) {
            $em->remove($transferAttachment);
        }

        $attachment = new OfflineTransferAttachment();
        $attachment->setContent($attachmentArray[0]['content']);
        $attachment->setAttachmentType($attachmentArray[0]['attachment_type']);
        $attachment->setFilename($attachmentArray[0]['filename']);
        $attachment->setPreview($attachmentArray[0]['preview']);
        $attachment->setSize($attachmentArray[0]['size']);
        $attachment->setTransfer($transfer);
        $em->persist($attachment);

        $transfer->setTransferStatus(OfflineTransfer::STATUS_PENDING);

        $em->flush();

        return new View();
    }

    /**
     * @Get("/topup/orders/{orderNumber}")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getOneTopUpOrderByOrderNumberAction(
        Request $request,
        $orderNumber
    ) {
        $userId = $this->getUserId();

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\TopUpOrder')
            ->findOneBy(
                [
                    'orderNumber' => $orderNumber,
                    'userId' => $userId,
                ]
            );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $view = new View($order);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_order']));

        return $view;
    }

    /**
     * @Get("/topup/orders/{id}")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getOneTopUpOrderByIdAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\TopUpOrder')
            ->findOneBy(
                [
                    'id' => $id,
                    'userId' => $userId,
                ]
            );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $view = new View($order);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_order']));

        return $view;
    }

    /**
     * @param $orderNumber
     * @param $price
     *
     * @return View
     */
    private function payByOffline(
        $orderNumber,
        $price
    ) {
        $em = $this->getDoctrine()->getManager();

        $transfer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Offline\OfflineTransfer')
            ->findOneBy(array('orderNumber' => $orderNumber));

        if (!is_null($transfer)) {
            return new View();
        }

        $transfer = new OfflineTransfer();
        $transfer->setOrderNumber($orderNumber);
        $transfer->setType(OfflineTransfer::TYPE_TOPUP);
        $transfer->setPrice($price);
        $transfer->setUserId($this->getUserId());
        $em->persist($transfer);

        $em->flush();

        return new View(array(
            'transfer_id' => $transfer->getId(),
        ));
    }
}
