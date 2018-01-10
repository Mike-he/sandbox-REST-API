<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\Location\LocationController;
use Sandbox\ApiBundle\Entity\User\UserShare;
use Sandbox\ApiBundle\Form\User\UserShareType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Rest controller for User Share.
 *
 * @category Sandbox
 *
 * @author   Leo Xu
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class ClientUserShareController extends LocationController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="object",
     *    array=false,
     *    default="product_order",
     *    nullable=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="objectId",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="\d+",
     *    description=""
     * )
     *
     * @Route("/share")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserShareAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $view = new View();

        $object = $paramFetcher->get('object');
        $objectId = $paramFetcher->get('objectId');

        $share = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserShare')
            ->findOneBy([
                'object' => $object,
                'objectId' => $objectId,
            ]);
        $this->throwNotFoundIfNull($share, self::NOT_FOUND_MESSAGE);

        switch ($object) {
            case UserShare::OBJECT_PRODUCT_ORDER:
                $view = $this->getProductOrderView(
                    $request,
                    $objectId,
                    $view,
                    $share
                );

                break;
            default:
                return $view;
        }

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Route("/share")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postUserShareAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy(
                [
                    'banned' => false,
                    'id' => $userId,
                ]
            );
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $this->handleSharePost($request, $userId);

        return new View();
    }

    /**
     * @param $request
     * @param $userId
     */
    private function handleSharePost(
        $request,
        $userId
    ) {
        $em = $this->getDoctrine()->getManager();

        $share = new UserShare();

        $form = $this->createForm(new UserShareType(), $share);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $object = $share->getObject();
        $objectId = $share->getObjectId();

        // remove existing share
        $existShare = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserShare')
            ->findOneBy([
                'object' => $object,
                'objectId' => $objectId,
            ]);

        if (!is_null($existShare)) {
            $existShare->setTitle($share->getTitle());
            $existShare->setDescription($share->getDescription());
        } else {
            switch ($object) {
                case UserShare::OBJECT_PRODUCT_ORDER:
                    $order = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Order\ProductOrder')
                        ->findOneBy([
                            'userId' => $userId,
                            'id' => $objectId,
                        ]);
                    $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

                    break;
                default:
                    return;
            }

            $em->persist($share);
        }

        $em->flush();
    }

    /**
     * @param $request
     * @param $orderId
     * @param View      $view
     * @param UserShare $share
     *
     * @return mixed
     */
    private function getProductOrderView(
        $request,
        $orderId,
        $view,
        $share
    ) {
        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->find($orderId);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $userId = $order->getUserId();
        $profile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserProfile')
            ->findOneBy(['userId' => $userId]);
        $username = $profile->getName();

        $room = $order->getProduct()->getRoom();
        $type = $room->getType();
        $language = $request->getPreferredLanguage();

        $description = $this->get('translator')->trans(
            ProductOrderExport::TRANS_ROOM_TYPE.$type,
            array(),
            null,
            $language
        );

        $room->setTypeDescription($description);

        $viewArray = [
            'order' => $order,
            'username' => $username,
            'share_info' => $share,
        ];

        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($viewArray);

        return $view;
    }
}
