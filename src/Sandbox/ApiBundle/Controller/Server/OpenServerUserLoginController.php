<?php

namespace Sandbox\ApiBundle\Controller\Server;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserClient;
use Sandbox\ApiBundle\Entity\User\UserToken;
use Sandbox\ClientApiBundle\Data\User\UserLoginData;
use Sandbox\ClientApiBundle\Form\User\UserLoginType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Doctrine\ORM\EntityManager;

class OpenServerUserLoginController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/openserver/client/auth/login")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postClientAuthLoginAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $user = $this->getUser();
        if ($user->isBanned()) {
            // user is banned
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        $loginData = new UserLoginData();

        $payload = json_decode($request->getContent(), true);

        if (!is_null($payload)) {
            $form = $this->createForm(new UserLoginType(), $loginData);
            $form->handleRequest($request);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
        }

        $responseArray = $this->handleClientUserLogin($request, $user, $loginData);

        $view = new View($responseArray);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('login')));

        return $view;
    }

    /**
     * @param Request       $request
     * @param User          $user
     * @param UserLoginData $login
     *
     * @return View
     *
     * @throws \Exception
     */
    private function handleClientUserLogin(
        Request $request,
        $user,
        $login
    ) {
        $data = array();

        $userClient = $login->getClient();

        $em = $this->getDoctrine()->getManager();

        // save or update user client
        $userClient = $this->saveUserClient(
            $em,
            $request,
            $userClient
        );
        $data['client'] = $userClient;

        if (!is_null($user)) {
            // save or refresh user token
            $userToken = $this->saveUserToken(
                $em,
                $user,
                $userClient
            );

            $data['user'] = $user;
            $data['token'] = $userToken;
        }

        $em->flush();

        return $data;
    }

    /**
     * @param EntityManager $em
     * @param Request       $request
     * @param UserClient    $userClient
     *
     * @return object|UserClient
     */
    private function saveUserClient(
        $em,
        $request,
        $userClient
    ) {
        // get incoming data
        $id = null;
        $name = null;
        $os = null;
        $version = null;

        if (!is_null($userClient)) {
            $id = $userClient->getId();
            $name = $userClient->getName();
            $os = $userClient->getOs();
            $version = $userClient->getVersion();
        }

        // save or update to db
        $client = null;

        if (!is_null($id)) {
            $client = $this->getRepo('User\UserClient')->find($id);
        }

        $now = new \DateTime('now');

        if (is_null($client)) {
            $client = new UserClient();
            $client->setCreationDate($now);

            $em->persist($client);
        }

        $client->setName($name);
        $client->setOs($os);
        $client->setVersion($version);
        $client->setIpAddress($request->getClientIp());
        $client->setModificationDate($now);

        // save to db
        $em->flush();

        return $client;
    }

    /**
     * @param EntityManager $em
     * @param User          $user
     * @param UserClient    $userClient
     *
     * @return UserToken
     */
    protected function saveUserToken(
        $em,
        $user,
        $userClient
    ) {
        $userToken = $this->getRepo('User\UserToken')->findOneBy(array(
            'user' => $user,
            'client' => $userClient,
        ));

        if (is_null($userToken)) {
            $userToken = new UserToken();
            $userToken->setUser($user);
            $userToken->setUserId($user->getId());
            $userToken->setClient($userClient);
            $userToken->setClientId($userClient->getId());
            $userToken->setToken($this->generateRandomToken());

            $em->persist($userToken);
        }

        // refresh creation date
        $userToken->setCreationDate(new \DateTime('now'));
        $userToken->setOnline(true);

        return $userToken;
    }
}
