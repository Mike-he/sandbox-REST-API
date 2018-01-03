<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Expert;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Expert\Expert;
use Sandbox\ApiBundle\Entity\Expert\ExpertOrderRemark;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class ClientExpertOrderRemarkController extends SandboxRestController
{
    /**
     * Create A Expert Order Remark.
     *
     * @param $request
     * @param $id
     *
     * @Route("/experts/orders/{id}/remarks")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postExpertAction(
        Request $request,
        $id
    ) {
        $em = $this->getDoctrine()->getManager();
        $userId = $this->getUserId();

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\ExpertOrder')
            ->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->find($order->getExpertId());
        $this->throwNotFoundIfNull($expert, self::NOT_FOUND_MESSAGE);

        if ($userId != $expert->getUserId()) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_ONLY_MY_OWN_OPERATION_CODE,
                CustomErrorMessagesConstants::ERROR_ONLY_MY_OWN_OPERATION_MESSAGE
            );
        }

        $content = json_decode($request->getContent(), true);

        $remark = new ExpertOrderRemark();
        $remark->setOrderId($id);
        $remark->setRemark($content['remark']);

        $em->persist($remark);
        $em->flush();

        $response = array(
            'id' => $remark->getId(),
        );

        return new View($response, 201);
    }
}
