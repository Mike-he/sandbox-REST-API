<?php

namespace Sandbox\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sandbox\ApiBundle\Entity\Buddy\Buddy;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Company\Company;
use Sandbox\ApiBundle\Entity\Announcement\Announcement;
use Sandbox\ApiBundle\Entity\Feed\Feed;
use Sandbox\ApiBundle\Entity\Feed\FeedComment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;

//TODO there's certainly a way to get the
// current bundle name with a magic function
const BUNDLE = 'SandboxApiBundle';

class SandboxRestController extends FOSRestController
{
    // TODO move constants to constant folder

    const NOT_ALLOWED_MESSAGE = 'You are not allowed to perform this action';

    const NOT_FOUND_MESSAGE = 'This resource does not exist';

    const BAD_PARAM_MESSAGE = 'Bad parameters';

    const CONFLICT_MESSAGE = 'This resource already exists';

    const SERVICE_ACCOUNT_NOT_FOUND = 'Sandbox Service Account Not Found';

    const HTTP_STATUS_OK = 200;
    const HTTP_STATUS_OK_NO_CONTENT = 204;

    const VERIFICATION_CODE_LENGTH = 6;

    const HTTP_HEADER_AUTH = 'authorization';

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
            BUNDLE.':'.$repo
        );
    }

    /**
     * @param $userArray
     * @param $orderId
     */
    protected function updateDoorAccess(
        $userArray,
        $orderId
    ) {
        foreach ($userArray as $user) {
            $userId = (int) $user['empid'];
            $doors = $this->getRepo('Door\DoorAccess')->findBy(
                array(
                    'userId' => $userId,
                    'orderId' => $orderId,
                    'access' => false,
                )
            );

            if (!empty($doors)) {
                foreach ($doors as $door) {
                    $door->setAccess(true);
                }
                $em = $this->getDoctrine()->getManager();
                $em->flush();
            }
        }
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

            return;
        }

        // if permission key is array
        if (is_array($permissionKeys)) {
            $flag = false;
            foreach ($permissionKeys as $permissionKey) {
                $permission = $this->getRepo('Admin\AdminPermission')->findOneByKey($permissionKey);

                // check user's permission
                $myPermission = $this->getRepo('Admin\AdminPermissionMap')
                    ->findOneBy(array(
                        'adminId' => $adminId,
                        'permissionId' => $permission->getId(),
                    ));
                if (!is_null($myPermission)) {
                    $flag = true;
                    break;
                }
            }

            if (!$flag) {
                throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
            }
        }

        // check user's operation level
        if ($myPermission->getOpLevel() < $opLevel) {
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

        $response = $this->get('curl_util')->callAPI(
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

        $response = $this->get('curl_util')->callAPI(
            $ch,
            'GET',
            array('Sandbox-Auth: '.$contentMd5)
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
        $channel
    ) {
        $json = $this->createJsonForCharge(
            $tradeId,
            $amount,
            $channel
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

        $response = $this->get('curl_util')->callAPI(
            $ch,
            'POST',
            array('Sandbox-Auth: '.$auth),
            $json);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result['balance'];
    }

    /**
     * @param $userId
     * @param $data
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

        $response = $this->get('curl_util')->callAPI(
            $ch,
            'POST',
            array('Sandbox-Auth: '.$auth),
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

        $response = $this->get('curl_util')->callAPI(
            $ch,
            'POST',
            array('Sandbox-Auth: '.$auth),
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
        $payType
    ) {
        $content = [
            'amount' => $amount,
            'pay_type' => $payType,
            'trade_id' => $orderNumber,
        ];

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

        $response = $this->get('curl_util')->callAPI(
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

        $response = $this->get('curl_util')->callAPI(
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

        $response = $this->get('curl_util')->callAPI(
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
     * Send sms.
     *
     * Reference: http://sms.webchinese.cn/api.shtml#top4
     * Example: $url='http://sms.webchinese.cn/web_api/?Uid=账号&Key=接口密钥&smsMob=手机号码&smsText=短信
     *
     * @param $smsMob
     * @param $smsText
     *
     * @return mixed|string
     */
    protected function sendSms(
        $smsMob,
        $smsText
    ) {
        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // set url
        $url = $globals['sms_api_base_url'];
        $url = $url.'Uid='.$globals['sms_api_uid'];
        $url = $url.'&Key='.$globals['sms_api_key'];
        $url = $url.'&smsMob='.$smsMob;
        $url = $url.'&smsText='.$smsText;

        // call api
        if (function_exists('file_get_contents')) {
            $file_contents = file_get_contents($url);
        } else {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            curl_close($ch);
        }

        return $file_contents;
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

    /**
     * @param $phone
     *
     * @return bool
     */
    protected function isPhoneNumberValid(
        $phone
    ) {
        if (is_null($phone) || !ctype_digit($phone)) {
            return false;
        }

        $phoneNumLength = strlen($phone);
        if ($phoneNumLength != 11) {
            return false;
        }

        return true;
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

    /**
     * @param $userId
     * @param null $currentToken
     * @param null $em
     */
    protected function removeUserOtherTokens(
        $userId,
        $currentToken = null,
        $em = null
    ) {
        $isEmNull = false;
        if (is_null($em)) {
            $isEmNull = true;
            $em = $this->getDoctrine()->getManager();
        }

        $tokens = $this->getRepo('User\UserToken')->findByUserId($userId);
        foreach ($tokens as $token) {
            if (!is_null($currentToken)
                && $token->getToken() === $currentToken) {
                continue;
            }

            $em->remove($token);
        }

        if ($isEmNull) {
            $em->flush();
        }
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

        $response = $this->get('curl_util')->callAPI(
            $ch,
            'POST',
            array('Sandbox-Auth: '.$contentMd5)
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

        $response = $this->get('curl_util')->callAPI(
            $ch,
            'GET',
            array('Sandbox-Auth: '.$contentMd5)
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

    //---------------------------------------- XMPP Notification ----------------------------------------//

    /**
     * @param Announcement $announcement
     * @param string       $action
     */
    protected function sendXmppAnnouncementNotification(
        $announcement,
        $action
    ) {
        try {
            // get event message data
            $jsonData = $this->getAnnouncementNotificationJsonData($announcement, $action);

            // send xmpp notification
            $this->sendXmppNotification($jsonData, true);
        } catch (Exception $e) {
            error_log('Send announcement notification went wrong!');
        }
    }

    /**
     * @param User   $fromUser
     * @param User   $recvUser
     * @param string $action
     */
    protected function sendXmppBuddyNotification(
        $fromUser,
        $recvUser,
        $action
    ) {
        try {
            // get event message data
            $jsonData = $this->getBuddyNotificationJsonData($action, $fromUser, $recvUser);

            // send xmpp notification
            $this->sendXmppNotification($jsonData, false);
        } catch (Exception $e) {
            error_log('Send buddy notification went wrong!');
        }
    }

    /**
     * @param Company $company
     * @param User    $fromUser
     * @param User    $recvUser
     * @param string  $action
     * @param bool    $memberSync
     */
    protected function sendXmppCompanyNotification(
        $company,
        $fromUser,
        $recvUser,
        $action,
        $memberSync
    ) {
        try {
            // get event message data
            $jsonData = $this->getCompanyNotificationJsonData(
                $company, $action, $fromUser, $recvUser, $memberSync
            );

            // send xmpp notification
            $this->sendXmppNotification($jsonData, false);
        } catch (Exception $e) {
            error_log('Send company notification went wrong!');
        }
    }

    /**
     * @param Feed        $feed
     * @param User        $fromUser
     * @param array       $recvUsers
     * @param string      $action
     * @param FeedComment $comment
     */
    protected function sendXmppFeedNotification(
        $feed,
        $fromUser,
        $recvUsers,
        $action,
        $comment = null
    ) {
        try {
            // get event message data
            $jsonData = $this->getFeedNotificationJsonData(
                $feed, $action, $fromUser, $recvUsers, $comment
            );

            // send xmpp notification
            $this->sendXmppNotification($jsonData, false);
        } catch (Exception $e) {
            error_log('Send feed notification went wrong!');
        }
    }

    /**
     * @param string $body
     */
    protected function sendXmppMessageNotification(
        $body
    ) {
        try {
            $globals = $this->getGlobals();
            $domainURL = $globals['xmpp_domain'];
            $jid = User::XMPP_SERVICE.'@'.$domainURL;

            $messageArray = [
                'type' => 'chat',
                'from' => $jid,
                'body' => $body,
            ];

            // get message data
            $jsonData = $this->getNotificationBroadcastJsonData(
                array(),
                null,
                $messageArray
            );

            // send xmpp notification
            $this->sendXmppNotification($jsonData, true);
        } catch (Exception $e) {
            error_log('Send message notification went wrong!');
        }
    }

    /**
     * @param object $jsonData
     * @param bool   $broadcast
     */
    protected function sendXmppNotification(
        $jsonData,
        $broadcast
    ) {
        try {
            // get globals
            $twig = $this->container->get('twig');
            $globals = $twig->getGlobals();

            // openfire API URL
            $apiURL = $globals['openfire_innet_url'].
                $globals['openfire_plugin_sandbox'].
                $globals['openfire_plugin_sandbox_notification'];

            if ($broadcast) {
                $apiURL = $apiURL.$globals['openfire_plugin_sandbox_notification_broadcast'];
            }

            // call OpenFire API
            $ch = curl_init($apiURL);
            $this->get('curl_util')->callAPI($ch, 'POST', null, $jsonData);
        } catch (Exception $e) {
            error_log('Send XMPP notification went wrong.');
        }
    }

    /**
     * @param Announcement $announcement
     * @param string       $action
     *
     * @return string | object
     */
    private function getAnnouncementNotificationJsonData(
        $announcement,
        $action
    ) {
        // get content array
        $contentArray = $this->getDefaultContentArray(
            'announcement', $action
        );

        $contentArray['announcement'] = array(
            'id' => $announcement->getId(),
            'title' => $announcement->getTitle(),
        );

        return $this->getNotificationBroadcastJsonData(array(), $contentArray);
    }

    /**
     * @param string $action
     * @param User   $fromUser
     * @param User   $recvUser
     *
     * @return string | object
     */
    private function getBuddyNotificationJsonData(
        $action,
        $fromUser,
        $recvUser
    ) {
        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $domainURL = $globals['xmpp_domain'];

        // get receivers
        $receivers = array(
            array('jid' => $recvUser->getXmppUsername().'@'.$domainURL),
        );

        // get content array
        $contentArray = $this->getDefaultContentArray(
            'buddy', $action, $fromUser
        );

        return $this->getNotificationJsonData($receivers, $contentArray);
    }

    /**
     * @param Company $company
     * @param string  $action
     * @param User    $fromUser
     * @param User    $recvUser
     * @param bool    $memberSync
     *
     * @return string | object
     */
    private function getCompanyNotificationJsonData(
        $company,
        $action,
        $fromUser,
        $recvUser,
        $memberSync
    ) {
        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $domainURL = $globals['xmpp_domain'];

        // get receivers
        $receivers = array();

        if ($memberSync) {
            $members = $this->getRepo('Company\CompanyMember')->findBy(array(
                'company' => $company,
            ));

            foreach ($members as $member) {
                $user = $this->getRepo('User\User')->find($member->getUserId());
                if (is_null($user)) {
                    continue;
                }
                $jid = $user->getXmppUsername().'@'.$domainURL;
                $receivers[] = array('jid' => $jid);
            }
        } else {
            $jid = $recvUser->getXmppUsername().'@'.$domainURL;
            $receivers[] = array('jid' => $jid);
        }

        // get content array
        $contentArray = $this->getDefaultContentArray(
            'company', $action, $fromUser
        );

        $contentArray['company'] = array(
            'id' => $company->getId(),
            'name' => $company->getName(),
        );

        return $this->getNotificationJsonData($receivers, $contentArray);
    }

    /**
     * @param Feed        $feed
     * @param string      $action
     * @param User        $fromUser
     * @param array       $recvUsers
     * @param FeedComment $comment
     *
     * @return string | object
     */
    private function getFeedNotificationJsonData(
        $feed,
        $action,
        $fromUser,
        $recvUsers,
        $comment = null
    ) {
        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $domainURL = $globals['xmpp_domain'];

        // get receivers
        $receivers = array();

        foreach ($recvUsers as $recvUser) {
            $jid = $recvUser->getXmppUsername().'@'.$domainURL;
            $receivers[] = array('jid' => $jid);
        }

        // get content array
        $contentArray = $this->getDefaultContentArray(
            'feed', $action, $fromUser
        );

        $contentArray['feed'] = array(
            'id' => $feed->getId(),
            'content' => $feed->getContent(),
        );

        if (!is_null($comment)) {
            $contentArray['comment'] = array(
                'id' => $comment->getId(),
                'payload' => $comment->getPayload(),
            );
        }

        return $this->getNotificationJsonData($receivers, $contentArray);
    }

    /**
     * @param string $type
     * @param string $action
     * @param User   $fromUser
     *
     * @return array
     */
    private function getDefaultContentArray(
        $type,
        $action,
        $fromUser = null
    ) {
        $timestamp = round(microtime(true) * 1000);

        $contentArray = array(
            'type' => $type,
            'action' => $action,
            'timestamp' => "$timestamp",
        );

        // get fromUserArray
        if (!is_null($fromUser)) {
            $contentArray['from'] = $this->getFromUserArray($fromUser);
        }

        return $contentArray;
    }

    /**
     * @param User $fromUser
     *
     * @return array
     */
    private function getFromUserArray(
        $fromUser
    ) {
        $name = '';

        $profile = $this->getRepo('User\UserProfile')->findOneByUser($fromUser);
        if (!is_null($profile)) {
            $name = $profile->getName();
        }

        return array(
            'id' => $fromUser->getId(),
            'xmpp_username' => $fromUser->getXmppUsername(),
            'name' => $name,
        );
    }

    /**
     * @param array $receivers
     * @param array $contentArray
     *
     * @return string | object
     */
    private function getNotificationJsonData(
        $receivers,
        $contentArray
    ) {
        $jsonDataArray = array(
            'receivers' => $receivers,
            'content' => $contentArray,
        );

        return json_encode($jsonDataArray);
    }

    /**
     * @param array $outcasts
     * @param array $contentArray
     *
     * @return string | object
     */
    private function getNotificationBroadcastJsonData(
        $outcasts,
        $contentArray = null,
        $messageArray = null
    ) {
        $jsonDataArray = array('outcasts' => $outcasts);

        // check content array
        if (!is_null($contentArray)) {
            $jsonDataArray['content'] = $contentArray;
        }

        // check message array
        if (!is_null($messageArray)) {
            $jsonDataArray['message'] = $messageArray;
        }

        return json_encode($jsonDataArray);
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
        $response = $this->get('curl_util')->callAPI(
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
        $dataArray = array();
        $dataArray['username'] = $username;

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
        $this->throwNotFoundIfNull($serviceUser, self::SERVICE_ACCOUNT_NOT_FOUND);

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
}
