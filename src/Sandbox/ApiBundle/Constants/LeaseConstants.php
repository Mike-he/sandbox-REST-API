<?php

namespace Sandbox\ApiBundle\Constants;

class LeaseConstants
{
    const ACTION_TYPE = 'lease';

    // push messages
    const APPLICATION_REJECTED_MESSAGE = 'lease.push_message.application_rejected';
    const APPLICATION_APPROVED_MESSAGE = 'lease.push_message.application_approved';

    const LEASE_CLOSED_MESSAGE = 'lease.push_message.lease_closed';
    const LEASE_PERFORMING_MESSAGE = 'lease.push_message.lease_performing';
    const LEASE_TERMINATED_MESSAGE = 'lease.push_message.lease_terminated';
    const LEASE_ENDED_WITH_UNPAID_BILLS_MESSAGE = 'lease.push_message.lease_ended_with_unpaid_bills';
    const LEASE_ENDED_WITHOUT_UNPAID_BILLS_MESSAGE = 'lease.push_message.lease_ended_without_unpaid_bills';
    const LEASE_ENDED_MESSAGE = 'lease.push_message.lease_ended';
    const LEASE_BILL_UNPAID_MESSAGE_PART1 = 'lease.push_message.lease_bill_unpaid_first';
    const LEASE_BILL_UNPAID_MESSAGE_PART2 = 'lease.push_message.lease_bill_unpaid_second';
    const LEASE_BILL_TRANSFER_RETURNED_MESSAGE = 'lease.push_message.lease_bill_transfer_returned';

    const TRANS_LEASE_BILL_ORDER_METHOD = 'lease.order_method.';
    const TRANS_LEASE_STATUS = 'lease.lease_status.';
    const TRANS_LEASE_CLUE_STATUS = 'lease.clue_status.';
    const TRANS_LEASE_OFFER_STATUS = 'lease.offer_status.';
    const TRANS_LEASE_BILL_STATUS = 'lease.bill_status.';
}
