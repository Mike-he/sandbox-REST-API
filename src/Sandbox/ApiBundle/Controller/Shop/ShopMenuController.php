<?php

namespace Sandbox\ApiBundle\Controller\Shop;

use Sandbox\ApiBundle\Entity\Shop\ShopMenu;

/**
 * Shop Menu Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ShopMenuController extends ShopController
{
    /**
     * @param $id
     *
     * @return ShopMenu $menu
     */
    public function findShopMenuById(
        $id
    ) {
        $menu = $this->getRepo('Shop\ShopMenu')->find($id);
        $this->throwNotFoundIfNull($menu, self::NOT_FOUND_MESSAGE);

        return $menu;
    }
}
