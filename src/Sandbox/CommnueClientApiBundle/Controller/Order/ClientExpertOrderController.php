<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Order;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Expert\Expert;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class ClientExpertOrderController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam
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
     *    description="offset of page"
     * )
     *
     * @Annotations\QueryParam(
     *     name="status",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Route("/orders/expert_orders")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMyExpertOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $status = $paramFetcher->get('status');

        $userId = $this->getUserId();

        $expertOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\ExpertOrder')
            ->getLists(
                null,
                $status,
                $limit,
                $offset,
                $userId
            );

        $response = [];
        foreach ($expertOrders as $order) {
            $expert = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Expert\Expert')
                ->find($order['expert_id']);

            $this->setExpertLocation($expert);

            $order['expert'] = $expert;

            array_push($response, $order);
        }

        return new View($response);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/orders/expert_orders/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMyExpertOrderAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $expertOrder = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\ExpertOrder')
            ->find($id);
        $this->throwNotFoundIfNull($expertOrder, self::NOT_FOUND_MESSAGE);

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->find($expertOrder->getExpertId());

        $this->setExpertLocation($expert);

        $expertOrder->setExpert($expert);

        return new View($expertOrder);
    }

    /**
     * @param Expert $expert
     */
    private function setExpertLocation(
        $expert
    ) {
        $countryId = $expert->getCountryId();
        $provinceId = $expert->getProvinceId();
        $cityId = $expert->getCityId();
        $districtId = $expert->getDistrictId();

        $location = '';
        if (!is_null($countryId)) {
            $country = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($countryId);

            $location .= $country->getName();
        }

        if (!is_null($provinceId)) {
            $province = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($provinceId);

            $location .= $province->getName();
        }

        if (!is_null($cityId)) {
            $city = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($cityId);

            $location .= $city->getName();
        }

        if (!is_null($districtId)) {
            $district = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($districtId);

            $location .= $district->getName();
        }

        $expert->setLocation($location);
    }
}
