<?php

namespace Sandbox\ApiBundle\Controller\Door;

use Sandbox\SalesApiBundle\Controller\SalesRestController;

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
class DoorController extends SalesRestController
{
    const RESPONSE_NOT_VALID_CODE = 400005;
    const RESPONSE_NOT_VALID_MESSAGE = 'Response Not Valid';
    const TIME_NOT_VALID_CODE = 400006;
    const TIME_NOT_VALID_MESSAGE = 'Times Are Not Valid';
    const NO_ORDER_CODE = 400007;
    const NO_ORDER_MESSAGE = 'Orders Not Found';
    const BUILDING_NOT_FOUND_CODE = 400015;
    const BUILDING_NOT_FOUND_MESSAGE = 'Building Not Found';
    const CARDNO_NOT_FOUND_CODE = 400008;
    const CARDNO_NOT_FOUND_MESSAGE = 'Cardno Not Found';
    const STATUS_AUTHED = 'authed';
    const STATUS_UNAUTHED = 'unauthed';
    const STATUS_LOST = 'lossed';
}
