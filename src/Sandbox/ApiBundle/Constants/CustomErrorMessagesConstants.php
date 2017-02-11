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

    // SALES COMPANY
    const ERROR_SALES_COMPANY_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE = 'The format of payload for sales company does not correct';
    const ERROR_SERVICE_INFO_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE = 'The format of payload for service info does not correct';
    const ERROR_CAN_NOT_MORE_THAN_TWO_ADMINS = 'Sorry, You can not set more than two admins';
    const ERROR_CAN_NOT_MORE_THAN_TWO_COFFEE_ADMINS = 'Sorry, You can not set more than two coffee admins';

    // Finance
    const ERROR_FINANCE_BILL_STATUS_NOT_CORRECT_CODE = 400001;
    const ERROR_FINANCE_BILL_STATUS_NOT_CORRECT_MESSAGE = 'The status of bill does not correct';
    const ERROR_FINANCE_BILLS_PAYLOAD_FORMAT_NOT_CORRECT_CODE= 400002;
    const ERROR_FINANCE_BILLS_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE = 'The format of payload for bills does not correct';
    const ERROR_FINANCE_BILL_MORE_THAN_TOTAL_SERVICE_FEE_CODE = 400003;
    const ERROR_FINANCE_BILL_MORE_THAN_TOTAL_SERVICE_FEE_MESSAGE = 'More than the total service fees';


    // 404 NOT FOUND MESSAGE
    // LEASE
    const ERROR_LEASE_NOT_FOUND_MESSAGE = 'The lease does not exist';
    const ERROR_DRAWEE_NOT_FOUND_MESSAGE = 'The drawee does not exist';
    const ERROR_SUPERVISOR_NOT_FOUND_MESSAGE = 'The supervisor does not exist';
    const ERROR_LEASE_RENT_TYPE_NOT_FOUND_MESSAGE = 'The lease rent type does not exist';

    // LEASE BILL
    const ERROR_BILL_NOT_FOUND_MESSAGE = 'The bill does not exist';
    const ERROR_BILL_CHANNEL_IS_EMPTY_MESSAGE = 'Channel cannot be empty';
    const ERROR_BILL_NOT_SUPPORT_BALANCE_PAYMENT_MESSAGE = 'Does not support the balance payment';
    const ERROR_BILL_COLLECTION_METHOD_MESSAGE = 'The company collection method does not correct';

    // PRODUCT
    const ERROR_PRODUCT_NOT_FOUND_MESSAGE = 'The product does not exist';
    const ERROR_APPOINTMENT_NOT_FOUND_MESSAGE = 'The appointment does not exist';

    // SALES COMPANY
    const ERROR_ADMIN_NOT_FOUND_MESSAGE = 'The admin does not exist';
    const ERROR_COFFEE_ADMIN_NOT_FOUND_MESSAGE = 'The coffee admin does not exist';
    const ERROR_SALES_COMPANY_NOT_FOUND_MESSAGE = 'The sales company does not exist';
    const ERROR_SALES_COMPANY_SERVICE_NOT_FOUND_MESSAGE = 'The sales company service does not exist';
    const ERROR_SALES_COMPANY_INVOICE_NOT_FOUND_CODE = 404001;
    const ERROR_SALES_COMPANY_INVOICE_NOT_FOUND_MESSAGE = 'The sales company invoice does not exist';
    const ERROR_SALES_COMPANY_EXPRESS_NOT_FOUND_CODE = 404002;
    const ERROR_SALES_COMPANY_EXPRESS_NOT_FOUND_MESSAGE = 'The sales company express does not exist';
}
