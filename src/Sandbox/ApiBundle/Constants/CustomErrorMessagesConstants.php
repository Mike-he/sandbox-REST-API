<?php

namespace Sandbox\ApiBundle\Constants;

class CustomErrorMessagesConstants
{
    // 400 BAD REQUEST
    // LEASE
    const ERROR_LEASE_STATUS_NOT_CORRECT_MESSAGE = 'The status of lease does not correct';
    const ERROR_LEASE_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE = 'The format of payload for lease does not correct';
    const ERROR_LEASE_KEEP_AT_LEAST_ONE_BILL_MESSAGE = 'Sorry, you can not remove all bills, please keeping at least one bill.';
    const ERROR_LEASE_END_BILL_UNPAID_MESSAGE = 'Sorry, you can not end lease, there are bills unpaid.';

    // LEASE BILL
    const ERROR_BILL_STATUS_NOT_CORRECT_MESSAGE = 'The status of bill does not correct';
    const ERROR_BILLS_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE = 'The format of payload for bills does not correct';
    const ERROR_STATUS_MESSAGE = 'The status is error';

    // SALES COMPANY
    const ERROR_SALES_COMPANY_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE = 'The format of payload for sales company does not correct';
    const ERROR_SERVICE_INFO_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE = 'The format of payload for service info does not correct';
    const ERROR_CAN_NOT_MORE_THAN_TWO_ADMINS = 'Sorry, You can not set more than two admins';
    const ERROR_CAN_NOT_MORE_THAN_TWO_COFFEE_ADMINS = 'Sorry, You can not set more than two coffee admins';

    // 404 NOT FOUND MESSAGE
    // LEASE
    const ERROR_LEASE_NOT_FOUND_MESSAGE = 'The lease does not exist';
    const ERROR_DRAWEE_NOT_FOUND_MESSAGE = 'The drawee does not exist';
    const ERROR_SUPERVISOR_NOT_FOUND_MESSAGE = 'The supervisor does not exist';
    const ERROR_LEASE_RENT_TYPE_NOT_FOUND_MESSAGE = 'The lease rent type does not exist';

    // LEASE BILL
    const ERROR_BILL_NOT_FOUND_MESSAGE = 'The bill does not exist';

    // PRODUCT
    const ERROR_PRODUCT_NOT_FOUND_MESSAGE = 'The product does not exist';
    const ERROR_APPOINTMENT_NOT_FOUND_MESSAGE = 'The appointment does not exist';

    // SALES COMPANY
    const ERROR_ADMIN_NOT_FOUND_MESSAGE = 'The admin does not exist';
    const ERROR_COFFEE_ADMIN_NOT_FOUND_MESSAGE = 'The coffee admin does not exist';
    const ERROR_SALES_COMPANY_NOT_FOUND_MESSAGE = 'The sales company does not exist';
    const ERROR_SALES_COMPANY_SERVICE_NOT_FOUND_MESSAGE = 'The sales company service does not exist';
}
