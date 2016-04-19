<?php

namespace Sandbox\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Entity\Auth\Auth;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Traits\CurlUtil;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sandbox\ApiBundle\Entity\Buddy\Buddy;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Company\Company;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Sandbox\ApiBundle\Constants\BundleConstants;
use Sandbox\ApiBundle\Constants\DoorAccessConstants;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermissionMap;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminType;

class SandboxRestController extends FOSRestController
{
    use DoorAccessTrait;
    use CurlUtil;

    // TODO move constants to constant folder

    const NOT_ALLOWED_MESSAGE = 'You are not allowed to perform this action';

    const NOT_FOUND_MESSAGE = 'This resource does not exist';

    const BAD_PARAM_MESSAGE = 'Bad parameters';

    const CONFLICT_MESSAGE = 'This resource already exists';

    const HTTP_STATUS_OK = 200;
    const HTTP_STATUS_OK_NO_CONTENT = 204;

    const VERIFICATION_CODE_LENGTH = 6;

    const HTTP_HEADER_AUTH = 'authorization';

    const SANDBOX_CUSTOM_HEADER = 'Sandbox-Auth: ';

    const UNAUTHED_API_CALL = 'Unauthorized Request';

    const ENCODE_METHOD_MD5 = 'md5';

    const ENCODE_METHOD_SHA1 = 'sha1';

    const SANDBOX_ADMIN_LOGIN_HEADER = 'sandboxadminauthorization';

    const SANDBOX_CLIENT_LOGIN_HEADER = 'sandboxclientauthorization';

    const ADMIN_COOKIE_NAME = 'sandbox_admin_token';

    const SALES_COOKIE_NAME = 'sandbox_sales_admin_token';

    const SHOP_COOKIE_NAME = 'sandbox_shop_admin_token';

    //-------------------- Global --------------------//

    /**
     * @return mixed
     */
    protected function getGlobals()
    {
        // get globals
        return $this->container->get('twig')->getGlobals();
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    protected function getGlobal(
        $key
    ) {
        // get globals
        $globals = $this->container->get('twig')->getGlobals();

        return $globals[$key];
    }

    /**
     * @return mixed
     */
    protected function getContainer()
    {
        // get container
        return $this->container;
    }

    //-------------------- Repo --------------------//

    /**
     * @param $repo
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepo(
        $repo
    ) {
        return $this->getDoctrine()->getRepository(
            BundleConstants::BUNDLE.':'.$repo
        );
    }

    //--------------------get user's info--------------------//

    /**
     * Get the jid of the guy who's making the API call.
     *
     * @return int
     */
    protected function getAdminId()
    {
        return $this->getUser()->getAdminId();
    }

    /**
     * Get the id of the guy who's making the API call.
     *
     * @return int
     */
    protected function getUserId()
    {
        return $this->getUser()->getUserId();
    }

    /**
     * @param $userId
     */
    protected function throwAccessDeniedIfNotSameUser(
        $userId
    ) {
        if ($this->getUserId() != $userId) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }
    }

    //-------------------- check user auth provided --------------------//

    /**
     * @return bool
     */
    protected function isAuthProvided()
    {
        $headers = apache_request_headers();
        $authHeaderKey = 'Authorization';

        if (!array_key_exists($authHeaderKey, $headers)) {
            return false;
        }

        $auth = $headers[$authHeaderKey];
        if (is_null($auth) || empty($auth)) {
            return false;
        }

        return true;
    }

    //-------------------- check admin permission --------------------//

    /**
     * Check admin's permission, is allowed to operate.
     *
     * @param int          $adminId
     * @param string       $typeKey
     * @param string|array $permissionKeys
     * @param int          $opLevel
     *
     * @throws AccessDeniedHttpException
     */
    protected function throwAccessDeniedIfAdminNotAllowed(
        $adminId,
        $typeKey,
        $permissionKeys = null,
        $opLevel = 0
    ) {
        $myPermission = null;

        // get admin
        $admin = $this->getRepo('Admin\Admin')->find($adminId);
        $type = $admin->getType();

        // if user is super admin, no need to check others
        if (AdminType::KEY_SUPER === $type->getKey()) {
            return;
        }

        // if admin type doesn't match, then throw exception
        if ($typeKey != $type->getKey()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        if (is_null($permissionKeys) || empty($permissionKeys)) {
            return;
        }

        // if permission key is string
        if (is_string($permissionKeys)) {
            $permission = $this->getRepo('Admin\AdminPermission')->findOneByKey($permissionKeys);

            // check user's permission
            $myPermission = $this->getRepo('Admin\AdminPermissionMap')
                ->findOneBy(array(
                    'adminId' => $adminId,
                    'permissionId' => $permission->getId(),
                ));
            if (is_null($myPermission)) {
                throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
            }
        }

        // if permission key is array
        if (is_array($permissionKeys)) {
            $permissionFound = false;

            foreach ($permissionKeys as $permissionKey) {
                $permission = $this->getRepo('Admin\AdminPermission')->findOneByKey($permissionKey);

                // check user's permission
                $myPermission = $this->getRepo('Admin\AdminPermissionMap')
                    ->findOneBy(array(
                        'adminId' => $adminId,
                        'permissionId' => $permission->getId(),
                    ));
                if (!is_null($myPermission)) {
                    $permissionFound = true;
                    break;
                }
            }

            if (!$permissionFound) {
                throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
            }
        }

        // check user's operation level
        if (is_null($myPermission) || $myPermission->getOpLevel() < $opLevel) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }
    }

    /**
     * @param User $userId
     *
     * @return null|Company
     */
    protected function getCompanyIfMember(
        $userId
    ) {
        $companyMember = $this->getRepo('Company\CompanyMember')->findOneByUserId($userId);
        if (is_null($companyMember)) {
            return;
        }

        return $companyMember->getCompany();
    }

    /**
     * Get the username of the guy who's making the API call.
     *
     * @return string
     */
    protected function getUsername()
    {
        //TODO move in a common controller
        $token = $this->container->get('security.token_storage')->getToken();

        return $token->getUsername();
    }

    //--------------------call remote api--------------------//

    /**
     * @param $auth
     *
     * @return string|null
     */
    protected function getCardNoIfUserAuthorized(
        $auth = null
    ) {
        if (is_null($auth)) {
            // get auth
            $headers = apache_request_headers();
            $auth = $headers['Authorization'];
        }

        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].
            $globals['crm_api_client_user_account_authentication'];

        // init curl
        $ch = curl_init($apiUrl);

        $response = $this->callAPI(
            $ch,
            'GET',
            array('Authorization: '.$auth)
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param $userId
     *
     * @return bool
     */
    protected function checkUserAuthorized(
        $userId
    ) {
        $user = $this->getRepo('User\User')->find($userId);
        if (is_null($user)) {
            return false;
        }

        return $user->isAuthorized();
    }

    /**
     * @param $userId
     *
     * @return string|null
     */
    protected function getCardNoByUser(
        $userId
    ) {
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $key = $globals['sandbox_auth_key'];

        $contentMd5 = md5($key);

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].
            $globals['crm_api_admin_user_account_cardno'];
        $apiUrl = preg_replace('/{userId}.*?/', "$userId", $apiUrl);

        // init curl
        $ch = curl_init($apiUrl);

        $response = $this->callAPI(
            $ch,
            'GET',
            array(self::SANDBOX_CUSTOM_HEADER.$contentMd5)
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param $userId
     * @param $data
     *
     * @return string|null
     */
    protected function postBalanceChange(
        $userId,
        $amount,
        $tradeId,
        $channel,
        $paidAmount,
        $type = null
    ) {
        $json = $this->createJsonForCharge(
            $tradeId,
            $amount,
            $channel,
            $paidAmount,
            $type
        );
        $auth = $this->authAuthMd5($json);

        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].
            $globals['crm_api_admin_user_balance_change'];
        $apiUrl = preg_replace('/{userId}.*?/', "$userId", $apiUrl);

        // init curl
        $ch = curl_init($apiUrl);

        $response = $this->callAPI(
            $ch,
            'POST',
            array(self::SANDBOX_CUSTOM_HEADER.$auth),
            $json
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result['balance'];
    }

    /**
     * @param int    $userId
     * @param string $amount
     * @param string $tradeId
     *
     * @return string|null
     */
    protected function postConsumeBalance(
        $userId,
        $amount,
        $tradeId
    ) {
        $json = $this->createJsonForConsume(
            $tradeId,
            $amount
        );
        $auth = $this->authAuthMd5($json);

        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].
            $globals['crm_api_admin_user_consume'];
        $apiUrl = preg_replace('/{userId}.*?/', "$userId", $apiUrl);

        // init curl
        $ch = curl_init($apiUrl);

        $response = $this->callAPI(
            $ch,
            'POST',
            array(self::SANDBOX_CUSTOM_HEADER.$auth),
            $json);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result['consume_amount'];
    }

    /**
     * @param $userId
     * @param $data
     *
     * @return string|null
     */
    protected function postAccountUpgrade(
        $userId,
        $productId,
        $tradeId
    ) {
        $content = [
            'product_id' => $productId,
            'trade_id' => $tradeId,
        ];
        $auth = $this->authAuthMd5(json_encode($content));

        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].
            $globals['crm_api_admin_user_account_upgrade'];
        $apiUrl = preg_replace('/{userId}.*?/', "$userId", $apiUrl);

        // init curl
        $ch = curl_init($apiUrl);

        $response = $this->callAPI(
            $ch,
            'POST',
            array(self::SANDBOX_CUSTOM_HEADER.$auth),
            json_encode($content)
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    protected function authAuthMd5(
        $json
    ) {
        $globals = $this->container->get('twig')->getGlobals();

        $key = $globals['sandbox_auth_key'];

        $contentMd5 = md5($json.$key);
        $contentMd5 = strtoupper($contentMd5);

        return $contentMd5;
    }

    /**
     * @return mixed
     */
    protected function createJsonForCharge(
        $orderNumber,
        $amount,
        $payType,
        $paidAmount,
        $type = null
    ) {
        $content = [
            'amount' => $amount,
            'pay_type' => $payType,
            'trade_id' => $orderNumber,
            'paid_amount' => $paidAmount,
        ];
        if (!is_null($type)) {
            $content['type'] = $type;
        }

        return json_encode($content);
    }

    /**
     * @return mixed
     */
    protected function createJsonForConsume(
        $orderNumber,
        $amount
    ) {
        $content = [
            'amount' => $amount,
            'trade_id' => $orderNumber,
        ];

        return json_encode($content);
    }

    /**
     * @param $auth
     *
     * @return string|null
     */
    protected function getExpireDateIfUserVIP(
        $auth = null
    ) {
        if (is_null($auth)) {
            // get auth
            $headers = apache_request_headers();
            $auth = $headers['Authorization'];
        }

        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].
            $globals['crm_api_client_user_account_vip'];

        // init curl
        $ch = curl_init($apiUrl);

        $response = $this->callAPI(
            $ch,
            'GET',
            array('Authorization: '.$auth)
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);
        if (!$result['is_vip']) {
            return;
        }

        return $result['expiration_time'];
    }

    /**
     * @param $auth
     *
     * @return string|null
     */
    protected function getOwnBalance(
        $auth = null
    ) {
        if (is_null($auth)) {
            // get auth
            $headers = apache_request_headers();
            $auth = $headers['Authorization'];
        }

        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].
            $globals['crm_api_client_own_account_balance'];

        // init curl
        $ch = curl_init($apiUrl);

        $response = $this->callAPI(
            $ch,
            'GET',
            array('Authorization: '.$auth)
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result['balance'];
    }

    /**
     * @param $auth
     *
     * @return string|null
     */
    protected function getDiscountPriceForOrder(
        $ruleId,
        $productId,
        $period,
        $startDate,
        $endDate,
        $isRenew,
        $auth = null
    ) {
        if (is_null($auth)) {
            // get auth
            $headers = apache_request_headers();
            $auth = $headers['Authorization'];
        }
        $startDate = $startDate->format('Y-m-d H:i:s');
        $endDate = $endDate->format('Y-m-d H:i:s');

        $dataArray = [
            'product_id' => $productId,
            'rent_amount' => $period,
            'start_time' => $startDate,
            'end_time' => $endDate,
            'is_renew' => $isRenew,
        ];
        $data = json_encode($dataArray);

        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].
            $globals['crm_api_client_user_price_calculate'];
        $apiUrl = preg_replace('/{rule_id}.*?/', "$ruleId", $apiUrl);
        // init curl
        $ch = curl_init($apiUrl);

        $response = $this->callAPI(
            $ch,
            'POST',
            array('Authorization: '.$auth),
            $data);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param $ruleId
     * @param null $auth
     *
     * @return mixed|void
     */
    protected function getSalesPriceRuleForOrder(
        $ruleId,
        $auth = null
    ) {
        if (is_null($auth)) {
            // get auth
            $headers = apache_request_headers();
            $auth = $headers['Authorization'];
        }

        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].
            $globals['crm_api_sales_admin_price_rule_info'];
        $apiUrl = preg_replace('/{rule_id}.*?/', "$ruleId", $apiUrl);
        // init curl
        $ch = curl_init($apiUrl);

        $response = $this->callAPI(
            $ch,
            'GET',
            array('Authorization: '.$auth)
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result;
    }

    //--------------------common functions--------------------//

    /**
     * @param int $limit
     *
     * @return int
     */
    protected function getLoadMoreLimit(
        $limit
    ) {
        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // set max limit
        if ($limit > $globals['load_more_limit']) {
            $limit = $globals['load_more_limit'];
        }

        return $limit;
    }

    /**
     * Send email.
     *
     * @param $subject
     * @param $toEmail
     * @param $toName
     * @param $twigFilePath
     * @param $twigArray
     */
    protected function sendEmail(
        $subject,
        $toEmail,
        $toName,
        $twigFilePath,
        $twigArray
    ) {
        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // send verification URL to email
        $mailer = $this->get('mailer');
        $message = $mailer->createMessage()
            ->setSubject($subject)
            ->setFrom(array($globals['email_from_address'] => $globals['email_from_name']))
            ->setTo(array($toEmail => $toName))
            ->setBody(
                $this->renderView(
                    $twigFilePath,
                    $twigArray
                ),
                'text/html'
            );
        $mailer->send($message);
    }

    //--------------------for user default value--------------------//
    /**
     * @param $username
     *
     * @return string
     */
    protected function constructXmppJid(
        $username
    ) {
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        return $username.'@'.$globals['xmpp_domain'];
    }

    //--------------------generate default verification code and token--------------------//
    /**
     * @param $digits
     *
     * @return string
     */
    protected function generateVerificationCode(
        $digits
    ) {
        return str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
    }

    /**
     * @return string
     */
    protected function generateRandomToken()
    {
        return md5(uniqid(rand(), true));
    }

    //--------------------throw customer http error --------------------//
    /**
     * Custom error view.
     *
     * @param $statusCode
     * @param $errorCode
     * @param $errorMessage
     *
     * @return View
     */
    protected function customErrorView(
        $statusCode,
        $errorCode,
        $errorMessage
    ) {
        $translated = $this->get('translator')->trans($errorMessage);

        $view = new View();
        $view->setStatusCode($statusCode);
        $view->setData(array(
            'code' => $errorCode,
            'message' => $translated,
        ));
        $view->getData();

        return $view;
    }

    /**
     * @param $code
     * @param $message
     *
     * @return array
     */
    protected function setErrorArray(
        $code,
        $message
    ) {
        $error = [
            'code' => $code,
            'message' => $message,
        ];

        return $error;
    }

    /**
     *
     */
    private function _throwHttpErrorIfNull(
        $item,
        $exception
    ) {
        if (!is_null($item)) {
            return;
        }
        throw $exception;
    }

    /**
     * @throws NotFoundHttpException when resource not exist
     */
    protected function throwNotFoundIfNull($resource, $message)
    {
        $this->_throwHttpErrorIfNull(
            $resource,
            $this->createNotFoundException($message)
        );
    }

    /**
     *
     */
    protected function throwAccessDeniedIfNull(
        $item
    ) {
        $this->_throwHttpErrorIfNull(
            $item,
            $this->createAccessDeniedException()
        );
    }

    /**
     * TODO : move that in something specific to Approval related
     * class.
     */
    protected function throwAccessDeniedIfNotAffiliated(
        $type,
        $jid,
        $approvalId
    ) {
        $affiliateRepo = $this->getAffilationRepo($type);
        $affilation = $affiliateRepo->findOneBy(
            array(
                'jid' => $jid,
                'itemid' => $approvalId,
            )
        );

        $this->throwAccessDeniedIfNull($affilation);
    }

    /**
     * @param $companyId
     * @param $userId
     */
    protected function throwAsscessDeniedIfNotCompanyOwner(
        $companyId,
        $userId
    ) {
        $company = $this->getRepo('Company')->findOneBy(
            array(
                'id' => $companyId,
                'creatorid' => $userId,
            )
        );

        $this->throwAccessDeniedIfNull($company);
    }

    /**
     * @param $userId
     *
     * @return mixed|void
     */
    protected function postUserAccount(
        $userId
    ) {
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $key = $globals['sandbox_auth_key'];

        $contentMd5 = md5($key);

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].
            $globals['crm_api_admin_user_create'];
        $apiUrl = preg_replace('/{userId}.*?/', "$userId", $apiUrl);

        // init curl
        $ch = curl_init($apiUrl);

        $response = $this->callAPI(
            $ch,
            'POST',
            array(self::SANDBOX_CUSTOM_HEADER.$contentMd5)
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param $userId
     *
     * @return mixed|void
     */
    protected function getVipStatusByUserId(
        $userId
    ) {
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $key = $globals['sandbox_auth_key'];

        $contentMd5 = md5($key);

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].
            $globals['crm_api_admin_user_account_vip'];
        $apiUrl = preg_replace('/{userId}.*?/', "$userId", $apiUrl);

        // init curl
        $ch = curl_init($apiUrl);

        $response = $this->callAPI(
            $ch,
            'GET',
            array(self::SANDBOX_CUSTOM_HEADER.$contentMd5)
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);
        if (!$result['is_vip']) {
            return;
        }

        return $result['expiration_time'];
    }

    //---------------------------------------- XMPP User ----------------------------------------//

    /**
     * @param string $username
     * @param string $password
     * @param string $name
     * @param string $fullJID
     *
     * @return bool
     */
    protected function updateXmppUser(
        $username,
        $password = null,
        $name = null,
        $fullJID = null
    ) {
        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // set ezUser secret to basic auth
        $ezuserNameSecret = $globals['openfire_plugin_bstuser_property_name_ezuser'].':'.
            $globals['openfire_plugin_bstuser_property_secret_ezuser'];

        $auth = 'Basic '.base64_encode($ezuserNameSecret);

        // Openfire API URL
        $apiUrl = $globals['openfire_innet_url'].
            $globals['openfire_plugin_bstuser'].
            $globals['openfire_plugin_bstuser_users'];

        // request json
        $jsonData = $this->createXmppUserPayload($username, $password, $name, $fullJID);

        // init curl
        $ch = curl_init($apiUrl);

        // get then response when post OpenFire API
        $response = $this->callAPI(
            $ch,
            'PUT',
            array('Authorization: '.$auth),
            $jsonData
        );

        if (!$response) {
            return false;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return false;
        }

        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $name
     * @param string $fullJID
     *
     * @return string
     */
    protected function createXmppUserPayload(
        $username,
        $password,
        $name,
        $fullJID
    ) {
        $dataArray = array('username' => $username);

        if (!is_null($password)) {
            $dataArray['password'] = $password;
        }

        if (!is_null($name)) {
            $dataArray['name'] = $name;
        }

        if (!is_null($fullJID)) {
            $dataArray['fulljid'] = $fullJID;
        }

        return json_encode($dataArray);
    }

    /**
     * @param array $users
     */
    protected function addBuddyToUser(
        $users
    ) {
        // find service account as buddy
        $serviceUser = $this->getRepo('User\User')->findOneByXmppUsername(User::XMPP_SERVICE);
        if (is_null($serviceUser)) {
            return;
        }

        $em = $this->getDoctrine()->getManager();

        foreach ($users as $user) {
            // set service account as buddy for user
            $this->saveBuddy(
                $em,
                $user,
                $serviceUser
            );

            // set user as buddy for service account
            $this->saveBuddy(
                $em,
                $serviceUser,
                $user
            );
        }

        $em->flush();
    }

    /**
     * @param object $em
     * @param User   $user
     * @param User   $buddy
     */
    protected function saveBuddy(
        $em,
        $user,
        $buddy
    ) {
        $myBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
            'user' => $user,
            'buddy' => $buddy,
        ));

        if (is_null($myBuddy)) {
            $myBuddy = new Buddy();
            $myBuddy->setUser($user);
            $myBuddy->setBuddy($buddy);

            $em->persist($myBuddy);
        }
    }

    //---------------------------------------- Food Payment ----------------------------------------//

    /**
     * @param $data
     *
     * @return mixed|void
     */
    protected function foodPaymentCallback(
        $data
    ) {
        $globals = $this->getGlobals();
        $key = sha1($globals['sandbox_auth_key']);

        // CRM API URL
        $apiUrl = $globals['food_api_url'].
            $globals['food_api_payment_callback'];

        // init curl
        $ch = curl_init($apiUrl);

        $response = $this->callAPI(
            $ch,
            'POST',
            array(self::SANDBOX_CUSTOM_HEADER.$key),
            $data
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param string $auth
     * @param string $method
     */
    protected function encodedKeysComparison(
        $auth,
        $method = self::ENCODE_METHOD_SHA1
    ) {
        $globals = $this->getGlobals();
        $key = sha1($globals['sandbox_auth_key']);

        if ($method == self::ENCODE_METHOD_MD5) {
            $key = md5($globals['sandbox_auth_key']);
        }

        if (strtoupper($auth) !== strtoupper($key)) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }
    }

    //---------------------------------------- Door Access Command ----------------------------------------//

    /**
     * @param $base
     * @param $userId
     * @param $cardNo
     * @param $roomDoors
     * @param $order
     *
     * @throws \Exception
     */
    protected function callSetCardAndRoomCommand(
        $base,
        $userId,
        $cardNo,
        $roomDoors,
        $order
    ) {
        try {
            $kernel = $this->get('kernel');
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput(array(
                'command' => 'CardAndRoom:Set',
                'base' => $base,
                'userId' => $userId,
                'cardNo' => $cardNo,
                'roomDoors' => $roomDoors,
                'order' => $order,
            ));

            $output = new NullOutput();
            $application->run($input, $output);
        } catch (\Exception $e) {
            error_log('Door Access Set Card and Room Command Error');
        }
    }

    /**
     * @param $base
     * @param $orderId
     */
    protected function callRepealRoomOrderCommand(
        $base,
        $orderId
    ) {
        try {
            $kernel = $this->get('kernel');
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput(array(
                'command' => 'RoomOrder:Repeal',
                'base' => $base,
                'orderId' => $orderId,
            ));

            $output = new NullOutput();
            $application->run($input, $output);
        } catch (\Exception $e) {
            error_log('Door Access Repeal Room Order Command Error');
        }
    }

    /**
     * @param $base
     * @param $userArray
     * @param $roomDoors
     * @param $order
     */
    protected function callSetRoomOrderCommand(
        $base,
        $userArray,
        $roomDoors,
        $order
    ) {
        try {
            $kernel = $this->get('kernel');
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput(array(
                'command' => 'RoomOrder:Set',
                'base' => $base,
                'userArray' => $userArray,
                'roomDoors' => $roomDoors,
                'order' => $order,
            ));

            $output = new NullOutput();
            $application->run($input, $output);
        } catch (\Exception $e) {
            error_log('Door Access Set Room Order Command Error');
        }
    }

    /**
     * @param $base
     * @param $orderId
     * @param $userArray
     */
    protected function callRemoveFromOrderCommand(
        $base,
        $orderId,
        $userArray
    ) {
        try {
            $kernel = $this->get('kernel');
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput(array(
                'command' => 'RoomOrderUser:Remove',
                'base' => $base,
                'userArray' => $userArray,
                'orderId' => $orderId,
            ));

            $output = new NullOutput();
            $application->run($input, $output);
        } catch (\Exception $e) {
            error_log('Door Access Remove User From Room Order Command Error');
        }
    }

    /**
     * @param $userId
     * @param $cardNo
     * @param $method
     */
    protected function callUpdateCardStatusCommand(
        $userId,
        $cardNo,
        $method,
        $oldCardNo = null
    ) {
        try {
            $kernel = $this->get('kernel');
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput(array(
                'command' => 'Card:Update',
                'userId' => $userId,
                'cardNo' => $cardNo,
                'method' => $method,
                'oldCardNo' => $oldCardNo,
            ));

            $output = new NullOutput();
            $application->run($input, $output);
        } catch (\Exception $e) {
            error_log('Door Access Update Card Status Command Error');
        }
    }

    /**
     * @param $userId
     * @param $userArray
     *
     * @return mixed
     */
    public function getUserArrayIfAuthed(
        $base,
        $userId,
        $userArray
    ) {
        $userEntity = $this->getRepo('User\User')->find($userId);
        $result = $this->getCardNoByUser($userId);
        if (
            !is_null($result) &&
            $result['status'] === DoorController::STATUS_AUTHED &&
            !$userEntity->isBanned()
        ) {
            $this->setEmployeeCardForOneBuilding(
                $base,
                $userId,
                $result['card_no']
            );

            $empUser = ['empid' => $userId];
            array_push($userArray, $empUser);
        }

        return $userArray;
    }

    /**
     * @param $order
     */
    public function syncAccessByOrder(
        $base,
        $order
    ) {
        $orderId = $order->getId();
        $roomId = $order->getProduct()->getRoom()->getId();
        $roomDoors = $this->getRepo('Room\RoomDoors')->findByRoomId($roomId);
        if (empty($roomDoors)) {
            return;
        }

        // check if order cancelled
        if ($order->getStatus() == ProductOrder::STATUS_CANCELLED) {
            // cancel order
            $this->callRepealRoomOrderCommand(
                $base,
                $orderId
            );
        } else {
            // get add action controls
            $addControls = $this->getRepo('Door\DoorAccess')->getAllWithoutAccess(
                DoorAccessConstants::METHOD_ADD,
                $orderId
            );

            // get delete action controls
            $deleteControls = $this->getRepo('Door\DoorAccess')->getAllWithoutAccess(
                DoorAccessConstants::METHOD_DELETE,
                $orderId
            );

            if (!empty($addControls)) {
                $userArray = [];
                foreach ($addControls as $addControl) {
                    $userArray = $this->getUserArrayIfAuthed(
                        $base,
                        $addControl->getUserId(),
                        $userArray
                    );
                }

                // set room access
                if (!empty($userArray)) {
                    $this->callSetRoomOrderCommand(
                        $base,
                        $userArray,
                        $roomDoors,
                        $order
                    );
                }
            }

            if (!empty($deleteControls)) {
                $removeUserArray = [];
                foreach ($deleteControls as $deleteControl) {
                    $userId = $deleteControl->getUserId();
                    $result = $this->getCardNoByUser($userId);
                    if ($result['status'] !== DoorController::STATUS_UNAUTHED) {
                        $empUser = ['empid' => $userId];
                        array_push($removeUserArray, $empUser);
                    }
                }

                // remove room access
                if (!empty($removeUserArray)) {
                    $this->callRemoveFromOrderCommand(
                        $base,
                        $orderId,
                        $removeUserArray
                    );
                }
            }
        }
    }

    /**
     * @param $adminId
     * @param $permissionKeyArray
     * @param $opLevel
     *
     * @return array
     */
    protected function getMyShopIds(
        $adminId,
        $permissionKeyArray,
        $opLevel = ShopAdminPermissionMap::OP_LEVEL_VIEW
    ) {
        // get admin
        $admin = $this->getRepo('Shop\ShopAdmin')->find($adminId);
        $type = $admin->getType();

        // get permission
        if (empty($permissionKeyArray)) {
            return array();
        }

        $permissions = array();
        if (is_array($permissionKeyArray)) {
            foreach ($permissionKeyArray as $key) {
                $permission = $this->getRepo('Shop\ShopAdminPermission')->findOneByKey($key);

                if (!is_null($permission)) {
                    array_push($permissions, $permission->getId());
                }
            }
        }

        if (ShopAdminType::KEY_SUPER === $type->getKey()) {
            // if user is super admin, get all buildings
            $myBuildings = $this->getRepo('Room\RoomBuilding')->getBuildingsByCompany($admin->getCompanyId());

            $shopsArray = array();
            foreach ($myBuildings as $building) {
                if (is_null($building)) {
                    continue;
                }

                $shops = $this->getRepo('Shop\Shop')->getMyShopByBuilding($building['id']);

                $shopsArray = array_merge($shopsArray, $shops);
            }
        } else {
            // platform admin get binding buildings
            $shopsArray = $this->getRepo('Shop\ShopAdminPermissionMap')->getMyShops(
                $adminId,
                $permissions,
                $opLevel
            );
        }

        if (empty($shopsArray)) {
            return $shopsArray;
        }

        $ids = array();
        foreach ($shopsArray as $shop) {
            array_push($ids, $shop['shopId']);
        }

        return $ids;
    }

    /**
     * @param $headerKey
     *
     * @return Auth
     */
    protected function getSandboxAuthorization(
        $headerKey
    ) {
        // get auth
        $headers = apache_request_headers();
        if (!array_key_exists($headerKey, $headers)) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }
        $authHeader = $headers[$headerKey];
        $adminString = base64_decode($authHeader, true);
        $adminArray = explode(':', $adminString);

        if (count($adminArray) != 2) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        $auth = new Auth();
        $auth->setUsername($adminArray[0]);
        $auth->setPassword($adminArray[1]);

        return $auth;
    }
}
