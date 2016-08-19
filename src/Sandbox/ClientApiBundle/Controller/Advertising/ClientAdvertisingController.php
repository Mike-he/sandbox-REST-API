<?php

namespace Sandbox\ClientApiBundle\Controller\Advertising;

use Sandbox\ApiBundle\Controller\Advertising\AdvertisingController;
use Sandbox\ApiBundle\Entity\Advertising\Advertising;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use JMS\Serializer\SerializationContext;

/**
 *  Client Advertising Controller.
 *
 * @category Sandbox
 *
 * @author   Feng Li <feng.li@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientAdvertisingController extends AdvertisingController
{
    /**
     * Get advertising.
     *
     * @param Request $request
     *
     * @Annotations\QueryParam(
     *    name="height",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="height"
     * )
     *
     * @Annotations\QueryParam(
     *    name="width",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="width"
     * )
     *
     * @Route("/advertising")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdvertisingAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $height = $paramFetcher->get('height');
        $width = $paramFetcher->get('width');

        $advertising = $this->getDoctrine()->getRepository("SandboxApiBundle:Advertising\Advertising")->findOneBy(array('visible' => true));
        $attachment = $this->getDoctrine()->getRepository("SandboxApiBundle:Advertising\AdvertisingAttachment")->findAttachment($advertising, $height, $width);

        $advertising->setAttachments($attachment);

        $view = new View($advertising);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('client_list'))
        );

        return $view;
    }
}
