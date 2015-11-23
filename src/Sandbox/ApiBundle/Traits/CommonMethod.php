<?php

namespace Sandbox\ApiBundle\Traits;

/**
 * Common Method Trait.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait CommonMethod
{
    abstract protected function getRepo($repo);

    abstract protected function getGlobals();

    abstract protected function getContainer();
}
