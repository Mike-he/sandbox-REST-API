<?php

namespace Sandbox\ClientApiBundle\Controller\Reservation;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Reservation\Reservation;
use Sandbox\ApiBundle\Form\Reservation\ReservationType;
use Sandbox\ApiBundle\Entity\Product\Product;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints\DateTime;

class ClientReservationController extends SandboxRestController
{
    const PRODUCT_NOT_FOUND_CODE = 400001;
    const PRODUCT_NOT_FOUND_MESSAGE = 'Product Does Not Exist';
    const ROOMTYPE_NOT_ALLOWED_CODE = 400002;
    const ROOMTYPE_NOT_ALLOWED_MESSAGE = 'RoomType Is Not Allowed';
    const PRODUCT_NOT_AVAILABLE_CODE = 400003;
    const PRODUCT_NOT_AVAILABLE_MESSAGE ='Product Is Not Available';
    const NOT_WITHIN_DATE_RANGE_CODE = 400004;
    const NOT_WITHIN_DATE_RANGE_MESSAGE = 'Not Within 7 Days For Reservation';
    CONST NOT_WITHIN_VIEW_TIME_CODE = 400005;
    CONST NOT_WITHIN_VIEW_TIME_MESSAGE = 'ViewTime Shoule Be In 8:00~18:00';
    const WRONG_VIEWTIME_CODE = 400006;
    const WRONG_VIEWTIME_MESSAGE = 'Not Allowed Before Now';
    const VIEW_RANGE_LIMIT = 7;

    /**
     * @Route("/reservation")
     *
     * @param Request $request
     *
     * @return View
     * @Method({"POST"})
     */
    public function postReservationAction(Request $request)
    {
        $userId = $this->getUserId();
        $reservation = new Reservation();

        $form = $this->createForm(new ReservationType(), $reservation);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $now = new \DateTime();
        $viewTime = $reservation->getViewTime();
        $productId = $reservation->getProductId();
        $product = $this->getDoctrine()->getRepository('SandboxApiBundle:Product\Product')->find($productId);
        $viewTime = new \DateTime($viewTime);
        $error = $this->checkIfProductAvailable(
            $product,
            $now,
            $viewTime
           );
        if (!empty($error)) {
            return $this->customErrorView(
                400,
                $error['code'],
                $error['message']
            );
        }

        $companyId = $product->getRoom()->getBuilding()->getCompanyId();
        $roomId = $product->getRoomId();
        $room = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\Room')->find($roomId);
        $type = $room->getType();
        if ($type != Room::TYPE_OFFICE) {
            return $this->customErrorView(
                400,
                self::ROOMTYPE_NOT_ALLOWED_CODE,
                self::ROOMTYPE_NOT_ALLOWED_MESSAGE
            );
        }

        $diff = $viewTime->diff($now)->days;
        if( $diff > self::VIEW_RANGE_LIMIT ){
            return $this->customErrorView(
                400,
                self::NOT_WITHIN_DATE_RANGE_CODE,
                self::NOT_WITHIN_DATE_RANGE_MESSAGE
            );
        }

        if( $now >= $viewTime ){
            return $this->customErrorView(
                400,
                self::WRONG_VIEWTIME_CODE,
                self::WRONG_VIEWTIME_MESSAGE
            );
        }
        $startdate = clone $viewTime;
        $startdate = $startdate->setTime('08','00','00');

        $enddate = clone $viewTime;
        $enddate = $enddate->setTime('18','00','00');

        $begin = $startdate > $viewTime;
        $end = $viewTime > $enddate;

        if($begin || $end){
            return $this->customErrorView(
                400,
                self::NOT_WITHIN_VIEW_TIME_CODE,
                self::NOT_WITHIN_VIEW_TIME_MESSAGE
            );
        }

        $sameReservation = $this->getDoctrine()->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->getReservationFromSameUser(
                $userId,
                $productId,
                $viewTime
            );

        if(!empty($sameReservation)){
            return new View(
                ['serial_number'=>$sameReservation[0]->getSerialNumber()]
            );
        }

        $reservation->setUserId($userId);

        $str = mt_rand(1000, 9999);
        $serialNumber = 'R'.$str.time();
        $reservation->setSerialNumber($serialNumber);
        $reservation->setCompanyId($companyId);

        $em = $this->getDoctrine()->getManager();
        $em->persist($reservation);
        $em->flush();

        $product = $this->getDoctrine()->getRepository('SandboxApiBundle:Product\Product')->findOneById($productId);
        $companyId = $product->getRoom()->getBuilding()->getCompanyId();

        $this->container->get('sandbox_api.sales_customer')->createCustomer($userId, $companyId);

        $view = new View();
        $view->setData(
            ['serial_number' => $reservation->getSerialNumber()]
        );

        return $view;
    }


    /**
     * @param $product
     * @param $now
     * @param $viewTime
     *
     * @return array
     */
    private function checkIfProductAvailable(
        $product,
        $now,
        $viewTime
    ) {
        $error = [];

        if (is_null($product)) {
            return $this->setErrorArray(
                self::PRODUCT_NOT_FOUND_CODE,
                self::PRODUCT_NOT_FOUND_MESSAGE
            );
        }

        $productStart = $product->getStartDate();
        $productEnd = $product->getEndDate();

        if (
            $now < $productStart ||
            $now > $productEnd ||
            $viewTime < $productStart ||
            $viewTime > $productEnd ||
            $product->getVisible() == false
        ) {
            return $this->setErrorArray(
                self::PRODUCT_NOT_AVAILABLE_CODE,
                self::PRODUCT_NOT_AVAILABLE_MESSAGE
            );
        }

        return $error;
    }
}
