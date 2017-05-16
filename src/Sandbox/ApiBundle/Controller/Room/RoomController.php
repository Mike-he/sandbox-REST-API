<?php

namespace Sandbox\ApiBundle\Controller\Room;

use FOS\RestBundle\Request\ParamFetcher;
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
     * @Annotations\QueryParam(
     *     name="group_key",
     *     array=false,
     *     default=null,
     *     nullable=true
     * )
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
        $groupKey = $paramFetcher->get('group_key');

        $types = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomTypes')
            ->getRoomTypesByGroupName($groupKey);

        $language = $request->getPreferredLanguage();

        foreach ($types as $type) {
            $typeText = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$type->getName(),
                array(),
                null,
                $language
            );
            $type->setDescription($typeText);
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
     * @param Request $request
     *
     * @Route("/type_groups")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getRoomTypeGroupsAction(
        Request $request
    ) {
        $groups = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomTypesGroups')
            ->findAll();

        $imageUrl = $this->getParameter('image_url');

        $response = array();
        foreach ($groups as $group) {
            $name = $this->get('translator')->trans(
                RoomTypesGroups::TRANS_GROUPS_PREFIX.$group->getGroupKey(),
                array(),
                null,
                $request->getPreferredLanguage()
            );

            array_push($response, array(
                'id' => $group->getId(),
                'group_key' => $group->getGroupKey(),
                'name' => $name,
                'icon' => $imageUrl.$group->getIcon(),
                'homepage_icon' => $imageUrl.$group->getHomepageIcon(),
            ));
        }

        return new View($response);
    }
}
