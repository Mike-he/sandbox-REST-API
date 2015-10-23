<?php

namespace Sandbox\ApiBundle\Controller\Food;

use Sandbox\ApiBundle\Controller\SandboxRestController;

/**
 * Food Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class FoodController extends SandboxRestController
{
    const LOCATION_CANNOT_NULL = 'City, Building cannot be null';
    const ATTACHMENT_CANNOT_NULL = 'Attachment cannot be null';
    const ENTITY_FOOD_ATTACHMENT = 'Food\FoodAttachment';
    const ENTITY_FOOD_FORM = 'Food\FoodForm';
}
