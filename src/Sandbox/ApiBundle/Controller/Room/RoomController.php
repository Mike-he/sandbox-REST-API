<?php

namespace Sandbox\ApiBundle\Controller\Room;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Room\RoomTypeTags;
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

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="type",
     *     nullable=true,
     *     default=null,
     *     strict=true
     * )
     *
     * @Route("/type_tags")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getRoomTypeTagsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $typeId = $paramFetcher->get('type');

        $typeTags = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomTypeTags')
            ->getRoomTypeTags($typeId);

        $response = array();
        foreach ($typeTags as $tag) {
            array_push($response, array(
                'id' => $tag->getId(),
                'tag_name' => $this->container->get('translator')->trans(RoomTypeTags::TRANS_PREFIX.$tag->getTagKey()),
//                'icon' => $this->getParameter('image_url').$tag->getIcon(),
            ));
        }

        return new View($response);
    }
}
