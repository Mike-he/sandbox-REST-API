<?php

namespace Sandbox\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Sandbox\AdminApiBundle\Controller\Admin\AdminPlatformController;
use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Entity\Auth\Auth;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Form\Log\LogType;
use Sandbox\ApiBundle\Traits\CurlUtil;
use Sandbox\ApiBundle\Traits\LogsTrait;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sandbox\ApiBundle\Entity\Buddy\Buddy;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\Company\Company;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Sandbox\ApiBundle\Constants\BundleConstants;
use Sandbox\ApiBundle\Constants\DoorAccessConstants;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;

class SandboxRestController extends FOSRestController
{
    use DoorAccessTrait;
    use CurlUtil;
    use LogsTrait;

    // TODO move constants to constant folder

    const NOT_ALLOWED_MESSAGE = 'You are not allowed to perform this action';

    const NOT_FOUND_MESSAGE = 'This resource does not exist';

    const BAD_PARAM_MESSAGE = 'Bad parameters';

    const CONFLICT_MESSAGE = 'This resource already exists';

    const PRECONDITION_NOT_SET = 'The precondition cookies not set';

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

    const HASH_ALGO_SHA256 = 'sha256';

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
        return $this->getUser()->getUserId();
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
        $headers = array_change_key_case($_SERVER, CASE_LOWER);
        $authHeaderKey = 'http_authorization';

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
     * Sample:
     * $permissionKeys = array(
     *   'key' => 'permission',
     *   'building_id' => 1,
     *   'shop_id' => 1,
     * )
     *
     * @param int          $adminId
     * @param string|array $permissionKeys
     * @param int          $opLevel
     * @param              $platform
     * @param              $salesCompanyId
     *
     * @throws AccessDeniedHttpException
     */
    protected function throwAccessDeniedIfAdminNotAllowed(
        $adminId,
        $permissionKeys = null,
        $opLevel = 0,
        $platform = null,
        $salesCompanyId = null
    ) {
        if (is_null($platform)) {
            // get platform sessions
            $sessions = $this->getPlatformSessions();
            $platform = $sessions['platform'];
            $salesCompanyId = $sessions['sales_company_id'];
        }

        // super admin
        $isSuperAdmin = $this->hasSuperAdminPosition(
            $adminId,
            $platform,
            $salesCompanyId
        );
        if ($isSuperAdmin) {
            return;
        }

        // if common admin, than get my permissions list
        $myPermissions = $this->getMyAdminPermissions(
            $adminId,
            $platform,
            $salesCompanyId
        );

        // check permissions
        foreach ($permissionKeys as $permissionKey) {
            $buildingId = isset($permissionKey['building_id']) ? $permissionKey['building_id'] : null;
            $shopId = isset($permissionKey['shop_id']) ? $permissionKey['shop_id'] : null;

            $pass = false;
            foreach ($myPermissions as $myPermission) {
                if ($permissionKey['key'] == $myPermission['key']
                    && $opLevel <= $myPermission['op_level']
                ) {
                    $pass = true;
                }

                if (!is_null($buildingId)) {
                    if ($buildingId == $myPermission['building_id']) {
                        $pass = true;
                    } else {
                        $pass = false;
                    }
                }

                if (!is_null($shopId)) {
                    if ($shopId == $myPermission['shop_id']) {
                        $pass = true;
                    } else {
                        $pass = false;
                    }
                }

                if ($pass) {
                    return;
                }
            }
        }

        throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
    }

    protected function getPlatformSessions()
    {
        $topLevelDomain = $this->container->getParameter('top_level_domain');
        ini_set('session.cookie_domain', $topLevelDomain);
        if (!isset($_SESSION)) {
            session_start();
        }

        $adminPlatformCookieName = AdminPlatformController::COOKIE_NAME_PLATFORM;
        $salesCompanyCookieName = AdminPlatformController::COOKIE_NAME_SALES_COMPANY;

        $platform = isset($_SESSION[$adminPlatformCookieName]) ? $_SESSION[$adminPlatformCookieName] : null;
        $salesCompanyId = isset($_SESSION[$salesCompanyCookieName]) ? $_SESSION[$salesCompanyCookieName] : null;

        if (is_null($platform)) {
            throw new PreconditionFailedHttpException(self::PRECONDITION_NOT_SET);
        }

        return array(
            'platform' => $platform,
            'sales_company_id' => $salesCompanyId,
        );
    }

    /**
     * @param $adminId
     * @param $platform
     * @param $salesCompanyId
     *
     * @return bool
     */
    protected function hasSuperAdminPosition(
        $adminId,
        $platform,
        $salesCompanyId
    ) {
        $superAdminPositionBindings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getPositionBindingsByIsSuperAdmin(
                $adminId,
                true,
                $platform,
                $salesCompanyId
            );

        if (count($superAdminPositionBindings) >= 1) {
            return true;
        }

        return false;
    }

    /**
     * @param $adminId
     * @param $platform
     * @param $salesCompanyId
     *
     * @return array
     */
    protected function getMyAdminPermissions(
        $adminId,
        $platform,
        $salesCompanyId
    ) {
        $commonAdminPositionBindings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getPositionBindingsByIsSuperAdmin(
                $adminId,
                false,
                $platform,
                $salesCompanyId
            );

        $myPermissions = array();
        foreach ($commonAdminPositionBindings as $binding) {
            $position = $binding->getPosition();

            $positionPermissionMaps = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionPermissionMap')
                ->findBy(array(
                    'position' => $position,
                ));

            foreach ($positionPermissionMaps as $map) {
                $permission = $map->getPermission();
                $permissionArray = array(
                    'key' => $permission->getKey(),
                    'op_level' => $map->getOpLevel(),
                    'building_id' => $binding->getBuildingId(),
                    'shop_id' => $binding->getShopId(),
                );

                array_push($myPermissions, $permissionArray);
            }
        }

        return $myPermissions;
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
            $headers = array_change_key_case($_SERVER, CASE_LOWER);
            $auth = $headers['http_authorization'];
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
            $headers = array_change_key_case($_SERVER, CASE_LOWER);
            $auth = $headers['http_authorization'];
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
            $headers = array_change_key_case($_SERVER, CASE_LOWER);
            $auth = $headers['http_authorization'];
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
            $headers = array_change_key_case($_SERVER, CASE_LOWER);
            $auth = $headers['http_authorization'];
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
            $headers = array_change_key_case($_SERVER, CASE_LOWER);
            $auth = $headers['http_authorization'];
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
     * @param string $prefix
     *
     * @return string
     */
    protected function generateRandomToken(
        $prefix = null
    ) {
        return md5(uniqid($prefix.rand(), true));
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
     * @param $headerKey
     *
     * @return Auth
     */
    protected function getSandboxAuthorization(
        $headerKey
    ) {
        $headerKey = 'http_'.$headerKey;

        // get auth
        $headers = array_change_key_case($_SERVER, CASE_LOWER);
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

    /**
     * @param array $logParams
     *
     * Example:
     * $logParams = array(
     *      'logModule' => $module,
     *      'logAction' => $action,
     *      'logObjectKey' => $objectKey,
     *      'logObjectId' => $objectId,
     * )
     *
     * @return bool
     */
    protected function generateAdminLogs(
        $logParams
    ) {
        try {
            $sessions = $this->getPlatformSessions();

            $em = $this->getDoctrine()->getManager();

            // clear doctrine cache, then get object
            $em->clear();

            // create log object
            $log = new Log();

            $form = $this->createForm(new LogType(), $log);
            $form->submit($logParams);

            if (!$form->isValid()) {
                return false;
            }

            $log->setAdminUsername($this->getAdminId());
            $log->setPlatform($sessions['platform']);
            $log->setSalesCompanyId($sessions['sales_company_id']);

            if ($this->handleLog($log)) {
                $em->persist($log);
                $em->flush();

                return true;
            }

            return false;
        } catch (\Exception $e) {
            error_log('generate log went wrong!');
        }
    }
}
