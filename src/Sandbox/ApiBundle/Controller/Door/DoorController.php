<?php

namespace Sandbox\ApiBundle\Controller\Door;

use Sandbox\ApiBundle\Controller\SandboxRestController;

/**
 * Door Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class DoorController extends SandboxRestController
{
    public function getSessionId()
    {
        $apiUrl = 'http://192.168.16.234:13390/ADSWebService.asmx/Login?Username=admin&Password=admin';

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }
}
