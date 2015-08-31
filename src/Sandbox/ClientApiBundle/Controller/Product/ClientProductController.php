<?php

namespace Sandbox\ClientApiBundle\Controller\Product;

use Sandbox\ApiBundle\Controller\Product\ProductController;
use Sandbox\ApiBundle\Entity\Room\Room;
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
     * @Get("/products/meeting")
     *
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
     *
     * @Annotations\QueryParam(
     *    name="start",
     *    default=null,
     *    nullable=true,
     *    description="
     *        start time
     *    "
     * )
     *
     *  @Annotations\QueryParam(
     *    name="end",
     *    default=null,
     *    nullable=true,
     *    description="
     *        end time
     *    "
     * )
     *
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
    public function getMeetingProductsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $start = $paramFetcher->get('start');
        $end = $paramFetcher->get('end');
        $allowedPeople = $paramFetcher->get('allowed_people');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $startTime = null;
        $endTime = null;
        $startHour = null;
        $endHour = null;
        if (!is_null($start) && !empty($start)) {
            $startTime = new \DateTime($start);
            $startHour = $startTime->format('H:i:s');
        }
        if (!is_null($end) && !empty($end)) {
            $endTime = new \DateTime($end);
            $endHour = $endTime->format('H:i:s');
        }
        $userId = $this->getUserId();

        $productIds = $this->getRepo('Product\Product')->getMeetingProductsForClient(
            $userId,
            $cityId,
            $buildingId,
            $allowedPeople,
            $startTime,
            $endTime,
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
     * @Get("/products/office")
     *
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
     *    name="start",
     *    default=null,
     *    nullable=true,
     *    description="
     *        start time
     *    "
     * )
     *
     *  @Annotations\QueryParam(
     *    name="end",
     *    default=null,
     *    nullable=true,
     *    description="
     *        end time
     *    "
     * )
     *
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
    public function getOfficeProductsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $start = $paramFetcher->get('start');
        $end = $paramFetcher->get('end');
        $allowedPeople = $paramFetcher->get('allowed_people');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $startDate = null;
        $endDate = null;
        if (!is_null($start) && !is_null($end) && !empty($start) && !empty($end)) {
            $startDate = new \DateTime($start);
            $endDate = new \DateTime($end);
            $endDate->setTime(23, 59, 59);
        }
        $userId = $this->getUserId();

        $productIds = $this->getRepo('Product\Product')->getOfficeProductsForClient(
            $userId,
            $cityId,
            $buildingId,
            $allowedPeople,
            $startDate,
            $endDate,
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
     * @Get("/products/workspace")
     *
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
     *    name="start",
     *    default=null,
     *    nullable=true,
     *    description="
     *        start time
     *    "
     * )
     *
     *  @Annotations\QueryParam(
     *    name="end",
     *    default=null,
     *    nullable=true,
     *    description="
     *        end time
     *    "
     * )
     *
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
    public function getWorkspaceProductsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $start = $paramFetcher->get('start');
        $end = $paramFetcher->get('end');
        $allowedPeople = $paramFetcher->get('allowed_people');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $startDate = null;
        $endDate = null;
        if (!is_null($start) && !is_null($end) && !empty($start) && !empty($end)) {
            $startDate = new \DateTime($start);
            $endDate = new \DateTime($end);
            $endDate->setTime(23, 59, 59);
        }
        $userId = $this->getUserId();

        $productIds = $this->getRepo('Product\Product')->getWorkspaceProductsForClient(
            $userId,
            $cityId,
            $buildingId,
            $allowedPeople,
            $startDate,
            $endDate,
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
     * @Annotations\QueryParam(
     *    name="month_start",
     *    default=null,
     *    nullable=true,
     *    description="
     *        start date
     *    "
     * )
     *
     * @Annotations\QueryParam(
     *    name="month_end",
     *    default=null,
     *    nullable=true,
     *    description="
     *        end date
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

        $response = [];
        $type = $product->getRoom()->getType();
        $rentDate = $paramFetcher->get('rent_date');
        if ($type == Room::TYPE_MEETING && !is_null($rentDate) && !empty($rentDate)) {
            $startDate = new \DateTime($rentDate);
            $endDate = clone $startDate;
            $endDate->setTime(23, 59, 59);
            $orders = $this->getRepo('Order\ProductOrder')->getTimesByDate(
                $id,
                $startDate,
                $endDate
            );
        } elseif ($type == Room::TYPE_FLEXIBLE) {
            $monthStart = $paramFetcher->get('month_start');
            $monthEnd = $paramFetcher->get('month_end');
            $allowedPeople = $product->getRoom()->getAllowedPeople();
            if (!is_null($monthStart) && !empty($monthStart) && !is_null($monthEnd) && !empty($monthEnd)) {
                $response = $this->getDatesForFlexibleRoom(
                    $id,
                    $monthStart,
                    $monthEnd,
                    $allowedPeople
                );
            }

            return new View($response);
        } else {
            $orders = $this->getRepo('Order\ProductOrder')->getBookedDates($id);
        }

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

    /**
     * @param $id
     * @param $monthStart
     * @param $monthEnd
     * @param $allowedPeople
     *
     * @return array
     */
    private function getDatesForFlexibleRoom(
        $id,
        $monthStart,
        $monthEnd,
        $allowedPeople
    ) {
        $response = [];
        $monthStart = new \DateTime($monthStart);
        $monthEnd = new \DateTime($monthEnd);
        $monthEnd->setTime(23, 59, 59);
        $flexibleOrders = $this->getRepo('Order\ProductOrder')->getFlexibleBookedDates(
            $id,
            $monthStart,
            $monthEnd
        );
        if (sizeof($flexibleOrders) >= $allowedPeople) {
            $daysArray = [];
            foreach ($flexibleOrders as $order) {
                $start = $order->getStartDate();
                $end = $order->getEndDate();
                $days = new \DatePeriod(
                    $start,
                    new \DateInterval('P1D'),
                    $end
                );
                foreach ($days as $day) {
                    array_push($daysArray, $day->format('Y-m-d'));
                }
            }
            $values = array_count_values($daysArray);
            foreach ($values as $key => $value) {
                if ($value >= $allowedPeople) {
                    $date = new \DateTime($key);
                    array_push($response, $date->getTimeStamp());
                }
            }
        }

        return $response;
    }
}
