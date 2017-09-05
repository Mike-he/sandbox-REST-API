<?php

namespace Sandbox\ClientApiBundle\Controller\Auth;

use Sandbox\ApiBundle\Controller\Auth\AuthController;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use JMS\Serializer\SerializationContext;

/**
 * Client Auth controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientAuthController extends AuthController
{
    /**
     * Token auth.
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
     * @Route("/me")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getClientAuthMeAction(
        Request $request
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        if ($myUser->isBanned()) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        $view = new View();
        $view->setData(array(
            'id' => $myUserId,
        ));

        return $view;
    }

    /**
     * Get user token info.
     *
     * @param Request $request
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/token")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postClientAuthToken(
        Request $request
    ) {
        $requestContent = json_decode($request->getContent(), true);
        $myUserToken = $requestContent['token'];
        $userToken = $this->getRepo('User\UserToken')->findOneByToken($myUserToken);
        $this->throwNotFoundIfNull($userToken, self::NOT_FOUND_MESSAGE);

        $view = new View($userToken);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('main')));

        return $view;
    }

    /**
     * DES Encrypt Password.
     *
     * @Route("/password")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getClientAuthPasswordAction(
        Request $request
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($myUserId);

        if ($myUser->isBanned()) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        $key = 'go_beta@';
        $plain = $myUser->getPassword();
        $encrypt = $this->encrypt($key, $plain);

        $view = new View();
        $view->setData(array(
            'xmpp_username' => $myUser->getXmppUsername(),
            'password' => $encrypt,
        ));

        return $view;
    }

    /**
     * PHP DES 加密程式.
     *
     * @param $key 密鑰（八個字元內）
     * @param $encrypt 要加密的明文
     *
     * @return string 密文
     */
    public function encrypt($key, $encrypt)
    {
        // 根據 PKCS#7 RFC 5652 Cryptographic Message Syntax (CMS) 修正 Message 加入 Padding
        $block = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
        $pad = $block - (strlen($encrypt) % $block);
        $encrypt .= str_repeat(chr($pad), $pad);

        // 不需要設定 IV 進行加密
        $passcrypt = mcrypt_encrypt(MCRYPT_DES, $key, $encrypt, MCRYPT_MODE_ECB);

        return base64_encode($passcrypt);
    }

    /**
     * PHP DES 解密程式.
     *
     * @param $key 密鑰（八個字元內）
     * @param $decrypt 要解密的密文
     *
     * @return string 明文
     */
    public function decrypt($key, $decrypt)
    {
        // 不需要設定 IV
        $str = mcrypt_decrypt(MCRYPT_DES, $key, base64_decode($decrypt), MCRYPT_MODE_ECB);

        // 根據 PKCS#7 RFC 5652 Cryptographic Message Syntax (CMS) 修正 Message 移除 Padding
        $pad = ord($str[strlen($str) - 1]);

        return substr($str, 0, strlen($str) - $pad);
    }
}
