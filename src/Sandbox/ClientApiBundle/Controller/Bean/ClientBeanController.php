<?php

namespace Sandbox\ClientApiBundle\Controller\Bean;

use Sandbox\ApiBundle\Controller\Bean\BeanController;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 *  Client Bean Controller.
 *
 * @category Sandbox
 *
 * @author   Feng Li <feng.li@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientBeanController extends BeanController
{
    /**
     * Post Bean.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/bean")
     * @Method({"post"})
     *
     * @Annotations\QueryParam(
     *    name="source",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="height"
     * )
     *
     * @return View
     */
    public function postBeanAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $source = $paramFetcher->get('source');

        switch ($source) {
            case 'login':
                $source = Parameter::KEY_BEAN_USER_LOGIN;
                break;
            case 'share':
                $source = Parameter::KEY_BEAN_USER_SHARE;
                break;
            default:
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $this->get('sandbox_api.bean')->postBeanChange(
            $userId,
            0,
            null,
            $source
        );

        $em = $this->getDoctrine()->getManager();

        $em->flush();

        return new View();
    }
}
