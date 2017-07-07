<?php

namespace Sandbox\AdminApiBundle\Controller\Message;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Traits\OpenfireApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class AdminMessageHistoryController extends AdminMessagePushController
{
    use OpenfireApi;

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
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
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_VIEW);

        $em = $this->getDoctrine()->getManager();

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy(array(
                'xmppUsername' => 'service',
            ));

        $userToken = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserToken')
            ->findOneBy(array(
                'user' => $user,
            ));

        $userToken->setCreationDate(new \DateTime('now'));
        $userToken->setModificationDate(new \DateTime('now'));
        $em->flush();

        $authorization = 'Basic ' . base64_encode($userToken->getToken() . ':' . $userToken->getClientId());

        return new View(array(
            'Authorization' => $authorization,
        ));
    }

    /**
     * Get History Message.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="fromJID",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="toJID",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description=""
     * )
     *
     * @Route("/messages/service_history_message")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getHistoryMessageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_VIEW);

        $fromJID = $paramFetcher->get('fromJID');
        $toJID = $paramFetcher->get('toJID');
        $type = $paramFetcher->get('type');

        $fromJID = '"'.$fromJID.'"';
        $toJID = '"'.$toJID.'"';
        $type = '"'.$type.'"';

        $message = $this->getHistoryMessageForService($fromJID, $toJID, $type);

        return new View($message);
    }

    /**
     * Get History Message.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="fromJID",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="toJID",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description=""
     * )
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
     * @Route("/messages/service_clients")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getHistoryMessageClientsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminMessagePermission(AdminPermission::OP_LEVEL_VIEW);

        $fromJID = $paramFetcher->get('fromJID');
        $toJID = $paramFetcher->get('toJID');
        $type = $paramFetcher->get('type');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $min = ($pageIndex - 1) * $pageLimit;
        $max = $min + $pageLimit - 1;

        $toJID = '"'.$toJID.'"';
        $type = '"'.$type.'"';

        $messages = $this->getHistoryMessage($fromJID, $toJID, $type);

        $fromJIDs = [];
        foreach ($messages as $message) {
            array_push($fromJIDs, $message['fromJID']);
        }

        $fromJIDs = array_values(array_unique($fromJIDs));

        $count = count($fromJIDs);

        $userJIDs = [];
        foreach ($fromJIDs as $key => $jid) {
            if ($min <= $key && $key <= $max) {
                array_push($userJIDs, $jid);
            }
        }

        $usersArray = [];
        foreach ($userJIDs as $jid) {
            $xmppUsername = explode('@', $jid);
            $xmppUsername = $xmppUsername[0];

            $user = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->findOneBy([
                    'xmppUsername' => $xmppUsername,
                ]);

            $userProfile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy([
                    'user' => $user,
                ]);

            $lastMessage = $this->getHistoryMessage($jid, $toJID, '"single"', 0, 1);
            $messageFromJid = $lastMessage[0]['fromJID'];
            $xmppUsernameMessage = explode('@', $messageFromJid);
            $xmppUsernameMessage = $xmppUsernameMessage[0];

            $userMessage = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->findOneBy([
                    'xmppUsername' => $xmppUsernameMessage,
                ]);

            $userProfileMessage = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy([
                    'user' => $userMessage,
                ]);

            array_push($usersArray, [
                'id' => $user->getId(),
                'name' => $userProfile->getName(),
                'phone' => $user->getPhone(),
                'email' => $user->getEmail(),
                'authorized' => $user->isAuthorized(),
                'jid' => $jid,
                'message' => [
                    'from_user_profile_name' => $userProfileMessage->getName(),
                    'body' => $lastMessage[0]['body'],
                    'sentDate' => $lastMessage[0]['sentDate'],
                ],
            ]);
        }

        return new View(array(
            'total_count' => $count,
            'items' => $usersArray,
        ));
    }
}