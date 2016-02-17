<?php

namespace Sandbox\ApiBundle\Controller\Shop;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Shop\Shop;

/**
 * Shop Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xue <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ShopController extends SandboxRestController
{
    /**
     * @param $id
     *
     * @return Shop $shop
     */
    public function findShopById(
        $id
    ) {
        $shop = $this->getRepo('Shop\Shop')->find($id);
        $this->throwNotFoundIfNull($shop, self::NOT_FOUND_MESSAGE);

        return $shop;
    }
}
