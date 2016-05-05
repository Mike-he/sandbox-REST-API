<?php

namespace Sandbox\ApiBundle\Constants;

class EventOrderExport
{
    const ORDER_NUMBER = 'order_number';
    const EVENT_NAME = 'event_name';
    const USER_ID = 'user_id';
    const PAY_AMOUNT = 'pay_amount';
    const PAYMENT_DATE = 'payment_date';
    const ORDER_STATUS = 'order_status';
    const USER_PHONE = 'user_phone';
    const USER_EMAIL = 'user_email';
    const PAYMENT_CHANNEL = 'channel';

    const TRANS_EVENT_ORDER_STATUS = 'event_order.status.';
    const TRANS_EVENT_ORDER_CHANNEL = 'event_order.channel.';

    const TRANS_EVENT_ORDER_HEADER_ORDER_NO = 'event_order.export_header.order_number';
    const TRANS_EVENT_ORDER_HEADER_EVENT_NAME = 'event_order.export_header.event_name';
    const TRANS_EVENT_ORDER_HEADER_USER_ID = 'event_order.export_header.user_id';
    const TRANS_EVENT_ORDER_HEADER_PAY_AMOUNT = 'event_order.export_header.pay_amount';
    const TRANS_EVENT_ORDER_HEADER_PAYMENT_DATE = 'event_order.export_header.payment_date';
    const TRANS_EVENT_ORDER_HEADER_ORDER_STATUS = 'event_order.export_header.order_status';
    const TRANS_EVENT_ORDER_HEADER_USER_PHONE = 'event_order.export_header.user_phone';
    const TRANS_EVENT_ORDER_HEADER_USER_EMAIL = 'event_order.export_header.user_email';
    const TRANS_EVENT_ORDER_HEADER_PAYMENT_CHANNEL = 'event_order.export_header.pay_channel';
}
