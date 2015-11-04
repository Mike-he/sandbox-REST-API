<?php

namespace Sandbox\ApiBundle\Controller\Banner;

use Sandbox\ApiBundle\Controller\SandboxRestController;

/**
 * Banner Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class BannerController extends SandboxRestController
{
    const ATTACHMENT_NULL = 'Attachment cannot be null';
    const WRONG_SOURCE = 'Wrong Source';
    const URL_NULL = 'Url cannot be null';
    const BANNER_ALREADY_EXIST = 'This Banner Already Exists';
}
