<?php

namespace Sandbox\ClientApiBundle\Controller\Bean;

use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
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
     * @Route("/beans")
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

        $exits = $this->get('sandbox_api.bean')->checkExits(
            $userId,
            $source
        );

        if ($exits) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_BEAN_OPERATION_TODAY_CODE,
                CustomErrorMessagesConstants::ERROR_BEAN_OPERATION_TODAY_MESSAGE
            );
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

    /**
     * Get My Bean flows.
     *
     * @param Request $request
     *
     * @Route("/beans")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMyBeanFlows(
        Request $request
    ) {
        $userId = $this->getUserId();

        $flows = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserBeanFlow')
            ->findBy(
                array('userId' => $userId),
                array('creationDate' => 'DESC')
            );

        return new View($flows);
    }
}
