<?php

namespace Sandbox\ClientApiBundle\Controller\Food;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Food\Food;
use Sandbox\ApiBundle\Entity\Food\FoodOrder;
use Sandbox\ApiBundle\Entity\Food\FoodOrderPost;
use Sandbox\ApiBundle\Entity\Food\FoodItem;
use Sandbox\ApiBundle\Entity\Food\FoodItemOption;
use Sandbox\ApiBundle\Form\Food\FoodOrderType;
use Sandbox\ApiBundle\Form\Food\FoodItemType;
use Sandbox\ApiBundle\Form\Food\FoodItemOptionType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
class ClientFoodOrderController extends PaymentController
{
    const LOCATION_CANNOT_NULL = 'City, Building cannot be null';
    const PAYMENT_SUBJECT = 'SANDBOX3-饮品/糕点';
    const PAYMENT_BODY = 'FOOD ORDER';
    const FOOD_ORDER_LETTER_HEAD = 'F';

    /**
     * Get food orders for current user.
     *
     *@Annotations\QueryParam(
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
     * @Route("/food/orders")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserFoodOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $orders = $this->getRepo('Order\FoodOrder')->findBy(
            ['userId' => $userId],
            null,
            $limit,
            $offset
        );

        return new View($orders);
    }

    /**
     * @param Request $request
     *
     * @Route("/food/orders")
     * @Method({"POST"})
     *
     * @return View
     */
    public function createFoodOrderAction(
        Request $request
    ) {
        // bind FoodOrderPost Form
        $orderPost = new FoodOrderPost();
        $orderPostForm = $this->createForm(new FoodOrderType(), $orderPost);
        $orderPostForm->handleRequest($request);
        if (!$orderPostForm->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // check location exists
        $roomCity = $this->getRepo('Room\RoomCity')->find($orderPost->getCityId());
        $roomBuilding = $this->getRepo('Room\RoomBuilding')->find($orderPost->getBuildingId());
        if (is_null($roomCity) || is_null($roomBuilding)) {
            throw new BadRequestHttpException(self::LOCATION_CANNOT_NULL);
        }

        // create food order
        return $this->handleFoodOrderCreation(
            $orderPostForm,
            $orderPost
        );
    }

    /**
     * @param Request $request
     *
     * @Route("/food/orders/{orderNumber}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getOneTopUpOrderAction(
        Request $request,
        $orderNumber
    ) {
        $order = $order = $this->getRepo('Order\FoodOrder')->findOneBy(
            ['orderNumber' => $orderNumber]
        );
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }

        return new View($order);
    }

    /**
     * @param $orderPostForm
     * @param $orderPost
     *
     * @return View|FoodOrder
     */
    private function handleFoodOrderCreation(
        $orderPostForm,
        $orderPost
    ) {
        $finalArray = [];
        $calculatedPrice = 0;
        foreach ($orderPostForm['items']->getData() as $item) {

            // bind FoodItem Form
            $foodItem = new FoodItem();
            $foodItemForm = $this->createForm(new FoodItemType(), $foodItem);
            $foodItemForm->submit($item, true);
            if (!$foodItemForm->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            // check food exists
            $food = $this->getRepo('Food\Food')->find($foodItem->getFoodId());
            if (is_null($food)) {
                throw new NotFoundHttpException(self::FOOD_DOES_NOT_EXIST_MESSAGE);
            }

            $formArray = [];
            if ($food->getCategory() == Food::CATEGORY_DESSERT) {
                $quantity = $foodItem->getQuantity();
                $inventory = $food->getInventory();
                if (is_null($quantity) || empty($quantity) || $inventory == 0) {
                    throw new NotFoundHttpException(self::FOOD_SOLD_OUT_MESSAGE);
                }

                // check inventory
                if ($inventory - $quantity < 0) {
                    throw new NotFoundHttpException(self::FOOD_SOLD_OUT_MESSAGE);
                }

                // get dessert price
                $eachPrice = $food->getPrice();
                $price = $quantity * $eachPrice;
                $calculatedPrice += $price;

                // set array
                $foodOptionArray = $food->jsonDessert() + $foodItem->jsonSerialize();
            } else {
                // get forms array
                $formArray = $this->getArrayForForms(
                    $food
                );

                // get chosen options
                $optionArray = [];
                foreach ($foodItemForm['options']->getData() as $option) {
                    // bind FoodItemOption Form
                    $foodItemOption = new FoodItemOption();
                    $foodItemOptionForm = $this->createForm(new FoodItemOptionType(), $foodItemOption);
                    $foodItemOptionForm->submit($option, true);
                    if (!$foodItemOptionForm->isValid()) {
                        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                    }

                    $foodOption = $this->getRepo('Food\FoodFormOption')->find($foodItemOption->getOptionId());
                    if (is_null($foodOption)) {
                        throw new NotFoundHttpException(self::FOOD_OPTION_DOES_NOT_EXIST_MESSAGE);
                    }

                    // get drink price
                    $quantity = $foodItemOption->getQuantity();
                    $eachPrice = $foodOption->getPrice();
                    $price = $quantity * $eachPrice;
                    $calculatedPrice += $price;

                    // set array
                    array_push($optionArray, $foodOption->jsonSerialize() + $foodItemOption->jsonSerialize());
                }
                $foodOptionArray = ['chosen_options' => $optionArray];
            }

            // get attachments array
            $attachmentArray = $this->getArrayForAttachments($food);

            // append arrays
            $itemArray = $food->jsonSerialize() + $attachmentArray + $formArray + $foodOptionArray;
            array_push($finalArray, $itemArray);
        }

        $totalPrice = $orderPost->getTotalPrice();
        // check price
        if ($calculatedPrice != $totalPrice) {
            throw new BadRequestHttpException(self::PRICE_MISMATCH_MESSAGE);
        }

        // check channel
        $channel = $orderPost->getChannel();
        if (
            $channel !== self::PAYMENT_CHANNEL_ALIPAY_WAP &&
            $channel !== self::PAYMENT_CHANNEL_UPACP &&
            $channel !== self::PAYMENT_CHANNEL_UPACP_WAP &&
            $channel !== self::PAYMENT_CHANNEL_ACCOUNT &&
            $channel !== self::PAYMENT_CHANNEL_WECHAT &&
            $channel !== self::PAYMENT_CHANNEL_ALIPAY
        ) {
            throw new BadRequestHttpException(self::WRONG_CHANNEL_MESSAGE);
        }

        // create food order with unpaid status
        $orderNumber = $this->getOrderNumber(self::FOOD_ORDER_LETTER_HEAD);
        $userId = $this->getUserId();
        $order = $this->setFoodOrderWithUnpaidStatus(
            $userId,
            $orderPost,
            $orderNumber,
            $totalPrice,
            $finalArray
        );

        // pay by account
        if ($channel === self::PAYMENT_CHANNEL_ACCOUNT) {
            return $this->payByAccount(
                $order
            );
        } else {
            $charge = $this->payForOrder(
                $orderNumber,
                $totalPrice,
                $channel,
                self::PAYMENT_SUBJECT,
                self::PAYMENT_BODY
            );
            $charge = json_decode($charge, true);

            return new View($charge);
        }
    }

    /**
     * @param $food
     * @param array $formArray
     *
     * @return array
     */
    private function getArrayForForms(
        $food,
        $formArray = []
    ) {
        $forms = [];
        $foodForms = $this->getRepo('Food\FoodForm')->findBy(['food' => $food]);
        if (!empty($foodForms)) {
            foreach ($foodForms as $foodForm) {
                // get all options array
                $allOptions = [];
                $options = $this->getRepo('Food\FoodFormOption')->findBy(['form' => $foodForm]);
                if (!empty($options)) {
                    foreach ($options as $option) {
                        array_push($allOptions, $option->jsonSerialize());
                    }
                }

                $ItemOptions = ['options' => $allOptions];
                array_push($forms, $foodForm->jsonSerialize() + $ItemOptions);
            }
            $formArray = ['forms' => $forms];
        }

        return $formArray;
    }

    /**
     * @param $food
     *
     * @return array
     */
    private function getArrayForAttachments(
       $food
    ) {
        $foodAttachments = [];
        $attachmentArray = [];
        $attachments = $this->getRepo('Food\FoodAttachment')->findBy(['food' => $food]);
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                array_push($foodAttachments, $attachment->jsonSerialize());
            }
            $attachmentArray = ['attachments' => $foodAttachments];
        }

        return $attachmentArray;
    }

    /**
     * @param $userId
     * @param $price
     * @param $orderNumber
     *
     * @return View
     */
    private function payByAccount(
        $order
    ) {
        $balance = $this->postBalanceChange(
            $order->getUserId(),
            (-1) * $order->getTotalPrice(),
            $order->getOrderNumber(),
            self::PAYMENT_CHANNEL_ACCOUNT,
            $order->getTotalPrice()
        );

        if (is_null($balance)) {
            throw new BadRequestHttpException(self::INSUFFICIENT_FUNDS_MESSAGE);
        }

        $orderId = $this->updateFoodOrderStatus($order);

        return new View(['id' => $orderId]);
    }

    /**
     * @param $userId
     * @param $orderPost
     * @param $orderNumber
     * @param $totalPrice
     * @param $finalArray
     *
     * @return FoodOrder
     */
    private function setFoodOrderWithUnpaidStatus(
        $userId,
        $orderPost,
        $orderNumber,
        $totalPrice,
        $finalArray
    ) {
        $order = new FoodOrder();
        $order->setUserId($userId);
        $order->setCityId($orderPost->getCityId());
        $order->setBuildingId($orderPost->getBuildingId());
        $order->setOrderNumber($orderNumber);
        $order->setTotalPrice($totalPrice);
        $order->setFoodInfo(json_encode($finalArray));
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        return $order;
    }
}
