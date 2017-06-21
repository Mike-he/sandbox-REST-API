<?php

namespace Sandbox\ApiBundle\Controller\Property;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Property\PropertyTypes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
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
class PropertyTypeController extends SandboxRestController
{
    /**
     * @Route("/types")
     * @Method({"GET"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function getPropertyTypesAction(
        Request $request
    ) {
        $types = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Property\PropertyTypes')
            ->findAll();

        $imageUrl = $this->getParameter('image_url');
        $language = $request->getPreferredLanguage();

        foreach ($types as $type) {
            $typeText = $this->get('translator')->trans(
                PropertyTypes::TRANS_PROPERTY_TYPE.$type->getName(),
                array(),
                null,
                $language
            );

            $type->setDescription($typeText);
            $type->setApplicationIcon($imageUrl.$type->getApplicationIcon());
            $type->setCommunityIcon($imageUrl.$type->getCommunityIcon());
            $type->setApplicationSelectedIcon($imageUrl.$type->getApplicationSelectedIcon());
        }

        return new View($types);
    }
}
