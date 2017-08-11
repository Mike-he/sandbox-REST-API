<?php

namespace Sandbox\ClientApiBundle\Controller\Reservation;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
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
     * @param Request $request
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

        $product = $this->getDoctrine()->getRepository('SandboxApiBundle:Product\Product')->findOneById($reservation->getProductId());
        $roomId = $product->getRoomId();
        $room = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\Room')->findOneById($roomId);
        $type = $room->getType();

        if($type != 'office'){
            return $this->customErrorView(
                400010,
                'ERROR_CODE',
                'INVALID_ROOM_TYPE'

            );
        }

        $reservations = $this->getDoctrine()->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->findByUserAndProduct($userId,$reservation->getProductId());

        if($reservations){
            throw new BadRequestHttpException(self::CONFLICT_MESSAGE);
        }

        $reservation->setUserId($userId);

        $str = mt_rand(1000,9999);
        $serialNumber = 'R'.$str.time();
        $reservation->setSerialNumber($serialNumber);

        $em = $this->getDoctrine()->getManager();
        $em->persist($reservation);
        $em->flush();

        $view = new View();
        $view->setData(
            ['id' => $reservation->getId()]
        );

        return $view;
     }
}
