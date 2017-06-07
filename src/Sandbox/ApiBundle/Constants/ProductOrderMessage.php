<?php

namespace Sandbox\ApiBundle\Constants;

class ProductOrderMessage
{
    const MEETING_START_MESSAGE = 'product_order.push_message.meeting_start';
    const MEETING_END_MESSAGE = 'product_order.push_message.meeting_end';

    const DESK_START_MESSAGE = 'product_order.push_message.desk_start';
    const DESK_END_MESSAGE = 'product_order.push_message.desk_end';

    const OTHERS_START_MESSAGE = 'product_order.push_message.others_start';
    const OTHERS_END_MESSAGE = 'product_order.push_message.others_end';

    const OFFICE_START_MESSAGE = 'product_order.push_message.office_start';
    const OFFICE_END_MESSAGE = 'product_order.push_message.office_end';
    const OFFICE_REJECTED_MESSAGE = 'product_order.push_message.office_reject';
    const OFFICE_ORDER_MESSAGE = 'product_order.push_message.office_order';
    const OFFICE_ACCEPTED_MESSAGE = 'product_order.push_message.office_accept';

    const APPOINT_MESSAGE_PART1 = 'product_order.push_message.appoint_first';
    const APPOINT_MESSAGE_PART2 = 'product_order.push_message.appoint_second';

    const CANCEL_ORDER_MESSAGE_PART1 = 'product_order.push_message.appoint_cancel_first';
    const CANCEL_ORDER_MESSAGE_PART2 = 'product_order.push_message.appoint_cancel_second';

    const ORDER_PREORDER_MESSAGE = 'product_order.push_message.order_preorder';

    const ORDER_CHANGE_PRICE_MESSAGE = 'product_order.push_message.order_change_price';
    const ORDER_ADMIN_CANCELLED_MESSAGE = 'product_order.push_message.order_admin_cancelled';
    const ORDER_TRANSFER_RETURNED_MESSAGE = 'product_order.push_message.order_transfer_returned';

    const PAYMENT_NOTIFICATION_MESSAGE = 'product_order.push_message.payment_notification';
}
