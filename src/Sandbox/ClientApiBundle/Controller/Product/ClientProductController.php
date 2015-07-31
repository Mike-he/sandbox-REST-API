<?php

namespace Sandbox\ClientApiBundle\Controller\Product;

use Sandbox\ApiBundle\Controller\Product\ProductController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;

/**
 * Rest controller for Client Product.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientProductController extends ProductController
{
    /**
     * @Get("/products")
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    default=null,
     *    nullable=true,
     *    description="
     *        type of room
     *    "
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    default=null,
     *    nullable=true,
     *    description="
     *        city id
     *    "
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    default=null,
     *    nullable=true,
     *    description="
     *        building id
     *    "
     * )
     *
     * @Annotations\QueryParam(
     *    name="start_time",
     *    default=null,
     *    nullable=true,
     *    description="
     *        rent time
     *    "
     * )
     *
     * @Annotations\QueryParam(
     *    name="time_unit",
     *    default=null,
     *    nullable=true,
     *    description="
     *        month|day|hour
     *    "
     * )
     *
     * @Annotations\QueryParam(
     *    name="rent_period",
     *    default=null,
     *    nullable=true,
     *    description="
     *        rent period
     *    "
     * )
     *
     * @Annotations\QueryParam(
     *    name="allowed_people",
     *    default=null,
     *    nullable=true,
     *    description="
     *        maximum allowed people
     *    "
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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getProductsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $roomType = $paramFetcher->get('type');
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $timeUnit = $paramFetcher->get('time_unit');
        $rentPeriod = $paramFetcher->get('rent_period');
        $allowedPeople = $paramFetcher->get('allowed_people');
        $startTime = $paramFetcher->get('start_time');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $endTime = null;
        $startHour = null;
        $endHour = null;

        if (!is_null($startTime) && !empty($startTime)) {
            $startTime = new \DateTime($startTime);
            $endTime = clone $startTime;
            $endTime->modify('+'.$rentPeriod.$timeUnit);
            $startHour = $startTime->format('H:i:s');
            $endHour = $endTime->format('H:i:s');
        }
        $userId = $this->getUserId();

        $productIds = $this->getRepo('Product\Product')->getProductsForClient(
            $roomType,
            $cityId,
            $buildingId,
            $startTime,
            $endTime,
            $allowedPeople,
            $userId,
            $startHour,
            $endHour,
            $limit,
            $offset
        );

        $products = [];
        foreach ($productIds as $productId) {
            $product = $this->getRepo('Product\Product')->find($productId);
            array_push($products, $product);
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($products);

        return $view;
    }

    /**
     * @Get("/products/{id}")
     *
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function getOneProductAction(
        Request $request,
        $id
    ) {
        return $this->getOneProduct($id);
    }

    /**
     * @Get("/products/{id}/dates")
     *
     * @Annotations\QueryParam(
     *    name="rent_date",
     *    default=null,
     *    nullable=true,
     *    description="
     *        rent date
     *    "
     * )
     *
     * @param Request $request
     * @param $id
     * @param ParamFetcherInterface $paramFetcher
     */
    public function getBookedDatesAction(
        Request $request,
        $id,
        ParamFetcherInterface $paramFetcher
    ) {
        $product = $this->getRepo('Product\Product')->find($id);
        if (is_null($product)) {
            return $this->customErrorView(
                400,
                self::PRODUCT_NOT_FOUND_CODE,
                self::PRODUCT_NOT_FOUND_MESSAGE
            );
        }

        $type = $product->getRoom()->getType();
        $rentDate = $paramFetcher->get('rent_date');
        if ($type === 'meeting' && !is_null($rentDate) && !empty($rentDate)) {
            $startDate = new \DateTime($rentDate);
            $endDate = clone $startDate;
            $endDate->setTime(23, 59, 59);
            $orders = $this->getRepo('Order\ProductOrder')->getTimesByDate(
                $id,
                $startDate,
                $endDate
            );
        } else {
            $orders = $this->getRepo('Order\ProductOrder')->getBookedDates($id);
        }

        $response = [];
        if (empty($orders)) {
            return new View($response);
        }

        foreach ($orders as $order) {
            $startDate = $order->getStartDate();
            $endDate = $order->getEndDate();
            $start = $startDate->getTimeStamp();
            $end = $endDate->getTimeStamp();

            $dates = array(
                'start' => $start,
                'end' => $end,
            );
            array_push($response, $dates);
        }

        return new View($response);
    }
}
