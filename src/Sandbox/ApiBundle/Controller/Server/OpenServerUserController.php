<?php

namespace Sandbox\ApiBundle\Controller\Server;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Error\Error;
use Sandbox\ApiBundle\Entity\Server\OpenServerUser;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Sandbox\ApiBundle\Form\Server\OpenServerUserPostType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OpenServerUserController extends SandboxRestController
{
    const REQUEST_AUTH_HEADER = 'OpenServerAuth';

    const ERROR_INVALID_AUTH_HEAR_CODE = 400001;
    const ERROR_INVALID_AUTH_HEAR_MESSAGE = 'Invalid authorization headers';
    const ERROR_INVALID_SERVICE_LIST_CODE = 400002;
    const ERROR_INVALID_SERVICE_LIST_MESSAGE = 'Service is not in the white list';
    const ERROR_AUTH_KEY_CODE = 400003;
    const ERROR_AUTH_KEY_MESSAGE = 'Auth Key is not correct';
    const ERROR_INVALID_SERVER_IP_CODE = 400004;
    const ERROR_INVALID_SERVER_IP_MESSAGE = 'Server\'s IP is not in the white list';
    const ERROR_EMAIL_EMPTY_CODE = 400005;
    const ERROR_EMAIL_EMPTY_MESSAGE = 'Email content can\'t be empty';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="service",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     description="service key"
     * )
     *
     * @Route("/openserver/users")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postClientUserAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $serviceKey = $paramFetcher->get('service');
        $headers = array_change_key_case($_SERVER, CASE_LOWER);
        $data = json_decode($request->getContent(), true);

        // check auth headers
        if (!array_key_exists(mb_strtolower('http_'.self::REQUEST_AUTH_HEADER), $headers)) {
            return $this->customErrorView(
                400,
                self::ERROR_INVALID_AUTH_HEAR_CODE,
                self::ERROR_INVALID_AUTH_HEAR_MESSAGE
            );
        }

        $authKey = $headers[mb_strtolower('http_'.self::REQUEST_AUTH_HEADER)];

        // get ip
        $ip = $request->getClientIp();

        $error = new Error();

        // check api request permission
        $this->checkApiRequestPermission(
            $serviceKey,
            $authKey,
            $ip,
            $error
        );

        if (!is_null($error->getCode())) {
            return $this->customErrorView(
                400,
                $error->getCode(),
                $error->getMessage()
            );
        }

        $openServerUser = new OpenServerUser();

        // bind form
        $form = $this->createForm(new OpenServerUserPostType(), $openServerUser);
        $form->submit($data);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $email = $openServerUser->getEmail();
        $password = $openServerUser->getPassword();
        $name = $openServerUser->getName();

        if (is_null($email)) {
            return $this->customErrorView(
                400,
                self::ERROR_EMAIL_EMPTY_CODE,
                self::ERROR_EMAIL_EMPTY_MESSAGE
            );
        }

        // insert or update user judge by server user exists
        $user = $this->getRepo('User\User')->findOneBy(array(
            'email' => $email,
        ));

        if (!is_null($user)) {
            // update user
            $user->setPassword($password);

            $userProfile = $this->getRepo('User\UserProfile')->findOneBy(array(
                'userId' => $user->getId(),
            ));

            $userProfile->setName($name);

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        } else {
            // insert user
            $user = $this->generateSandboxUser(
                $email,
                $password,
                $name
            );
        }

        return new View(array(
            'userId' => $user->getId(),
        ));
    }

    /**
     * @param $serviceKey
     * @param $authKey
     * @param $ip
     * @param Error $error
     *
     * @return mixed
     */
    private function checkApiRequestPermission(
        $serviceKey,
        $authKey,
        $ip,
        $error
    ) {
        $server = $this->getRepo('Server\OpenServer')->findOneBy(array(
            'serviceKey' => $serviceKey,
        ));

        // check server list
        if (is_null($server)) {
            $error->setCode(self::ERROR_INVALID_SERVICE_LIST_CODE);
            $error->setMessage(self::ERROR_INVALID_SERVICE_LIST_MESSAGE);

            return $error;
        }

        // check auth
        if ($server->getAuthKey() != $authKey) {
            $error->setCode(self::ERROR_AUTH_KEY_CODE);
            $error->setMessage(self::ERROR_AUTH_KEY_MESSAGE);

            return $error;
        }

        $serverIps = explode(',', $server->getIp());

        if (!in_array($ip, $serverIps)) {
            $error->setCode(self::ERROR_INVALID_SERVER_IP_CODE);
            $error->setMessage(self::ERROR_INVALID_SERVER_IP_MESSAGE);

            return $error;
        }

        return $error;
    }

    /**
     * @param $email
     * @param $password
     * @param $name
     *
     * @return User
     */
    private function generateSandboxUser(
        $email,
        $password,
        $name
    ) {
        $em = $this->getDoctrine()->getManager();

        // set user
        $user = new User();

        $user->setEmail($email);
        $user->setPassword($password);
        $em->persist($user);

        // generate user profile
        $userProfile = new UserProfile();

        $userProfile->setName($name);
        $userProfile->setUser($user);
        $em->persist($userProfile);
        $em->flush();

        // generate xmpp user
        $response = $this->createXmppUser($user, $userProfile->getId());
        $user->setXmppUsername($response);

        $em->flush();

        return $user;
    }
}
