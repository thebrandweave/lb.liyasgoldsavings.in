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

    public function sendPaymentVerified($phoneNumber, $customerName, $amount)
    {
        $channels = $this->getChannels();
        $result = ['sms' => null, 'whatsapp' => null];

        if ($channels['sms']) {
            $result['sms'] = sendPaymentVerifiedSMSHardcoded($phoneNumber, $customerName, $amount);
        }

        if ($channels['whatsapp']) {
            $params = [
                ['type' => 'text', 'parameter_name' => 'customer_name', 'text' => (string)$customerName],
                ['type' => 'text', 'parameter_name' => 'payment_amount', 'text' => number_format((float)$amount, 0, '', '')]
            ];
            $result['whatsapp'] = $this->whatsappAPI->sendTemplate($phoneNumber, 'gd_payment_received', null, $params);
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
}

