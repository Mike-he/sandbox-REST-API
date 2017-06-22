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

        $toJID = '"'.$toJID.'"';
        $type = '"'.$type.'"';

        $message = $this->getHistoryMessage($fromJID, $toJID, $type);

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

        $toJID = '"'.$toJID.'"';
        $type = '"'.$type.'"';

        $messages = $this->getHistoryMessage($fromJID, $toJID, $type);

        $fromJIDs = [];
        foreach ($messages as $message) {
            array_push($fromJIDs, $message['fromJID']);
        }

        $fromJIDs = array_unique($fromJIDs);

        $usersArray = [];
        foreach ($fromJIDs as $jid) {
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

            array_push($usersArray, [
                'name' => $userProfile->getName(),
                'phone' => $user->getPhone(),
                'email' => $user->getEmail(),
                'authorized' => $user->isAuthorized(),
            ]);
        }

        return new View($usersArray);
    }
}