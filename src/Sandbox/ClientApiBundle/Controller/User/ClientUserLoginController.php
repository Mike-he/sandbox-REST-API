<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserLoginController;
use Sandbox\ApiBundle\Entity\Error\Error;
use Sandbox\ClientApiBundle\Data\User\UserLoginData;
use Sandbox\ClientApiBundle\Form\User\UserLoginType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Login controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientUserLoginController extends UserLoginController
{
    /**
     * Login.
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
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postClientUserLoginAction(
        Request $request
    ) {
        // check security & get client
        $error = new Error();
        $user = $this->getUserIfAuthenticated($error);

        if (is_null($user)) {
            return $this->customErrorView(
                401,
                $error->getCode(),
                $error->getMessage()
            );
        }

        // get globals
        $globals = $this->getGlobals();

        $customerPhone = $globals['customer_service_phone'];
        $translated = $this->get('translator')->trans(self::ERROR_ACCOUNT_BANNED_MESSAGE);
        $bannedMessage = $translated.$customerPhone;

        if ($user->isBanned()) {
            // user is banned
            return $this->customErrorView(
                401,
                self::ERROR_ACCOUNT_BANNED_CODE,
                $bannedMessage
            );
        }

        $login = new UserLoginData();

        $payload = json_decode($request->getContent(), true);

        if (!is_null($payload)) {
            $form = $this->createForm(new UserLoginType(), $login);
            $form->handleRequest($request);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
        }

        $responseArray = $this->handleClientUserLogin($request, $user, $login);

        // response
        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('login')));

        return $view->setData($responseArray);
    }
}
