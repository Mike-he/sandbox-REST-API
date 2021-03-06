<?php

namespace Sandbox\AdminApiBundle\Controller\Message;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\AdminApiBundle\Command\SyncJmessageCommand;
use Sandbox\ApiBundle\Entity\Message\JMessageHistory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;

class AdminMessageHistoryController extends AdminMessagePushController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="xmpp_username",
     *     array=false,
     *     nullable=true,
     *     default="service",
     *     strict=true
     * )
     *
     * @Route("/messages/service_authorization")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getServiceAuthorizationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission

        $xmppUsername = $paramFetcher->get('xmpp_username');

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy(array(
                'xmppUsername' => $xmppUsername,
            ));

        if (is_null($user)) {
            return new View([]);
        }

        return new View(array(
            'xmpp_username' => $xmppUsername,
            'xmpp_code' => $this->get('sandbox_api.des_encrypt')->encrypt($user->getPassword()),
        ));
    }

    /**
     * @param Request $request the request object
     *
     * @Annotations\QueryParam(
     *    name="media_id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="search by tag"
     * )
     *
     * @Route("/messages/media")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMediaAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $mediaId = $paramFetcher->get('media_id');

        $media = $this->get('sandbox_api.jmessage')->getMedia($mediaId);

        $result = $media['body'];

        return new View($result);
    }

    /**
     * @param Request $request the request object
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many rooms to return "
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
     * @Annotations\QueryParam(
     *     name="xmpp_username",
     *     array=false,
     *     nullable=true,
     *     default="service",
     *     strict=true
     * )
     *
     * @Route("/messages/history")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getHistoryAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $xmppUsername = $paramFetcher->get('xmpp_username');

        $min = ($pageIndex - 1) * $pageLimit;
        $max = $min + $pageLimit - 1;

        $messages = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Message\JMessageHistory')
            ->getFromIds(
                JMessageHistory::TARGET_TYPE_SINGLE,
                $xmppUsername
            );

        $fromIds = [];
        foreach ($messages as $message) {
            array_push($fromIds, $message['from_id']);
        }

        $fromIds = array_values(array_unique($fromIds));

        $count = count($fromIds);

        $userJIDs = [];
        foreach ($fromIds as $key => $jid) {
            if ($min <= $key && $key <= $max) {
                array_push($userJIDs, $jid);
            }
        }

        $usersArray = [];
        foreach ($userJIDs as $jid) {
            $user = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->findOneBy([
                    'xmppUsername' => $jid,
                ]);

            $userProfile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy([
                    'user' => $user,
                ]);

            $lastMessage = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Message\JMessageHistory')
                ->getLastMessages(
                    $jid,
                    $xmppUsername,
                    JMessageHistory::TARGET_TYPE_SINGLE
                );
            if ($user) {
                $usersArray[] = array(
                    'id' => $user->getId(),
                    'name' => $userProfile->getName(),
                    'phone' => $user->getPhone(),
                    'email' => $user->getEmail(),
                    'authorized' => $user->isAuthorized(),
                    'banned' => $user->isBanned(),
                    'jid' => $jid,
                    'message' => [
                        'msg_type' => $lastMessage->getMsgType(),
                        'body' => $lastMessage->getMsgBody(),
                        'sent_date' => $lastMessage->getMsgCtime(),
                    ],
                );
            }
        }

        return new View(array(
            'total_count' => $count,
            'items' => $usersArray,
        ));
    }

    /**
     * @param Request $request the request object
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many rooms to return "
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
     * @Annotations\QueryParam(
     *    name="from_id",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="search by tag"
     * )
     *
     * @Annotations\QueryParam(
     *     name="xmpp_username",
     *     array=false,
     *     nullable=true,
     *     default="service",
     *     strict=true
     * )
     *
     * @Route("/messages/single/history")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSingleHistoryAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $fromId = $paramFetcher->get('from_id');
        $xmppUsername = $paramFetcher->get('xmpp_username');

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $messages = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Message\JMessageHistory')
            ->getSingleMessages(
                $fromId,
                $xmppUsername,
                JMessageHistory::TARGET_TYPE_SINGLE,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Message\JMessageHistory')
            ->countSingleMessages(
                $fromId,
                $xmppUsername,
                JMessageHistory::TARGET_TYPE_SINGLE
            );

        return new View(array(
            'total_count' => $count,
            'items' => $messages,
        ));
    }

    /**
     * @param Request $request the request object
     *
     * @Route("/messages/sync/history")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSyncHistoryAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        //execute SyncJmessageCommand
        $command = new SyncJmessageCommand();
        $command->setContainer($this->container);

        $input = new ArrayInput(array());
        $output = new NullOutput();

        $command->run($input, $output);

        return new View();
    }
}
