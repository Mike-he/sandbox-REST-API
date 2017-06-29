<?php

namespace Sandbox\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Auth\Auth;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\User\UserGroupHasUser;
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

    const PRECONDITION_NOT_SET = 'The precondition not set';

    const HTTP_STATUS_OK = 200;
    const HTTP_STATUS_OK_NO_CONTENT = 204;
    const HTTP_STATUS_CREATE_SUCCESS = 201;

    const VERIFICATION_CODE_LENGTH = 6;

    const HTTP_HEADER_AUTH = 'authorization';

    const SANDBOX_CUSTOM_HEADER = 'Sandbox-Auth: ';

    const UNAUTHED_API_CALL = 'Unauthorized Request';

    const ENCODE_METHOD_MD5 = 'md5';

    const ENCODE_METHOD_SHA1 = 'sha1';

    const SANDBOX_ADMIN_LOGIN_HEADER = 'http_sandboxadminauthorization';

    const SANDBOX_CLIENT_LOGIN_HEADER = 'http_sandboxclientauthorization';

    const ADMIN_COOKIE_NAME = 'sandbox_admin_token';

    const HASH_ALGO_SHA256 = 'sha256';

    const PAYMENT_NOTIFICATION_TYPE = 'other_user_payment';
    const PAYMENT_NOTIFICATION_ACTION = 'need_to_pay';

    const ZH_SMS_BEFORE = '【创合秒租】您的验证码为：';
    const ZH_SMS_AFTER = '。验证码在10分钟内有效，请勿向任何人泄露您的验证码。';

    const EN_SMS_BEFORE = '【Sandbox3】Your verification code is';
    const EN_SMS_AFTER = '. It will be expired after 10 minutes. Please do not reveal it to others.';

    const ZH_SMS_APPOINTMENT_BEFORE = '【创合秒租】您的“';
    const ZH_SMS_APPOINTMENT_AFTER = '”收到一位用户的办公室申请，请登录创合秒租管理平台查看。';

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

    /**
     * authenticate with web browser cookie.
     *
     * @return mixed
     */
    protected function authenticateAdminCookie()
    {
        $cookie_name = self::ADMIN_COOKIE_NAME;
        if (!isset($_COOKIE[$cookie_name])) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $token = $_COOKIE[$cookie_name];
        $adminToken = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserToken')
            ->findOneBy(array(
                'token' => $token,
            ));
        if (is_null($adminToken)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        return $adminToken->getUser();
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
            $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
            $platform = $adminPlatform['platform'];
            $salesCompanyId = $adminPlatform['sales_company_id'];
        }

        // super admin
        $isSuperAdmin = $this->hasSuperAdminPosition(
            $adminId,
            $platform,
            $salesCompanyId
        );

        if ($isSuperAdmin) {
            $myPermissions = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPermission')
                ->findSuperAdminPermissionsByPlatform(
                    $platform,
                    $salesCompanyId
                );

            // check permissions
            foreach ($permissionKeys as $permissionKey) {
                // check specify resource permission
                $this->checkSpecifyResourcePermissionIfSuperAdmin(
                    $permissionKey,
                    $salesCompanyId
                );

                $pass = false;
                foreach ($myPermissions as $myPermission) {
                    if ($permissionKey['key'] == $myPermission['key']
                        && $opLevel <= $myPermission['op_level']
                    ) {
                        $pass = true;
                    }

                    if ($pass) {
                        return;
                    }
                }
            }
        } else {
            // check permission by sales monitoring permission
            $hasSalesMonitoringPermission = $this->checkSalesMonitoringPermission(
                $platform,
                $adminId
            );

            if ($opLevel == AdminPermission::OP_LEVEL_VIEW && $hasSalesMonitoringPermission) {
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

                foreach ($myPermissions as $myPermission) {
                    if ($permissionKey['key'] == $myPermission['key']
                        && $opLevel <= $myPermission['op_level']
                    ) {
                        if (!is_null($buildingId)) {
                            if ($buildingId == $myPermission['building_id']) {
                                return;
                            } else {
                                continue;
                            }
                        }

                        if (!is_null($shopId)) {
                            if ($shopId == $myPermission['shop_id']) {
                                return;
                            } else {
                                continue;
                            }
                        }

                        return;
                    }
                }
            }
        }

        throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
    }

    /**
     * @param $permissionKey
     * @param $salesCompanyId
     */
    protected function checkSpecifyResourcePermissionIfSuperAdmin(
        $permissionKey,
        $salesCompanyId
    ) {
        if (isset($permissionKey['building_id'])) {
            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($permissionKey['building_id']);

            if (is_null($building)) {
                throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
            }

            if ($building->getCompanyId() != $salesCompanyId) {
                throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
            }
        }

        if (isset($permissionKey['shop_id'])) {
            $shop = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Shop\Shop')
                ->find($permissionKey['shop_id']);

            if (is_null($shop)) {
                throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
            }

            if ($shop->getBuilding()->getCompanyId() != $salesCompanyId) {
                throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
            }
        }
    }

    /**
     * @return array
     */
    protected function getAdminPlatform()
    {
        $userId = $this->getUser()->getUserId();
        $clientId = $this->getUser()->getClientId();

        $adminPlatform = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPlatform')
            ->findOneBy(array(
                'userId' => $userId,
                'clientId' => $clientId,
            ));

        if (is_null($adminPlatform)) {
            throw new PreconditionFailedHttpException(self::PRECONDITION_NOT_SET);
        }

        return array(
            'platform' => $adminPlatform->getPlatform(),
            'sales_company_id' => $adminPlatform->getSalesCompanyId(),
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
        $salesCompanyId = null
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
        $salesCompanyId = null
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
                    'name' => $permission->getName(),
                    'id' => $permission->getId(),
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

    /**
     * @param $ruleId
     * @param null $auth
     *
     * @return mixed|void
     */
    protected function getSalesAdminInvoices(
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
            $globals['crm_api_sales_admin_invoices'].
            '?status[]=pending&status[]=cancelled_wait';

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
    protected function throwNotFoundIfNull($resource, $message = self::NOT_FOUND_MESSAGE)
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
     * @param User $user
     * @param int  $registrationId
     *
     * @return mixed
     */
    protected function createXmppUser(
        $user,
        $registrationId
    ) {
        // generate username
        $username = strval(2000000 + $registrationId);
        $password = $user->getPassword();

        $service = $this->get('openfire.service');
        $service->createUser($username, $password);

        return $username;
    }

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
        $service = $this->get('openfire.service');
        $service->editUser($username, $password, $name);

        return true;
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
     * @param $accessNo
     */
    protected function callRepealRoomOrderCommand(
        $base,
        $accessNo
    ) {
        try {
            $kernel = $this->get('kernel');
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput(array(
                'command' => 'RoomOrder:Repeal',
                'base' => $base,
                'accessNo' => $accessNo,
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
        $accessNo,
        $startDate,
        $endDate
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
                'accessNo' => $accessNo,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ));

            $output = new NullOutput();
            $application->run($input, $output);
        } catch (\Exception $e) {
            error_log('Door Access Set Room Order Command Error');
        }
    }

    /**
     * @param $base
     * @param $accessNo
     * @param $userArray
     */
    protected function callRemoveFromOrderCommand(
        $base,
        $accessNo,
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
                'accessNo' => $accessNo,
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
        $orderControl
    ) {
        $roomId = $orderControl->getRoomId();
        $roomDoors = $this->getRepo('Room\RoomDoors')->findByRoomId($roomId);
        if (empty($roomDoors)) {
            return;
        }

        $accessNo = $orderControl->getAccessNo();
        $startDate = $orderControl->getStartDate();
        $endDate = $orderControl->getEndDate();

        // get cancelled action controls
        $cancelledControls = $this->getRepo('Door\DoorAccess')->getAllWithoutAccess(
            DoorAccessConstants::METHOD_CANCELLED,
            $accessNo
        );

        if (!empty($cancelledControls)) {
            foreach ($cancelledControls as $cancelledControl) {
                // cancel order
                $this->callRepealRoomOrderCommand(
                    $base,
                    $cancelledControl->getAccessNo()
                );
            }
        } else {
            // get add action controls
            $addControls = $this->getRepo('Door\DoorAccess')->getAllWithoutAccess(
                DoorAccessConstants::METHOD_ADD,
                $accessNo
            );

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
                    $accessNo,
                    $startDate,
                    $endDate
                );
            }

            // get delete action controls
            $deleteControls = $this->getRepo('Door\DoorAccess')->getAllWithoutAccess(
                DoorAccessConstants::METHOD_DELETE,
                $accessNo
            );

            $removeUserArray = [];
            foreach ($deleteControls as $deleteControl) {
                $userId = $deleteControl->getUserId();
                $result = $this->getCardNoByUser($userId);
                if (!is_null($result['card_no']) && !empty($result['card_no'])) {
                    $empUser = ['empid' => $userId];
                    array_push($removeUserArray, $empUser);
                } else {
                    $deleteControl->setAccess(true);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // remove room access
            if (!empty($removeUserArray)) {
                $this->callRemoveFromOrderCommand(
                    $base,
                    $accessNo,
                    $removeUserArray
                );
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
            $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();

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
            $log->setPlatform($adminPlatform['platform']);

            // set sales company
            $salesCompanyId = isset($adminPlatform['sales_company_id']) ? $adminPlatform['sales_company_id'] : null;

            if (!is_null($salesCompanyId)) {
                $salesCompany = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
                    ->find($salesCompanyId);

                if (!is_null($salesCompany)) {
                    $log->setSalesCompany($salesCompany);
                }
            }

            // save log
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

    /**
     * @param $str
     *
     * @return mixed
     */
    protected function filterEmoji(
        $str
    ) {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return $str;
    }

    /**
     * @param $platform
     * @param $salesCompanyId
     *
     * @return array
     */
    protected function getSalesPlatformMonitoringPermissions(
        $platform,
        $salesCompanyId
    ) {
        // check has sales monitoring permission
        $hasSalesMonitoringPermission = $this->checkSalesMonitoringPermission(
            $platform
        );

        if (!$hasSalesMonitoringPermission) {
            return null;
        }

        // get my sales permissions
        $salesPlatformMonitoringPermissions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findSuperAdminPermissionsByPlatform(
                $platform,
                $salesCompanyId
            );

        $salesPlatformMonitoringPermissionsArray = array();
        foreach ($salesPlatformMonitoringPermissions as $permission) {
            $permission['op_level'] = 1;

            array_push($salesPlatformMonitoringPermissionsArray, $permission);
        }

        return $salesPlatformMonitoringPermissionsArray;
    }

    /**
     * @param $platform
     * @param $adminId
     *
     * @return bool
     */
    protected function checkSalesMonitoringPermission(
        $platform,
        $adminId = null
    ) {
        if ($platform == AdminPermission::PERMISSION_PLATFORM_OFFICIAL) {
            return false;
        }

        if (is_null($adminId)) {
            $adminId = $this->getAdminId();
        }

        $isOfficialSuperAdmin = $this->hasSuperAdminPosition(
            $adminId,
            AdminPermission::PERMISSION_PLATFORM_OFFICIAL
        );

        if ($isOfficialSuperAdmin) {
            return true;
        }

        $myOfficialPermissions = $this->getMyAdminPermissions(
            $adminId,
            AdminPermission::PERMISSION_PLATFORM_OFFICIAL
        );

        $salesMonitoringPermission = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SALES_MONITORING,
            ));

        if (is_null($salesMonitoringPermission)) {
            return false;
        }

        $salesMonitoringPermissionArray = array(
            'key' => $salesMonitoringPermission->getKey(),
            'op_level' => $salesMonitoringPermission->getMaxOpLevel(),
            'name' => $salesMonitoringPermission->getName(),
            'id' => $salesMonitoringPermission->getId(),
            'building_id' => null,
            'shop_id' => null,
        );

        if (in_array($salesMonitoringPermissionArray, $myOfficialPermissions)) {
            return true;
        }

        return false;
    }

    /**
     * @param $buildingId
     * @param $userIds
     * @param $startDate
     * @param $endDate
     * @param $orderNumber
     * @param $type
     */
    protected function setDoorAccessForMembershipCard(
        $buildingId,
        $userIds,
        $startDate,
        $endDate,
        $orderNumber,
        $type = UserGroupHasUser::TYPE_ORDER
    ) {
        $door = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupDoors')
            ->getGroupsByBuilding(
                $buildingId,
                true
            );

        if (is_null($door)) {
            return;
        }

        $now = new \DateTime('now');
        $card = $door->getCard();
        $accessNo = $card->getAccessNo();

        // add user to user_group
        $em = $this->getDoctrine()->getManager();
        $this->addUserToUserGroup(
            $em,
            $userIds,
            $card,
            $startDate,
            $endDate,
            $orderNumber,
            $type
        );

        // add user to door access
        if ($now >= $startDate) {
            $this->addUserDoorAccess(
                $accessNo,
                $userIds,
                $startDate,
                $endDate,
                array($buildingId)
            );
        }
    }

    /**
     * @param $em
     * @param $userIds
     * @param $card
     * @param $startDate
     * @param $endDate
     * @param $orderNumber
     * @param $type
     */
    protected function addUserToUserGroup(
        $em,
        $userIds,
        $card,
        $startDate,
        $endDate,
        $orderNumber,
        $type
    ) {
        $userGroup = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroup')
            ->findOneBy(array(
                'card' => $card->getId(),
            ));

        if (is_null($userGroup)) {
            return;
        }

        foreach ($userIds as $userId) {
            $this->get('sandbox_api.group_user')->storeGroupUser(
                $em,
                $userGroup->getId(),
                $userId,
                $type,
                $startDate,
                $endDate,
                $orderNumber
            );
        }

        $em->flush();
    }

    /**
     * @param $buildingId
     * @param $userIds
     * @param $startDate
     * @param $orderNumber
     * @param $type
     */
    protected function removeUserFromUserGroup(
        $buildingId,
        $userIds,
        $startDate,
        $orderNumber,
        $type
    ) {
        $door = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupDoors')
            ->getGroupsByBuilding(
                $buildingId,
                true
            );

        if (is_null($door)) {
            return;
        }

        $now = new \DateTime('now');
        $card = $door->getCard();

        // add user to user_group
        $em = $this->getDoctrine()->getManager();
        $this->addUserToUserGroup(
            $em,
            $userIds,
            $card,
            $startDate,
            $now,
            $orderNumber,
            $type
        );
    }
}
