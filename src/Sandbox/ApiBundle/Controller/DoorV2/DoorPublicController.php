<?php

namespace Sandbox\ApiBundle\Controller\DoorV2;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DoorPublicController extends SandboxRestController
{
    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/door/webhooks_sn")
     * @Method({"POST"})
     *
     * @return mixed
     */
    public function webhooksSNAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $partnerId = $request->get('partnerID');
        $timeStamp = $request->get('timestamp');
        $nonstr = $request->get('nonstr');
        $token = $request->get('token');
        $code = $request->get('code');
        $door = $request->get('door');
        $sn = $request->get('sn');

        $str = '';
        $str .= 'parterId: '.$partnerId.PHP_EOL;
        $str .= 'timestamp: '.$timeStamp.PHP_EOL;
        $str .= 'nonstr: '.$nonstr.PHP_EOL;
        $str .= 'token: '.$token.PHP_EOL;
        $str .= 'code: '.$code.PHP_EOL;
        $str .= 'door: '.$door.PHP_EOL;
        $str .= 'sn: '.$sn.PHP_EOL;

        $em = $this->getDoctrine()->getManager();

        $param = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array(
                'key' => 'sn',
            ));

        if (!$param) {
            $param = new Parameter();
        }

        $param->setKey('sn');
        $param->setValue($str);
        $em->persist($param);
        $em->flush();

        return new Response(json_encode(array('success' => true,)));
    }
}