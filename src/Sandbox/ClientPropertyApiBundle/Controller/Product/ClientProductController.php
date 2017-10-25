<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Product;

use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\Product\ProductController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Sandbox\ApiBundle\Form\Product\ProductPatchVisibleType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use JMS\Serializer\SerializationContext;

class ClientProductController extends ProductController
{
    /**
     * Product.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by room type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for the page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="start of the page"
     * )
     *
     *
     * @Route("/products")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getProductsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // filters
        $buildingId = $paramFetcher->get('building');
        $type = $paramFetcher->get('type');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_PRODUCT,
            )
        );

        if (empty($myBuildingIds)) {
            return new View();
        }

        $products = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->getProductsForPropertyClient(
                $myBuildingIds,
                $type,
                $buildingId,
                $limit,
                $offset
            );

        $productData = [];
        foreach ($products as $product) {
            $productData[] = $this->handleProductData($product);
        }

        $view = new View();
        $view->setData($productData);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/products/{id}/usage")
     * @Method({"GET"})
     *
     * @Annotations\QueryParam(
     *    name="start",
     *    nullable=false,
     *    description=""
     * )
     *
     *  @Annotations\QueryParam(
     *    name="end",
     *    nullable=false,
     *    description=""
     * )
     *
     * @return View
     */
    public function getRoomUsageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $product = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->find($id);

        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        $startString = $paramFetcher->get('start');
        $endString = $paramFetcher->get('end');

        $start = new \DateTime($startString);
        $start->setTime(0, 0, 0);
        $end = new \DateTime($endString);
        $end->setTime(23, 59, 59);

        $results = $this->generateOrders(
            $product,
            $start,
            $end
        );

        $view = new View();
        $view->setData($results);

        return $view;
    }

    /**
     * Get product by id.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/products/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getProductByIdAction(
        Request $request,
        $id
    ) {
        $product = $this->getRepo('Product\Product')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        $favorite = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserFavorite')
            ->countFavoritesByObject(
                UserFavorite::OBJECT_PRODUCT,
                $id
            );

        $product->setFavorite($favorite);

        $productLeasingSets = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
            ->findBy(array('product' => $product));

        $product->setLeasingSets($productLeasingSets);

        $productRentSet = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductRentSet')
            ->findOneBy(array('product' => $product));

        $product->setRentSet($productRentSet);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_room']));
        $view->setData($product);

        return $view;
    }

    /**
     * Update a product.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     *
     * @Route("/products/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchProductAction(
        Request $request,
        $id
    ) {
        $product = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->findOneBy([
                'id' => $id,
                'isDeleted' => false,
            ]);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        // bind data
        $productJson = $this->container->get('serializer')->serialize($product, 'json');
        $patch = new Patch($productJson, $request->getContent());
        $productJson = $patch->apply();

        $form = $this->createForm(new ProductPatchVisibleType(), $product);
        $form->submit(json_decode($productJson, true));

        $rentDate = $form['earliest_rent_date']->getData();

        if (!is_null($rentDate) && !empty($rentDate)) {
            $rentDate->setTime(0, 0, 0);

            $productRentSet = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\ProductRentSet')
                ->findOneBy(array('product' => $product));

            if ($productRentSet) {
                $productRentSet->setEarliestRentDate($rentDate);
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    private function handleProductData(
        $product
    ) {
        $room = $product->getRoom();
        $building = $room->getBuilding();

        $type = $room->getType();
        $tag = $room->getTypeTag();

        $roomType = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$type);

        $productLeasingSets = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
            ->findBy(array('product' => $product));

        $basePriceArray = [];
        if (!empty($productLeasingSets)) {
            foreach ($productLeasingSets as $productLeasingSet) {
                $unitPrice = $this->get('translator')
                    ->trans(ProductOrderExport::TRANS_ROOM_UNIT.$productLeasingSet->getUnitPrice());

                $basePriceArray[$unitPrice] = $productLeasingSet->getBasePrice();
            }

            if (Room::TYPE_DESK == $type && Room::TAG_DEDICATED_DESK == $tag) {
                $price = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomFixed')
                    ->getFixedSeats($room);
                if (!is_null($price)) {
                    $basePrice = $price;
                }
            } else {
                $pos = array_search(min($basePriceArray), $basePriceArray);
                $basePrice = $basePriceArray[$pos];
                $unitPrice = $pos;
            }
        }

        $productRentSet = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductRentSet')
            ->findOneBy(array(
                'product' => $product,
                'status' => true,
            ));

        if (Room::TYPE_OFFICE == $type && empty($productLeasingSets) && !is_null($productRentSet)) {
            $unitPrice = $this->get('translator')
                ->trans(ProductOrderExport::TRANS_ROOM_UNIT.$productRentSet->getUnitPrice());

            $basePrice = $productRentSet->getBasePrice();
        }

        $result = array(
            'id' => $product->getId(),
            'room_name' => $room->getName(),
            'building_name' => $building->getName(),
            'type' => $roomType,
            'visible' => $product->getVisible(),
            'base_price' => (float) $basePrice,
            'unit_price' => $unitPrice,
            'has_long_rent' => $productRentSet ? true : false,
        );

        $attachment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
            ->findAttachmentsByRoom($room->getId(), 1);

        if (!empty($attachment)) {
            $result['content'] = $attachment[0]['content'];
            $result['preview'] = $attachment[0]['preview'];
        }

        return $result;
    }

    /**
     * @param Product $product
     * @param $start
     * @param $end
     *
     * @return array
     */
    private function generateOrders(
        $product,
        $start,
        $end
    ) {
        $productId = $product->getId();
        $room = $product->getRoom();
        $roomType = $room->getType();
        $roomTypeTag = $room->getTypeTag();

        if (Room::TYPE_DESK == $roomType && 'hot_desk' == $roomTypeTag) {
            $orders = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Order\ProductOrder')
                ->getRoomUsersUsage(
                    $productId,
                    $start,
                    $end
                );

            $orderList = $this->handleFlexibleOrder($orders);
        } else {
            $orders = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Order\ProductOrder')
                ->getRoomUsersUsage(
                    $productId,
                    $start,
                    $end
                );

            $orderList = $this->handleOrders($orders);

            if (Room::TYPE_OFFICE == $roomType) {
                $status = array(
                    Lease::LEASE_STATUS_PERFORMING,
                    Lease::LEASE_STATUS_END,
                    Lease::LEASE_STATUS_MATURED,
                    Lease::LEASE_STATUS_TERMINATED,
                    Lease::LEASE_STATUS_CLOSED,
                );
                $leases = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Lease\Lease')
                    ->getRoomUsersUsage(
                        $productId,
                        $start,
                        $end,
                        $status
                    );

                $leaseList = $this->handleLease($leases);

                $orderList = array_merge($orderList, $leaseList);
            }
        }

        $productDetail['id'] = $productId;
        $productDetail['room_type'] = $roomType;

        if (Room::TYPE_DESK == $roomType) {
            $seats = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomFixed')
                ->findBy(array(
                    'room' => $room->getId(),
                ));

            $productSeats = array();
            foreach ($seats as $seat) {
                $productSeats[] = array(
                    'id' => $seat->getId(),
                    'seat_number' => $seat->getSeatNumber(),
                    'base_price' => (float) $seat->getBasePrice(),
                );
            }

            $productDetail['seats'] = $productSeats;
        }

        $productLeasingSets = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
            ->findBy(array('product' => $productId));

        foreach ($productLeasingSets as $productLeasingSet) {
            $productDetail['leasing_sets'][] = array(
                'base_price' => (float) $productLeasingSet->getBasePrice(),
                'unit_price' => $productLeasingSet->getUnitPrice(),
                'amount' => $productLeasingSet->getAmount(),
            );
        }

        if (Room::TYPE_OFFICE == $roomType) {
            $productRentSet = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\ProductRentSet')
                ->findOneBy(array('product' => $productId, 'status' => true));
            if ($productRentSet) {
                $productDetail['rent_set'] = array(
                    'base_price' => (float) $productRentSet->getBasePrice(),
                    'unit_price' => $productRentSet->getUnitPrice(),
                );
            }
        }

        $result = array(
            'product' => $productDetail,
            'orders' => $orderList,
        );

        return $result;
    }

    /**
     * @param ProductOrder $orders
     *
     * @return array
     */
    private function handleOrders(
        $orders
    ) {
        $result = array();
        foreach ($orders as $order) {
            /** @var ProductOrder $order */
            $invitedPeoples = $order->getInvitedPeople();
            $invited = array();
            foreach ($invitedPeoples as $invitedPeople) {
                $invited[] = array(
                    'user_id' => $invitedPeople->getUserId(),
                );
            }

            $result[] = array(
                'type' => 'order',
                'order_id' => $order->getId(),
                'start_date' => $order->getStartDate(),
                'end_date' => $order->getEndDate(),
                'user' => $order->getUserId(),
                'appointed_user' => $order->getAppointed(),
                'invited_people' => $invited,
                'seat_id' => $order->getSeatId(),
                'order_type' => $order->getType(),
                'status' => $order->getStatus(),
                'pay_channel' => $order->getPayChannel(),
                'customer_id' => $order->getCustomerId(),
                'unit_price' => $order->getUnitPrice(),
            );
        }

        return $result;
    }

    /**
     * @param $orders
     *
     * @return array
     */
    private function handleFlexibleOrder(
        $orders
    ) {
        $result = array();
        foreach ($orders as $order) {
            /** @var ProductOrder $order */
            $invitedPeoples = $order->getInvitedPeople();
            $invited = array();
            foreach ($invitedPeoples as $invitedPeople) {
                $invited[] = array(
                    'user_id' => $invitedPeople->getUserId(),
                );
            }

            $startDate = $order->getStartDate();
            $endDate = $order->getEndDate();
            $user = $order->getUserId();
            $appointed = $order->getAppointed();
            $days = new \DatePeriod(
                $startDate,
                new \DateInterval('P1D'),
                $endDate
            );

            foreach ($days as $day) {
                $result[] = array(
                    'type' => 'order',
                    'order_id' => $order->getId(),
                    'date' => $day->format('Y-m-d'),
                    'user' => $user,
                    'appointed_user' => $appointed,
                    'invited_people' => $invited,
                    'order_type' => $order->getType(),
                    'status' => $order->getStatus(),
                    'pay_channel' => $order->getPayChannel(),
                    'customer_id' => $order->getCustomerId(),
                    'unit_price' => $order->getUnitPrice(),
                );
            }
        }

        return $result;
    }

    /**
     * @param Lease $leases
     *
     * @return array
     */
    private function handleLease(
        $leases
    ) {
        $result = array();
        foreach ($leases as $lease) {
            /* @var Lease $lease */
            $result[] = array(
                'type' => 'lease',
                'lease_id' => $lease->getId(),
                'start_date' => $lease->getStartDate(),
                'end_date' => $lease->getEndDate(),
                'customer_id' => $lease->getLesseeCustomer(),
                'invited_people' => $lease->degenerateInvitedPeople(),
                'status' => $lease->getStatus(),
            );
        }

        return $result;
    }
}
