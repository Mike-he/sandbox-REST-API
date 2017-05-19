<?php

namespace Sandbox\ApiBundle\Controller\Room;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Room\RoomTypesGroups;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;

/**
 * Room Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class RoomController extends SandboxRestController
{
    /**
     * @param Request $request
     *
     * @Route("/types")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getRoomTypesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $types = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomTypes')
            ->findAll();

        $language = $request->getPreferredLanguage();
        $imageUrl = $this->getParameter('image_url');

        foreach ($types as $type) {
            $typeText = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$type->getName(),
                array(),
                null,
                $language
            );
            $type->setDescription($typeText);
            $type->setIcon($imageUrl.$type->getIcon());
            $type->setHomepageIcon($imageUrl.$type->getHomepageIcon());

            $units = $type->getUnits();

            foreach ($units as $unit) {
                $unitText = $this->get('translator')->trans(
                    ProductOrderExport::TRANS_ROOM_UNIT.$unit->getUnit(),
                    array(),
                    null,
                    $language
                );
                $unit->setDescription($unitText);
            }
        }

        $view = new View($types);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['drop_down']));

        return $view;
    }
}
