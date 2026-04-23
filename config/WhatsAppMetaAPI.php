<?php

class WhatsAppMetaAPI
{
    private $conn;
    private $config;
    private $logFile;

    public function __construct($database)
    {
        $this->conn = $database->getConnection();
        $this->logFile = __DIR__ . '/whatsapp_log.txt';
        $this->loadConfig();
    }

    private function writeLog($event, $phone = '', $detail = '')
    {
        $maskedPhone = $phone;
        if ($phone !== '') {
            $digits = preg_replace('/\D/', '', $phone);
            if (strlen($digits) > 6) {
                $maskedPhone = substr($digits, 0, 2) . str_repeat('*', strlen($digits) - 6) . substr($digits, -4);
            } else {
                $maskedPhone = '***';
            }
        }

        $line = date('Y-m-d H:i:s') . ' | EVENT=' . $event;
        if ($maskedPhone !== '') {
            $line .= ' | PHONE=' . $maskedPhone;
        }
        if ($detail !== '') {
            $clean = str_replace(["\r", "\n"], ' ', $detail);
            if (strlen($clean) > 400) {
                $clean = substr($clean, 0, 400) . '...';
            }
            $line .= ' | ' . $clean;
        }
        $line .= PHP_EOL;

        $written = @file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
        if ($written === false) {
            $fallbackDir = __DIR__ . '/../logs';
            if (!is_dir($fallbackDir)) {
                @mkdir($fallbackDir, 0755, true);
            }
            if (is_dir($fallbackDir)) {
                @file_put_contents($fallbackDir . '/whatsapp_log.txt', $line, FILE_APPEND | LOCK_EX);
            }
        }
    }

    private function loadConfig()
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM WhatsAppAPIConfig WHERE Status = 'Active' ORDER BY ConfigID DESC LIMIT 1");
            $stmt->execute();
            $this->config = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error loading WhatsApp Meta config: " . $e->getMessage());
            $this->config = null;
        }
    }

    public function isConfigured()
    {
        return $this->config
            && !empty($this->config['APIEndpoint'])
            && !empty($this->config['AccessToken'])
            && !empty($this->config['PhoneNumberID'])
            && ($this->config['Status'] === 'Active');
    }

    private function formatPhone($phoneNumber)
    {
        $phone = preg_replace('/\D/', '', (string)$phoneNumber);
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        } elseif (substr($phone, 0, 2) !== '91') {
            $phone = '91' . $phone;
        }
        return $phone;
    }

    private function sendRequest($payload)
    {
        if (!$this->isConfigured()) {
            $this->writeLog('config_missing', $payload['to'] ?? '', 'WhatsApp Meta API not configured');
            return ['success' => false, 'error' => 'WhatsApp Meta API not configured'];
        }

        $endpoint = rtrim($this->config['APIEndpoint'], '/');
        $url = $endpoint . '/' . $this->config['PhoneNumberID'] . '/messages';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->config['AccessToken'],
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->writeLog('curl_error', $payload['to'] ?? '', $curlError);
            return ['success' => false, 'error' => 'CURL Error: ' . $curlError];
        }

        $decoded = json_decode($response, true);
        $ok = $httpCode >= 200 && $httpCode < 300;

        $result = [
            'success' => $ok,
            'httpCode' => $httpCode,
            'response' => $decoded ?: $response,
            'error' => $ok ? null : ('HTTP ' . $httpCode)
        ];

        if ($ok) {
            $this->writeLog('sent', $payload['to'] ?? '', 'HTTP ' . $httpCode . ' TEMPLATE=' . ($payload['template']['name'] ?? 'unknown'));
        } else {
            $responseText = is_array($result['response']) ? json_encode($result['response']) : (string)$result['response'];
            $this->writeLog('failed', $payload['to'] ?? '', 'HTTP ' . $httpCode . ' RESPONSE=' . $responseText);
        }

        return $result;
    }

    public function sendTemplate($phoneNumber, $templateName = null, $languageCode = null, $parameters = [])
    {
        $phone = $this->formatPhone($phoneNumber);
        $template = $templateName ?: ($this->config['DefaultTemplateName'] ?? 'hello_world');
        $language = $languageCode ?: ($this->config['TemplateLanguageCode'] ?? 'en_US');

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => [
                    'code' => $language
                ]
            ]
        ];

        if (!empty($parameters)) {
            $payload['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => $parameters
                ]
            ];
        }

        $result = $this->sendRequest($payload);

        // Meta can return 132001 when the template exists but not for this translation
        // (for example: template created in "en", request sent as "en_US").
        // Retry once with base language code.
        $errorCode = $result['response']['error']['code'] ?? null;
        if (
            !$result['success'] &&
            (int)$errorCode === 132001 &&
            strpos($language, '_') !== false
        ) {
            $baseLanguage = strtolower(explode('_', $language)[0]);
            $payload['template']['language']['code'] = $baseLanguage;
            $this->writeLog('retry_language_fallback', $phone, 'Retrying template with language=' . $baseLanguage);
            $retryResult = $this->sendRequest($payload);
            if ($retryResult['success']) {
                return $retryResult;
            }
        }

        // Meta 132000 can happen when localizable params count does not match template expectation.
        // Example: sent 3 params, template expects 2.
        if (
            !$result['success'] &&
            (int)$errorCode === 132000 &&
            !empty($parameters) &&
            isset($result['response']['error']['error_data']['details'])
        ) {
            $details = (string)$result['response']['error']['error_data']['details'];
            if (preg_match('/expected number of params \((\d+)\)/', $details, $m)) {
                $expected = (int)$m[1];
                if ($expected >= 0 && $expected < count($parameters)) {
                    $payload['template']['components'][0]['parameters'] = array_slice($parameters, 0, $expected);
                    $this->writeLog('retry_param_count_fallback', $phone, 'Retrying with params=' . $expected);
                    $retryResult = $this->sendRequest($payload);
                    if ($retryResult['success']) {
                        return $retryResult;
                    }
                }
            }
        }

        return $result;
    }
}

