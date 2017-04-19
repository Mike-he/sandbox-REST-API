<?php

namespace Sandbox\ClientApiBundle\Controller\Bean;

use Sandbox\ApiBundle\Constants\BeanConstants;
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
     * @param Request $request
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
        Request $request
    ) {
        $userId = $this->getUserId();

        $source = $request->get('source');

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

        $parameter = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array('key' => $source));
        $value = $parameter->getValue();
        $number = substr($value, 1);

        return new View((int) $number);
    }

    /**
     * Get My Bean flows.
     *
     * @param Request $request
     *
     * @Route("/beans/balance")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMyBeanBalanceAction(
        Request $request
    ) {
        $userId = $this->getUserId();

        $bean = 0;
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($userId);

        if ($user) {
            $bean = $user->getBean();
        }

        return new View(array('bean' => $bean));
    }

    /**
     * Get My Bean flows.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     *    description="Offset of page"
     * )
     *
     * @Route("/beans/flows")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMyBeanFlowsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $language = $request->getPreferredLanguage();

        $flows = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserBeanFlow')
            ->findBy(
                array('userId' => $userId),
                array('creationDate' => 'DESC'),
                $limit,
                $offset
            );

        foreach ($flows as $flow) {
            $source = $this->get('translator')->trans(
                BeanConstants::TRANS_USER_BEAN.$flow->getSource(),
                array(),
                null,
                $language
            );

            $flow->setSource($source);
        }

        return new View($flows);
    }
}
