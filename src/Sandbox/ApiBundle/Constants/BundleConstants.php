<?php

namespace Sandbox\ApiBundle\Constants;

class BundleConstants
{
    const BUNDLE = 'SandboxApiBundle';

    const SANDBOX_CUSTOM_HEADER = 'Sandbox-Auth: ';

    const HTTP_STATUS_OK = 200;

    const PRODUCT_ORDER_ENTITY = 'SandboxApiBundle:Order\ProductOrder';
    const SHOP_ORDER_ENTITY = 'SandboxApiBundle:Shop\ShopOrder';
    const EVENT_ORDER_ENTITY = 'SandboxApiBundle:Event\EventOrder';
    const TOP_UP_ORDER_ENTITY = 'SandboxApiBundle:Order\TopUpOrder';

    const PING_CREATE_CUSTOMER = 'https://api.pingxx.com/v1/customers';
}
