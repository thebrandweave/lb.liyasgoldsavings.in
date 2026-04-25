<?php

require_once(__DIR__ . "/SMSAPI.php");
require_once(__DIR__ . "/SMSHelper.php");
require_once(__DIR__ . "/WhatsAppMetaAPI.php");

class NotificationService
{
    private $conn;
    private $smsAPI;
    private $whatsappAPI;

    public function __construct($database)
    {
        $this->conn = $database->getConnection();
        $this->smsAPI = new SMSAPI($database);
        $this->whatsappAPI = new WhatsAppMetaAPI($database);
    }

    private function getChannels()
    {
        try {
            $stmt = $this->conn->query("SELECT IsSMSEnabled, IsWhatsAppEnabled FROM NotificationChannelSettings ORDER BY SettingID DESC LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $this->conn->exec("INSERT INTO NotificationChannelSettings (IsSMSEnabled, IsWhatsAppEnabled) VALUES (1, 1)");
                return ['sms' => true, 'whatsapp' => true];
            }
            return [
                'sms' => !empty($row['IsSMSEnabled']),
                'whatsapp' => !empty($row['IsWhatsAppEnabled'])
            ];
        } catch (Exception $e) {
            error_log("NotificationService channel fetch failed: " . $e->getMessage());
            return ['sms' => true, 'whatsapp' => false];
        }
    }

    public function sendGeneric($phoneNumber, $message)
    {
        $channels = $this->getChannels();
        $result = ['sms' => null, 'whatsapp' => null];

        if ($channels['sms']) {
            $result['sms'] = $this->smsAPI->sendSMS($phoneNumber, $message);
        }

        if ($channels['whatsapp']) {
            $result['whatsapp'] = $this->whatsappAPI->sendTemplate($phoneNumber);
        }

        return $result;
    }

    public function sendWelcomeCustomer($phoneNumber, $customerName, $customerUniqueID)
    {
        $channels = $this->getChannels();
        $result = ['sms' => null, 'whatsapp' => null];

        if ($channels['sms']) {
            $result['sms'] = $this->smsAPI->sendWelcomeSMS($phoneNumber, $customerName, $customerUniqueID);
        }

        if ($channels['whatsapp']) {
            $params = [
                ['type' => 'text', 'parameter_name' => 'customer_name', 'text' => (string)$customerName],
                ['type' => 'text', 'parameter_name' => 'customer_id', 'text' => (string)$customerUniqueID]
            ];
            $buttonParams = [
                ['index' => 0, 'text' => 'la']
            ];
            $result['whatsapp'] = $this->whatsappAPI->sendTemplate($phoneNumber, 'gd_customer_register', null, $params, $buttonParams);
        }

        return $result;
    }

    public function sendPaymentVerified($phoneNumber, $customerName, $amount, $customerId = '', $schemeName = '', $installmentName = '')
    {
        $channels = $this->getChannels();
        $result = ['sms' => null, 'whatsapp' => null];

        if ($channels['sms']) {
            $result['sms'] = sendPaymentVerifiedSMSHardcoded($phoneNumber, $customerName, $amount);
        }

        if ($channels['whatsapp']) {
            $params = [
                ['type' => 'text', 'parameter_name' => 'customer_name', 'text' => (string)$customerName],
                ['type' => 'text', 'parameter_name' => 'payment_amount', 'text' => number_format((float)$amount, 0, '', '')],
                ['type' => 'text', 'parameter_name' => 'customer_id', 'text' => ($customerId !== '' ? (string)$customerId : 'NA')],
                ['type' => 'text', 'parameter_name' => 'scheme_name', 'text' => ($schemeName !== '' ? (string)$schemeName : 'NA')],
                ['type' => 'text', 'parameter_name' => 'installment_name', 'text' => ($installmentName !== '' ? (string)$installmentName : 'NA')]
            ];
            $result['whatsapp'] = $this->whatsappAPI->sendTemplate($phoneNumber, 'payment_confirmation_with_details', null, $params);
        }

        return $result;
    }

    public function sendPaymentRejected($phoneNumber, $customerName, $amount, $remarks = '')
    {
        $channels = $this->getChannels();
        $result = ['sms' => null, 'whatsapp' => null];

        if ($channels['sms']) {
            $result['sms'] = sendPaymentRejectedSMSHardcoded($phoneNumber, $customerName, $amount, $remarks);
        }

        if ($channels['whatsapp']) {
            $params = [
                ['type' => 'text', 'parameter_name' => 'customer_name', 'text' => (string)$customerName],
                ['type' => 'text', 'parameter_name' => 'payment_amount', 'text' => number_format((float)$amount, 0, '', '')],
                ['type' => 'text', 'parameter_name' => 'rejection_remarks', 'text' => ($remarks !== '' ? $remarks : 'not_available')]
            ];
            $result['whatsapp'] = $this->whatsappAPI->sendTemplate($phoneNumber, 'gd_payment_rejected', null, $params);
        }

        return $result;
    }

    public function sendWalletUpdate($phoneNumber, $promoterName, $walletAction, $transactionAmount, $newBalanceAmount, $adminRemarks = '')
    {
        $channels = $this->getChannels();
        $result = ['sms' => null, 'whatsapp' => null];

        $smsMessage = "Dear " . $promoterName . ", your wallet has been " . $walletAction . " with Rs " .
            number_format((float)$transactionAmount, 0) . ". New balance: Rs " . number_format((float)$newBalanceAmount, 0);
        if ($adminRemarks !== '') {
            $smsMessage .= ". Remarks: " . $adminRemarks;
        }

        if ($channels['sms']) {
            $result['sms'] = $this->smsAPI->sendSMS($phoneNumber, $smsMessage);
        }

        if ($channels['whatsapp']) {
            $params = [
                ['type' => 'text', 'parameter_name' => 'promoter_name', 'text' => (string)$promoterName],
                ['type' => 'text', 'parameter_name' => 'wallet_action', 'text' => (string)$walletAction],
                ['type' => 'text', 'parameter_name' => 'transaction_amount', 'text' => number_format((float)$transactionAmount, 0, '', '')],
                ['type' => 'text', 'parameter_name' => 'new_balance_amount', 'text' => number_format((float)$newBalanceAmount, 0, '', '')],
                ['type' => 'text', 'parameter_name' => 'admin_remarks', 'text' => ($adminRemarks !== '' ? $adminRemarks : 'not_available')]
            ];
            $result['whatsapp'] = $this->whatsappAPI->sendTemplate($phoneNumber, 'gd_wallet_update', null, $params);
        }

        return $result;
    }

    public function sendWithdrawalStatus($phoneNumber, $userName, $withdrawalAmount, $withdrawalStatus, $adminRemarks = '')
    {
        $channels = $this->getChannels();
        $result = ['sms' => null, 'whatsapp' => null];

        $smsMessage = "Dear " . $userName . ", your withdrawal request of Rs " .
            number_format((float)$withdrawalAmount, 0) . " has been " . strtolower($withdrawalStatus);
        if ($adminRemarks !== '') {
            $smsMessage .= ". Remarks: " . $adminRemarks;
        }

        if ($channels['sms']) {
            $result['sms'] = $this->smsAPI->sendSMS($phoneNumber, $smsMessage);
        }

        if ($channels['whatsapp']) {
            $params = [
                ['type' => 'text', 'parameter_name' => 'user_name', 'text' => (string)$userName],
                ['type' => 'text', 'parameter_name' => 'withdrawal_amount', 'text' => number_format((float)$withdrawalAmount, 0, '', '')],
                ['type' => 'text', 'parameter_name' => 'withdrawal_status', 'text' => strtolower((string)$withdrawalStatus)],
                ['type' => 'text', 'parameter_name' => 'admin_remarks', 'text' => ($adminRemarks !== '' ? $adminRemarks : 'not_available')]
            ];
            $result['whatsapp'] = $this->whatsappAPI->sendTemplate($phoneNumber, 'gd_withdrawal_status', null, $params);
        }

        return $result;
    }

    public function sendPaymentReminder($phoneNumber, $customerName, $schemeName, $installmentName, $dueAmount, $dueDate)
    {
        $channels = $this->getChannels();
        $result = ['sms' => null, 'whatsapp' => null];

        $message = "Dear " . $customerName . ",\n\n";
        $message .= "This is a reminder that your payment for " . $schemeName . " - " . $installmentName . " is pending.\n\n";
        $message .= "Amount: ₹" . number_format((float)$dueAmount, 2) . "\n";
        $message .= "Due Date: " . $dueDate . "\n\n";
        $message .= "Please submit your payment at the earliest.\n\n";
        $message .= "If already paid, please ignore this message.\n\n";
        $message .= "Thank you,\nGolden Dreams Team";

        if ($channels['sms']) {
            $result['sms'] = $this->smsAPI->sendSMS($phoneNumber, $message);
        }

        if ($channels['whatsapp']) {
            $params = [
                ['type' => 'text', 'parameter_name' => 'customer_name', 'text' => (string)$customerName],
                ['type' => 'text', 'parameter_name' => 'scheme_name', 'text' => (string)$schemeName],
                ['type' => 'text', 'parameter_name' => 'installment_name', 'text' => (string)$installmentName],
                ['type' => 'text', 'parameter_name' => 'due_amount', 'text' => number_format((float)$dueAmount, 0, '', '')],
                ['type' => 'text', 'parameter_name' => 'due_date', 'text' => (string)$dueDate]
            ];
            $result['whatsapp'] = $this->whatsappAPI->sendTemplate($phoneNumber, 'gd_payment_reminder', null, $params);
        }

        return $result;
    }

    public function sendPromoterRegistrationSuccess($phoneNumber, $promoterName, $promoterId, $commissionRate)
    {
        $channels = $this->getChannels();
        $result = ['sms' => null, 'whatsapp' => null];

        $message = "Welcome to Golden Dream! Dear " . $promoterName . ", you have successfully joined as a promoter. " .
            "Your ID is: " . $promoterId . " and your commission rate is: " . $commissionRate . ".";

        if ($channels['sms']) {
            $result['sms'] = $this->smsAPI->sendSMS($phoneNumber, $message);
        }

        if ($channels['whatsapp']) {
            $params = [
                ['type' => 'text', 'parameter_name' => 'promoter_name', 'text' => (string)$promoterName],
                ['type' => 'text', 'parameter_name' => 'promoter_id', 'text' => (string)$promoterId],
                ['type' => 'text', 'parameter_name' => 'commission_rate', 'text' => (string)$commissionRate]
            ];
            $result['whatsapp'] = $this->whatsappAPI->sendTemplate($phoneNumber, 'gd_promoter_registration_success', null, $params);
        }

        return $result;
    }
}

