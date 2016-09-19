<?php

namespace Sandbox\AdminApiBundle\Controller;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AdminRestController extends SandboxRestController
{
    const ERROR_ACCOUNT_NONEXISTENT_CODE = 401002;
    const ERROR_ACCOUNT_NONEXISTENT_MESSAGE = 'client.login.account_non_existent';

    const ERROR_ACCOUNT_WRONG_PASSWORD_CODE = 401003;
    const ERROR_ACCOUNT_WRONG_PASSWORD_MESSAGE = 'client.login.wrong_password';

    /**
     * @return User $admin
     *
     * @throws UnauthorizedHttpException
     */
    protected function checkAdminLoginSecurity()
    {
        $auth = $this->getSandboxAuthorization(self::SANDBOX_ADMIN_LOGIN_HEADER);

        $admin = $this->getRepo('User\User')->findOneBy(array(
            'username' => $auth->getUsername(),
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

        $admin = $this->getRepo('User\User')->findOneBy(array(
            'phoneCode' => $phoneCode,
            'phone' => $phone,
        ));

        if (is_null($admin)) {
            $error->setCode(self::ERROR_ACCOUNT_NONEXISTENT_CODE);
            $error->setMessage(self::ERROR_ACCOUNT_NONEXISTENT_MESSAGE);

            return;
        }

        if ($auth->getPassword() != $admin->getPassword()) {
            $error->setCode(self::ERROR_ACCOUNT_WRONG_PASSWORD_CODE);
            $error->setMessage(self::ERROR_ACCOUNT_WRONG_PASSWORD_MESSAGE);

            return;
        }

        // check admin is existed
        $adminPositionUser = $this->getRepo('Admin\AdminPositionUserBinding')->findOneBy(array(
            'userId' => $admin->getId(),
        ));

        if (is_null($adminPositionUser)) {
            throw new AccessDeniedHttpException(null, self::NOT_ALLOWED_MESSAGE);
        }

        return $admin;
    }
}
