<?php

namespace Sandbox\ApiBundle\Constants;

class ProductOrderMessage
{
    const MEETING_START_MESSAGE = 'product_order.push_message.meeting_start';
    const MEETING_END_MESSAGE = 'product_order.push_message.meeting_end';

    const FLEXIBLE_START_MESSAGE = 'product_order.push_message.flexible_start';
    const FLEXIBLE_END_MESSAGE = 'product_order.push_message.flexible_end';

    const FIXED_START_MESSAGE = 'product_order.push_message.fixed_start';
    const FIXED_END_MESSAGE = 'product_order.push_message.fixed_end';

    const STUDIO_START_MESSAGE = 'product_order.push_message.studio_start';
    const STUDIO_END_MESSAGE = 'product_order.push_message.studio_end';

    const OFFICE_START_MESSAGE = 'product_order.push_message.office_start';
    const OFFICE_END_MESSAGE = 'product_order.push_message.office_end';
    const OFFICE_REJECTED_MESSAGE = 'product_order.push_message.office_reject';
    const OFFICE_ORDER_MESSAGE = 'product_order.push_message.office_order';
    const OFFICE_ACCEPTED_MESSAGE = 'product_order.push_message.office_accept';

    const APPOINT_MESSAGE_PART1 = 'product_order.push_message.appoint_first';
    const APPOINT_MESSAGE_PART2 = 'product_order.push_message.appoint_second';

    const CANCEL_ORDER_MESSAGE_PART1 = 'product_order.push_message.appoint_cancel_first';
    const CANCEL_ORDER_MESSAGE_PART2 = 'product_order.push_message.appoint_cancel_second';
}
