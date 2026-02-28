<?php
/**
 * Hardcoded Airtel SMS sender (same as test/sms.php).
 * No DB; templates and credentials fixed.
 */

// Credentials (from test/sms.php)
define('SMS_API_URL', 'https://iqsms.airtel.in/api/v1/send-prepaid-sms');
define('SMS_CUSTOMER_ID', '72c5ff0d-4624-4972-bc1c-dcef261dd7f7');
define('SMS_USERNAME', 'f8758d62_260c_404b_8d4a_a15ce94593d4');
define('SMS_PASSWORD', 'jx0NgVQjBT');
define('SMS_ENTITY_ID', '1001791223662244844');

// Template ID 1007289085098641045 - Welcome
// "Dear var, welcome to PROGEEDEE Ventures Private Limited. You have successfully registered for the Golden Dream Savings Plan. Your Customer ID is var. Visit https://goldendream.in/ for more details."
define('SMS_TEMPLATE_WELCOME_ID', '1007289085098641045');
define('SMS_SOURCE_WELCOME', 'PGDVTR');

// Template ID 1007000046423973167 - Payment received
// "Dear var Thank you for choosing PROGEEDEE Ventures Private Limited Golden Dream Savings Plan We have received your payment of Rs var"
define('SMS_TEMPLATE_PAYMENT_ID', '1007000046423973167');
define('SMS_SOURCE_PAYMENT', 'PRGDVN');

/**
 * Send one SMS via Airtel API (same cURL as test/sms.php).
 * @param array $data Payload: customerId, destinationAddress, message, sourceAddress, messageType, dltTemplateId, entityId
 * @return array ['ok' => bool, 'httpCode' => int, 'response' => string]
 */
function smsHelperSend($data) {
    $ch = curl_init(SMS_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'content-type: application/json',
        'Authorization: Basic ' . base64_encode(SMS_USERNAME . ':' . SMS_PASSWORD)
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'ok' => ($httpCode >= 200 && $httpCode < 300),
        'httpCode' => $httpCode,
        'response' => $response === false ? '' : $response
    ];
}

/**
 * Format phone: 10 digits -> 91XXXXXXXXXX
 */
function smsHelperPhone($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    if (strlen($phone) === 10) {
        return '91' . $phone;
    }
    if (substr($phone, 0, 2) !== '91') {
        return '91' . $phone;
    }
    return $phone;
}

/**
 * Welcome SMS (onboarding) - Template 1007289085098641045, PGDVTR
 * var1 = customer name, var2 = customer unique ID
 */
function sendWelcomeSMSHardcoded($phoneNumber, $customerName, $customerUniqueID) {
    $phone = smsHelperPhone($phoneNumber);
    if (strlen($phone) < 12) {
        error_log("SMSHelper welcome: invalid phone");
        return false;
    }
    // Exact template text; replace first var with name, second with customer ID
    $message = "Dear " . $customerName . ", welcome to PROGEEDEE Ventures Private Limited. You have successfully registered for the Golden Dream Savings Plan. Your Customer ID is " . $customerUniqueID . ". Visit https://goldendream.in/ for more details.";

    $data = [
        'customerId' => SMS_CUSTOMER_ID,
        'destinationAddress' => [$phone],
        'message' => $message,
        'sourceAddress' => SMS_SOURCE_WELCOME,
        'messageType' => 'SERVICE_EXPLICIT',
        'dltTemplateId' => SMS_TEMPLATE_WELCOME_ID,
        'entityId' => SMS_ENTITY_ID
    ];

    $result = smsHelperSend($data);
    if (!$result['ok']) {
        error_log("SMSHelper welcome failed: HTTP " . $result['httpCode'] . " " . $result['response']);
    }
    return $result['ok'];
}

/**
 * Payment verified SMS - Template 1007000046423973167, PRGDVN
 * var1 = customer name, var2 = amount (no decimals)
 * Template: "Dear var Thank you for choosing PROGEEDEE Ventures Private Limited Golden Dream Savings Plan We have received your payment of Rs var"
 */
function sendPaymentVerifiedSMSHardcoded($phoneNumber, $customerName, $amount) {
    $phone = smsHelperPhone($phoneNumber);
    if (strlen($phone) < 12) {
        error_log("SMSHelper payment verified: invalid phone");
        return false;
    }
    $amountStr = number_format((float) $amount, 0, '', '');
    $message = "Dear " . $customerName . " Thank you for choosing PROGEEDEE Ventures Private Limited Golden Dream Savings Plan We have received your payment of Rs " . $amountStr;

    $data = [
        'customerId' => SMS_CUSTOMER_ID,
        'destinationAddress' => [$phone],
        'message' => $message,
        'sourceAddress' => SMS_SOURCE_PAYMENT,
        'messageType' => 'SERVICE_EXPLICIT',
        'dltTemplateId' => SMS_TEMPLATE_PAYMENT_ID,
        'entityId' => SMS_ENTITY_ID
    ];

    $result = smsHelperSend($data);
    if (!$result['ok']) {
        error_log("SMSHelper payment verified failed: HTTP " . $result['httpCode'] . " " . $result['response']);
    }
    return $result['ok'];
}

/**
 * Payment rejected - no DLT template; send plain (may get PE-TM hash error on some gateways).
 * If your gateway requires a template, add a reject template and call a new function here.
 */
function sendPaymentRejectedSMSHardcoded($phoneNumber, $customerName, $amount, $remarks = '') {
    $phone = smsHelperPhone($phoneNumber);
    if (strlen($phone) < 12) {
        error_log("SMSHelper payment rejected: invalid phone");
        return false;
    }
    $amountStr = number_format((float) $amount, 0, '', '');
    $message = "Dear " . $customerName . ", your payment of Rs " . $amountStr . " has been rejected.";
    if ($remarks !== '') {
        $message .= " Remarks: " . $remarks;
    }
    // Use payment template structure; message won't match template so gateway may return 400 (PE-TM hash).
    $data = [
        'customerId' => SMS_CUSTOMER_ID,
        'destinationAddress' => [$phone],
        'message' => $message,
        'sourceAddress' => SMS_SOURCE_PAYMENT,
        'messageType' => 'SERVICE_EXPLICIT',
        'dltTemplateId' => SMS_TEMPLATE_PAYMENT_ID,
        'entityId' => SMS_ENTITY_ID
    ];

    $result = smsHelperSend($data);
    if (!$result['ok']) {
        error_log("SMSHelper payment rejected failed: HTTP " . $result['httpCode'] . " " . $result['response']);
    }
    return $result['ok'];
}
