<?php

namespace Sandbox\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Sandbox\ApiBundle\Entity\User\User;

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
     * @return string
     */
    protected function getUserJid()
    {
        return $this->getUser()->getJid();
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

    /**
     * Get user's vCard name.
     *
     * @param $userId
     * @param $companyId
     *
     * @return string
     */
    protected function getUserVCardName(
        $userId,
        $companyId = null
    ) {
        $name = '';

        $vCard = $this->getRepo('JtVCard')->findOneBy(array(
            'userid' => $userId,
            'companyid' => $companyId,
        ));

        if (!is_null($vCard)) {
            $name = $vCard->getName();
        }

        return $name;
    }

    //--------------------call remote api--------------------//
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

    /**
     * @param $ch     curl
     * @param $data   json data
     * @param $auth   authorization
     * @param $method http method
     *
     * @return mixed
     */
    protected function callAPI(
        $ch,
        $data,
        $auth,
        $method
    ) {
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        } elseif ($method === 'PUT' || $method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:'.$auth));

        return curl_exec($ch);
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

    /**
     * @param $countryCode
     * @param $phone
     *
     * @return string
     */
    protected function constructVCardPhone(
        $countryCode,
        $phone
    ) {
        if (!is_null($countryCode)) {
            return '(+'.$countryCode.')'.$phone;
        }

        return $phone;
    }

    /**
     * @param $type
     * @param $id
     * @param $userID
     */
    protected function checkIsOwner(
        $type,
        $id,
        $userID
    ) {
        $repo = $this->getRepo($type);

        $item = $repo->findOneById($id);
        $this->throwNotFoundIfNull($item, self::NOT_FOUND_MESSAGE);

        $ownerID = $item->getOwnerid();
        if ($userID != $ownerID) {
            $this->throwAccessDeniedIfNull($item);
        }

        return $item;
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

    //--------------------generate default company member or vcard--------------------//
    protected function generateCompanyMember(
        $userId,
        $companyId
    ) {
        $em = $this->getDoctrine()->getManager();

        //get user's real name from jtVcard
        $vcard = $this->getRepo('JtVCard')->findOneBy(array(
            'userid' => $userId,
            'companyid' => null,
        ));
        $this->throwNotFoundIfNull($vcard, 'vcard '.self::NOT_FOUND_MESSAGE);

        $fullName = $vcard->getName();
        $gender = $vcard->getGender();

        // get company member
        $companyMember = $this->getRepo('Companymember')->findOneBy(array(
            'userid' => $userId,
            'companyid' => $companyId,
        ));

        if (is_null($companyMember)) {
            // save to company member
            $companyMember = $this->setCompanyMember(
                $userId,
                $companyId
            );
            $em->persist($companyMember);
        } else {
            $companyMember->setIsdelete(false);
        }

        // get vcard
        $vcard = $this->getRepo('JtVCard')->findOneBy(array(
            'userid' => $userId,
            'companyid' => $companyId,
        ));

        if (is_null($vcard)) {
            $jtVCard = $this->setDefaultVCard(
                $userId,
                $companyId,
                $fullName,
                $gender
            );
            $em->persist($jtVCard);
        }
        $em->flush();
    }

    /**
     * @param $userId
     * @param $companyId
     * @param $fullName
     * @param $gender
     *
     * @return JtVCard
     */
    protected function setDefaultVCard(
        $userId,
        $companyId,
        $fullName,
        $gender
    ) {
        $jtVCard = new JtVCard();
        $jtVCard->setUserid($userId);
        $jtVCard->setCompanyid($companyId);
        $jtVCard->setName($fullName);
        $jtVCard->setGender($gender);

        return $jtVCard;
    }

    /**
     * @param $userId
     * @param $companyId
     *
     * @return Companymember
     */
    protected function setCompanyMember(
        $userId,
        $companyId
    ) {
        $companyMember = new Companymember();
        $companyMember->setUserid($userId);
        $companyMember->setCompanyid($companyId);
        $companyMember->setIsdelete(false);

        return $companyMember;
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
        $view = new View();
        $view->setStatusCode($statusCode);
        $view->setData(array(
            'code' => $errorCode,
            'message' => $errorMessage,
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

    /**
     * @param $companyID
     * @param $userID
     */
    protected function throwAccessDeniedIfNotCompanyMember(
        $companyID,
        $userID
    ) {
        $member = $this->getRepo('CompanymemberView')->findOneBy(array(
            'userid' => $userID,
            'companyid' => $companyID,
        ));

        if (is_null($member)) {
            $this->throwAccessDeniedIfNull($member);
        }
    }

    /**
     * @param $companyID
     * @param $userID
     */
    protected function throwAccessDeniedIfIsCompanyMember(
        $companyID,
        $userID
    ) {
        $member = $this->getRepo('CompanymemberView')->findOneBy(array(
            'userid' => $userID,
            'companyid' => $companyID,
        ));

        if (!is_null($member)) {
            $this->throwAccessDeniedIfNull($member);
        }
    }

    /**
     * @param $httpResponseCode
     *
     * @throws BadRequestHttpException
     */
    protected function throwBadRequestIfCallApiFailed(
        $httpResponseCode
    ) {
        if ($httpResponseCode != self::HTTP_STATUS_OK) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
    }

    /**
     * @param Request $request
     * @param int     $companyId
     * @param array   $rooms
     *
     * @return mixed
     */
    protected function callApiDisableGroupChat(
        Request $request,
        $companyId,
        $rooms
    ) {
        // get company admin
        $admin = $this->getRepo('CompanyAdmin')->findOneByCompanyid($companyId);
        $this->throwNotFoundIfNull($admin, self::NOT_FOUND_MESSAGE);

        $globals = $this->container->get('twig')->getGlobals();
        $adminJID = $admin->getUsername().'@'.$globals['xmpp_domain'];

        // the request auth from header
        $auth = $request->headers->get(self::HTTP_HEADER_AUTH);

        $apiUrl = $globals['openfire_innet_protocol'].
            $globals['openfire_innet_address'].
            $globals['openfire_innet_port'].
            $globals['openfire_plugin_groupchat'].
            $globals['openfire_plugin_groupchat_rooms'].
            $globals['openfire_plugin_groupchat_rooms_action'].
            $globals['openfire_plugin_groupchat_rooms_action_disable'];

        // set json data
        $jsonData = array(
            'owner' => $adminJID,
            'rooms' => $rooms,
        );

        // init curl
        $ch = curl_init($apiUrl);
        $this->get('curl_util')->callAPI($ch, json_encode($jsonData), $auth, 'DELETE');

        return curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }
}
