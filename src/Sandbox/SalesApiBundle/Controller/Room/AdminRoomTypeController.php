<?php

namespace Sandbox\SalesApiBundle\Controller\Room;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class AdminRoomTypeController extends SalesRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/room/types")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSalesRoomTypesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $salesInfos = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
            ->findBy(array(
                'company' => $salesCompanyId,
                'status' => true,
            ));

        if (empty($salesInfos)) {
            return new View(array());
        }

        $typeKeys = array();
        foreach ($salesInfos as $info) {
            array_push($typeKeys, $info->getTradeTypes());
        }

        $types = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomTypes')
            ->getTypesByKeys($typeKeys);

        $imageUrl = $this->getParameter('image_url');

        // translate
        foreach ($types as $type) {
            $typeText = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$type->getName(),
                array(),
                null,
                $request->getPreferredLanguage()
            );
            $type->setDescription($typeText);

            $type->setIcon($imageUrl.$type->getIcon());
            $type->setHomepageIcon($imageUrl.$type->getHomepageIcon());
        }

        $view = new View($types);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['drop_down']));

        return $view;
    }
}
