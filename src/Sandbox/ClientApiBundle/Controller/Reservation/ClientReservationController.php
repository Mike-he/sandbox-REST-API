<?php

namespace Sandbox\ClientApiBundle\Controller\Reservation;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sandbox\ApiBundle\Entity\Reservation\Reservation;
use Sandbox\ApiBundle\Form\Reservation\ReservationType;
use Sandbox\ApiBundle\Entity\Product\Product;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClientReservationController extends SandboxRestController
{
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

        $productId = $reservation->getProductId();
        $product = $this->getDoctrine()->getRepository('SandboxApiBundle:Product\Product')->findOneById($productId);
        $roomId = $product->getRoomId();
        $room = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\Room')->findOneById($roomId);
        $type = $room->getType();

        if ($type != 'office') {
            return $this->customErrorView(
                400010,
                'ERROR_CODE',
                'INVALID_ROOM_TYPE'
            );
        }

        $reservations = $this->getDoctrine()->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->findByUserAndProduct($userId, $productId);

        if ($reservations) {
            throw new BadRequestHttpException(self::CONFLICT_MESSAGE);
        }

        $reservation->setUserId($userId);

        $str = mt_rand(1000, 9999);
        $serialNumber = 'R'.$str.time();
        $reservation->setSerialNumber($serialNumber);

        $em = $this->getDoctrine()->getManager();
        $em->persist($reservation);
        $em->flush();

        $product = $this->getDoctrine()->getRepository('SandboxApiBundle:Product\Product')->findOneById($productId);
        $companyId = $product->getRoom()->getBuilding()->getCompanyId();

        $customer_id = $this->container->get('sandbox_api.sales_customer')->createCustomer($userId, $companyId);

        $view = new View();
        $view->setData(
            ['id' => $reservation->getId(), 'customer_id' => $customer_id]
        );

        return $view;
    }
}
