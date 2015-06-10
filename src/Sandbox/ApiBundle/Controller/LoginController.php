<?php

namespace Sandbox\ApiBundle\Controller;

use Sandbox\ApiBundle\Entity\Client;
use Sandbox\ApiBundle\Entity\UserToken;
use Sandbox\ApiBundle\Form\ClientType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * THIS NEED TO BE MOVED OUT OF REST API RESOURCE SERVER
 * TO AN INDEPENDENT USER SERVER
 *
 * Login controller
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class LoginController extends SandboxRestController
{
    /**
     * Login
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/login")
     * @Method({"GET", "POST"})
     *
     * @return string
     * @throws \Exception
     */
    public function getLoginAction(
        Request $request
    ) {
        try {
            $user = $this->getUser();

            // check user is activated
            if (!$user->getActivated()) {
                return $this->customErrorView(401, 490, 'Account not activated');
            }

            $em = $this->getDoctrine()->getManager();

            $client = new Client();
            try {
                // get payload from request
                $payload = json_decode($request->getContent(), true);

                // get client data from request
                $clientData = $payload['client'];

                $clientDataId = 0;
                if (array_key_exists('id', $clientData)) {
                    $clientDataId = $clientData['id'];
                }

                // get existing client
                $client = $this->getRepo('Client')->find($clientDataId);
                if (is_null($client)) {
                    // not found, create new one
                    $client = new Client();
                    unset($clientData['id']);
                    $clientDataId = 0;
                }

                $form = $this->createForm(new ClientType(), $client);
                $form->submit($clientData, true);

                if ($form->isValid()) {
                    if ($clientDataId <= 0) {
                        // save new client
                        $em->persist($client);
                        $em->flush();
                    }

                    // set client ip address
                    $client->setIpAddress($request->getClientIp());
                }
            } catch (Exception $e) {
                // do nothing...
            }

            // save or refresh token
            $userToken = $this->setUserToken($user->getId(), $client->getId());
            if (is_null($userToken->getId())) {
                $em->persist($userToken);
            }
            $em->flush();

            // response
            $view = new View();
            $view->setData(array(
                'userid' => $user->getId(),
                'clientid' => $client->getId(),
                'xmpp_username' => $user->getXmppUsername(),
                'token' => $userToken->getToken(),
            ));
        } catch (Exception $e) {
            throw new \Exception('Something went wrong!');
        }

        return $view;
    }

    /**
     * @param $userId
     * @param $clientId
     * @return UserToken|object
     * @throws \Exception
     */
    private function setUserToken(
        $userId,
        $clientId
    ) {
        try {
            $userToken = $this->getRepo('UserToken')->findOneBy(
                array(
                    'userid' => $userId,
                    'clientid' => $clientId,
                )
            );

            if (is_null($userToken)) {
                $userToken = new UserToken();
                $userToken->setUserid($userId);
                $userToken->setClientid($clientId);
                $userToken->setToken(md5(uniqid(rand(), true)));
            }

            // refresh creation date
            $userToken->setCreationdate(time());

            return $userToken;
        } catch (Exception $e) {
            throw new \Exception('Something went wrong!');
        }
    }
}
