<?php

namespace Sandbox\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Company\Company;
use Symfony\Component\HttpFoundation\Request;

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

    const HTTP_STATUS_OK = 200;

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
     * @param int    $adminId
     * @param string $typeKey
     * @param string $permissionKey
     * @param int    $opLevel
     *
     * @throws AccessDeniedHttpException
     */
    protected function throwAccessDeniedIfAdminNotAllowed(
        $adminId,
        $typeKey,
        $permissionKey = null,
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

        if (is_null($permissionKey)) {
            return;
        }

        $permission = $this->getRepo('Admin\AdminPermission')->findOneByKey($permissionKey);

        // check user's permission
        $myPermission = $this->getRepo('Admin\AdminPermissionMap')
            ->findOneBy(array(
                'adminId' => $adminId,
                'permissionId' => $permission->getId(),
            ));
        if (is_null($myPermission)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
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

        $response = $this->get('curl_util')->callAPI($ch, 'GET', $auth);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);
        if ($result['status'] === 'unauthed') {
            return;
        }

        return $result['card_no'];
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

        $response = $this->get('curl_util')->callInternalAPI($ch, 'GET', $contentMd5);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);
        if ($result['status'] === 'unauthed') {
            return;
        }

        return $result['card_no'];
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

        $response = $this->get('curl_util')->callInternalAPI($ch, 'POST', $auth, $json);

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

        $response = $this->get('curl_util')->callInternalAPI($ch, 'POST', $auth, $json);

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

        $response = $this->get('curl_util')->callInternalAPI($ch, 'POST', $auth, json_encode($content));

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

        $response = $this->get('curl_util')->callAPI($ch, 'GET', $auth);

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

        $response = $this->get('curl_util')->callAPI($ch, 'GET', $auth);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result['balance'];
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
}
