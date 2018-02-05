<?php

namespace Sandbox\ApiBundle\Constants;

class CustomErrorMessagesConstants
{
    // 400 BAD REQUEST
    const ERROR_STATUS_NOT_CORRECT_CODE = 400001;
    const ERROR_STATUS_NOT_CORRECT_MESSAGE = 'The status does not correct';
    const ERROR_PAYLOAD_FORMAT_NOT_CORRECT_CODE = 400002;
    const ERROR_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE = 'The format of payload does not correct';

    // LEASE
    const ERROR_LEASE_KEEP_AT_LEAST_ONE_BILL_MESSAGE = 'Sorry, you can not remove all bills, please keeping at least one bill.';

    // SALES COMPANY
    const ERROR_SALES_COMPANY_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE = 'The format of payload for sales company does not correct';
    const ERROR_SERVICE_INFO_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE = 'The format of payload for service info does not correct';
    const ERROR_CAN_NOT_MORE_THAN_TWO_ADMINS = 'Sorry, You can not set more than two admins';
    const ERROR_CAN_NOT_MORE_THAN_TWO_COFFEE_ADMINS = 'Sorry, You can not set more than two coffee admins';

    // Finance
    const ERROR_FINANCE_BILL_STATUS_NOT_CORRECT_CODE = 400001;
    const ERROR_FINANCE_BILL_STATUS_NOT_CORRECT_MESSAGE = 'The status of bill does not correct';
    const ERROR_FINANCE_BILLS_PAYLOAD_FORMAT_NOT_CORRECT_CODE = 400002;
    const ERROR_FINANCE_BILLS_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE = 'The format of payload for bills does not correct';
    const ERROR_FINANCE_BILL_MORE_THAN_TOTAL_SERVICE_FEE_CODE = 400003;
    const ERROR_FINANCE_BILL_MORE_THAN_TOTAL_SERVICE_FEE_MESSAGE = 'More than the total service fees';
    const ERROR_FINANCE_SHORT_RENT_INVOICE_STATUS_NOT_CORRECT_CODE = 400004;
    const ERROR_FINANCE_SHORT_RENT_INVOICE_STATUS_NOT_CORRECT_MESSAGE = 'Invoice status does not correct';
    const ERROR_FINANCE_SHORT_RENT_INVOICE_PAYLOAD_FORMAT_NOT_CORRECT_CODE = 400005;
    const ERROR_FINANCE_SHORT_RENT_INVOICE_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE = 'The format of payload for invoice does not correct';

    // CUSTOMER SERVICE
    const ERROR_CUSTOMER_SERVICE_PAYLOAD_NOT_CORRECT_CODE = 'The format of payload for customer service does not correct';
    const ERROR_JMESSAGE_ERROR_MESSAGE = 'Jmessage Error';

    // Membership Card && Group
    const ERROR_CARD_GROUP_CAN_NOT_BE_EDITED_CODE = 400011;
    const ERROR_CARD_GROUP_CAN_NOT_BE_EDITED_MESSAGE = 'The card group cannot be edited';

    //Bean
    const ERROR_BEAN_OPERATION_TODAY_CODE = 400012;
    const ERROR_BEAN_OPERATION_TODAY_MESSAGE = 'Today has been operating';

    //Offline Transfer
    const ERROR_TRANSFER_STATUS_CODE = 400013;
    const ERROR_TRANSFER_STATUS_MESSAGE = 'Wrong Transfer Status';

    // Expert
    const ERROR_MORE_THAN_QUANTITY_CODE = 400014;
    const ERROR_MORE_THAN_QUANTITY_MESSAGE = '超过可选择领域数';
    const ERROR_ID_CARD_HAS_CERTIFIED_CODE = 400015;
    const ERROR_ID_CARD_HAS_CERTIFIED_MESSAGE = '该身份证已被认证';
    const ERROR_ID_CARD_AUTHENTICATION_FAILURE_CODE = 400016;
    const ERROR_ID_CARD_AUTHENTICATION_FAILURE_MESSAGE = '实名认证失败';
    const ERROR_EXPERT_HAS_CREATED_CODE = 400017;
    const ERROR_EXPERT_HAS_CREATED_MESSAGE = '您已是专家，请勿重复提交';
    const ERROR_EXPERT_HAS_BANNED_CODE = 400018;
    const ERROR_EXPERT_HAS_BANNED_MESSAGE = '该专家已冻结';
    const ERROR_EXPERT_WAS_NOT_IN_SERVICE_CODE = 400019;
    const ERROR_EXPERT_WAS_NOT_IN_SERVICE_MESSAGE = '该专家已停止服务';
    const ERROR_ONLY_MY_OWN_OPERATION_CODE = 400020;
    const ERROR_ONLY_MY_OWN_OPERATION_MESSAGE = '只能本人操作';
    const ERROR_EXPERT_STATUS_ERROR_CODE = 400021;
    const ERROR_EXPERT_STATUS_ERROR_MESSAGE = '状态错误';
    const ERROR_EXPERT_HAS_NOT_PASSED_CODE = 400022;
    const ERROR_EXPERT_HAS_NOT_PASSED_MESSAGE = '该专家还未通过审核';

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
    const ERROR_CUSTOMER_SERVICE_NOT_FOUND_MESSAGE = 'The customer service does not exist';
    const ERROR_SALES_COMPANY_ROOM_BUILDING_NOT_FOUND_MESSAGE = 'The building does not exist';
}
