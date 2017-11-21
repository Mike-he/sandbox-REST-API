<?php

namespace Sandbox\AdminApiBundle\Controller;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AdminRestController extends SandboxRestController
{
    const ERROR_ACCOUNT_NONEXISTENT_CODE = 401002;
    const ERROR_ACCOUNT_NONEXISTENT_MESSAGE = 'client.login.account_non_existent';

    const ERROR_ACCOUNT_WRONG_PASSWORD_CODE = 401003;
    const ERROR_ACCOUNT_WRONG_PASSWORD_MESSAGE = 'client.login.wrong_password';

    const ERROR_WRONG_CHECK_CODE_CODE = 400003;
    const ERROR_WRONG_CHECK_CODE_MESSAGE = 'client.login.wrong_check_code';

    const ERROR_CURRENT_USER_IS_NOT_AN_ADMIN_CODE = 401005;
    const ERROR_CURRENT_USER_IS_NOT_AN_ADMIN_MESSAGE = 'client.login.current_user_is_not_an_admin';

    const ERROR_EXPIRED_VERIFICATION_CODE = 400006;
    const ERROR_EXPIRED_VERIFICATION_MESSAGE = 'client.account.expired_verification';

    /**
     * @return SalesAdmin $admin
     *
     * @throws UnauthorizedHttpException
     */
    protected function checkAdminLoginSecurity()
    {
        $auth = $this->getSandboxAuthorization(self::SANDBOX_ADMIN_LOGIN_HEADER);
        $usernameArray = explode('-', $auth->getUsername());

        $admin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
            ->findOneBy(array(
                'phone' => $usernameArray[1],
                'password' => $auth->getPassword(),
            ));

        if (is_null($admin)) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        return $admin;
    }

    /**
     * @return User $admin
     *
     * @throws UnauthorizedHttpException
     */
    protected function checkAdminIsExisted($error)
    {
        //get auth
        $auth = $this->getSandboxAuthorization(self::SANDBOX_ADMIN_LOGIN_HEADER);

        $username = $auth->getUsername();
        if (is_null($username)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $usernameArray = explode('-', $username);
        if (count($usernameArray) != 2) {
            $error->setCode(self::ERROR_ACCOUNT_NONEXISTENT_CODE);
            $error->setMessage(self::ERROR_ACCOUNT_NONEXISTENT_MESSAGE);

            return;
        }

        $phoneCode = $usernameArray[0];
        $phone = $usernameArray[1];

        // check current user is existed
        $admin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
            ->findOneBy(array(
                'phoneCode' => $phoneCode,
                'phone' => $phone,
            ));

        if (is_null($admin)) {
            $error->setCode(self::ERROR_ACCOUNT_NONEXISTENT_CODE);
            $error->setMessage(self::ERROR_ACCOUNT_NONEXISTENT_MESSAGE);

            return;
        }

        // check the password of current user is correct
        if ($auth->getPassword() != $admin->getPassword()) {
            $error->setCode(self::ERROR_ACCOUNT_WRONG_PASSWORD_CODE);
            $error->setMessage(self::ERROR_ACCOUNT_WRONG_PASSWORD_MESSAGE);

            return;
        }

        // check admin is existed
        $adminPositionUser = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
            ->findOneBy(['userId' => $admin->getUserId()]);

        if (is_null($adminPositionUser)) {
            $error->setCode(self::ERROR_CURRENT_USER_IS_NOT_AN_ADMIN_CODE);
            $error->setMessage(self::ERROR_CURRENT_USER_IS_NOT_AN_ADMIN_MESSAGE);

            return;
        }

        return $admin;
    }
}
