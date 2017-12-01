<?php

namespace Sandbox\AdminApiBundle\Controller\Message;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Paginator;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Message\Message;
use Sandbox\ApiBundle\Entity\Message\MessageMaterial;
use Sandbox\ApiBundle\Form\Message\MessagePushType;
use Sandbox\ApiBundle\Traits\SendNotification;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Controller\Annotations;

class AdminMessagePushController extends AdminRestController
{
    use SendNotification;

    /**
     * Get Message List.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many messages to return per page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Route("/messages")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getMessagesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $messages = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Message\Message')
            ->getMessageList(
                $platform,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Message\Message')
            ->countMessage(
                $platform
            );

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => (int) $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $messages,
                'total_count' => (int) $count,
            ));

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/messages/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMessageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_VIEW);

        $message = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Message\Message')
            ->find($id);
        $this->throwNotFoundIfNull($message, self::NOT_FOUND_MESSAGE);

        return new View($message);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/messages/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteMessageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_VIEW);

        $message = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Message\Message')
            ->find($id);
        $this->throwNotFoundIfNull($message, self::NOT_FOUND_MESSAGE);

        $message->setVisible(false);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="query",
     *     array=false,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many messages to return per page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Route("/message_materials")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMessageMaterialsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $search = $paramFetcher->get('query');

        $messages = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Message\MessageMaterial')
            ->getMessageMaterialList($search);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $messages,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/message_materials/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMessageMaterialAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_VIEW);

        $message = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Message\MessageMaterial')
            ->find($id);
        $this->throwNotFoundIfNull($message, self::NOT_FOUND_MESSAGE);

        return new View($message);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/message_materials/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteMessageMaterialAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_VIEW);

        $message = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Message\MessageMaterial')
            ->find($id);
        $this->throwNotFoundIfNull($message, self::NOT_FOUND_MESSAGE);

        $message->setVisible(false);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/messages")
     * @Method("POST")
     *
     * @return View
     */
    public function postMessagesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_EDIT);

        $messageMaterial = new MessageMaterial();

        $form = $this->createForm(new MessagePushType(), $messageMaterial);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        if (MessageMaterial::TYPE_MATERIAL == $messageMaterial->getType()) {
            if (is_null($messageMaterial->getContent())) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($messageMaterial);
            $em->flush();
        }

        // send message to all client
        if (MessageMaterial::TYPE_MESSAGE == $messageMaterial->getType() || !is_null($messageMaterial->getUrl()) || MessageMaterial::ACTION_PUSH == $messageMaterial->getAction()) {
            $this->sendMessages($messageMaterial);
        }

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/messages/{id}")
     * @Method("PUT")
     *
     * @return View
     */
    public function putMessageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_EDIT);

        $messageMaterial = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Message\MessageMaterial')
            ->find($id);

        $this->throwNotFoundIfNull($messageMaterial, self::NOT_FOUND_MESSAGE);

        // bind form
        $form = $this->createForm(
            new MessagePushType(),
            $messageMaterial,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // send message to all client
        if (!is_null($messageMaterial->getUrl()) || MessageMaterial::ACTION_PUSH == $messageMaterial->getAction()) {
            $this->sendMessages($messageMaterial);
        }

        return new View();
    }

    /**
     * @param MessageMaterial $messageMaterial
     */
    private function sendMessages(
        $messageMaterial
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];

        $messageUrl = $messageMaterial->getUrl();
        $messageTitle = $messageMaterial->getTitle();
        $messageCover = $messageMaterial->getCover();
        $messageContent = $messageMaterial->getContent();

        $em = $this->getDoctrine()->getManager();

        $bodyArray = [
            'title' => $messageTitle,
            'cover' => $messageCover,
            'url' => $messageUrl,
            'content' => $messageContent,
        ];

        $message = new Message();
        $message->setBody(json_encode($bodyArray));
        $message->setPlatform($platform);
        $em->persist($message);
        $em->flush();

        if (!is_null($messageUrl)) {
            $url = $messageUrl;
        } else {
            $mobileUrl = $this->getParameter('room_mobile_url');
            $url = $mobileUrl.'/message?id='.$message->getId();
        }

        $contentArray = [
            'type' => 'service',
            'action' => 'push',
            'id' => $message->getId(),
            'title' => $messageTitle,
            'url' => $url,
            'cover' => $messageCover,
        ];

        $key = null;
        $secret = null;
        switch ($platform) {
            case Message::PLATFORM_OFFICIAL:
                $title = '创合秒租';
                break;
            case Message::PLATFORM_COMMNUE:
                $title = '合创设';
                $key = $this->getParameter('jpush_commnue_key');
                $secret = $this->getParameter('jpush_commnue_secret');
                break;
            default:
                $title = '创合秒租';
        }

        $data = $this->getJpushData(
            'all',
            ['lang_zh'],
            $messageTitle,
            $title,
            $contentArray,
            true
        );

        $this->sendJpushNotification($data, $key, $secret);
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    protected function checkAdminMessagePermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_MESSAGE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_MESSAGE_CONSULTATION],
                ['key' => AdminPermission::KEY_COMMNUE_PLATFORM_CUSTOMER],
            ],
            $opLevel
        );
    }
}
