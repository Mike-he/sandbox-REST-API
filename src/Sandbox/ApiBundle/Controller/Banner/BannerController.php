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
    const URL_NULL_CODE = 400001;
    const URL_NULL_MESSAGE = 'Url cannot be null';

    const WRONG_SOURCE_CODE = 400002;
    const WRONG_SOURCE_MESSAGE = 'Wrong Source';

    const BANNER_ALREADY_EXIST_CODE = 400003;
    const BANNER_ALREADY_EXIST_MESSAGE = 'This Banner Already Exists';
}
