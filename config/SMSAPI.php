<?php

class SMSAPI
{
    private $conn;
    private $config;

    public function __construct($database)
    {
        $this->conn = $database->getConnection();
        $this->loadConfig();
    }

    private function loadConfig()
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM SMSAPIConfig WHERE Status = 'Active' ORDER BY ConfigID DESC LIMIT 1");
            $stmt->execute();
            $this->config = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error loading SMS config: " . $e->getMessage());
            $this->config = null;
        }
    }

    public function isConfigured()
    {
        return $this->config && $this->config['Status'] === 'Active';
    }

    public function sendSMS($phoneNumber, $message, $customerName = null, $amount = null, $returnDetails = false)
    {
        if (!$this->isConfigured()) {
            $error = "SMS API is not configured or inactive";
            error_log($error);
            return $returnDetails ? ['success' => false, 'error' => $error] : false;
        }

        try {
            // Format phone number (add country code if not present)
            if (substr($phoneNumber, 0, 2) !== '91') {
                $phoneNumber = '91' . $phoneNumber;
            }

            // Always prefer configured DLT template for payment messages when variables provided
            if ($customerName && $amount && !empty($this->config['MessageTemplate'])) {
                $message = $this->formatTemplateMessage($customerName, $amount);
            }

            // Prepare API request data
            $apiData = [
                'customerId' => $this->config['CustomerID'],
                'destinationAddress' => [$phoneNumber],
                'dltTemplateId' => $this->config['DLTTemplateID'],
                'entityId' => $this->config['DLTEntityID'],
                'message' => $message,
                'messageType' => $this->config['MessageType'],
                'sourceAddress' => $this->config['SourceAddress']
            ];

            // Send request to Airtel SMS API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->config['APIEndpoint']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apiData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'accept: application/json',
                'content-type: application/json'
            ]);
            // Use Basic Authentication
            curl_setopt($ch, CURLOPT_USERPWD, $this->config['Username'] . ':' . $this->config['Password']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Handle null/empty response
            if ($response === false) {
                $response = '';
            }

            // Log the response
            error_log("SMS API Response: " . ($response ?: 'empty or null'));
            error_log("SMS API HTTP Code: " . $httpCode);
            if ($curlError) {
                error_log("SMS API CURL Error: " . $curlError);
            }

            if ($curlError) {
                $error = "CURL Error: " . $curlError;
                error_log("SMS API " . $error);
                return $returnDetails ? ['success' => false, 'error' => $error, 'httpCode' => $httpCode, 'response' => $response] : false;
            }

            // Check if request was successful
            if ($httpCode == 200 || $httpCode == 201) {
                $responseData = json_decode($response, true);

                // Airtel API success indicators: messageRequestId exists (success) or status field
                $isSuccess = false;
                $hasMessageRequestId = isset($responseData['messageRequestId']) && !empty($responseData['messageRequestId']);

                if ($hasMessageRequestId) {
                    // Airtel API format: messageRequestId indicates request was processed
                    // Check if there are any incorrect numbers
                    $incorrectNumbers = isset($responseData['incorrectNum']) && is_array($responseData['incorrectNum']) ? $responseData['incorrectNum'] : [];
                    if (empty($incorrectNumbers)) {
                        // All numbers were valid - success
                        $isSuccess = true;
                    } else {
                        // Some numbers were invalid - partial failure
                        $isSuccess = false;
                    }
                } elseif (isset($responseData['status']) && $responseData['status'] === 'success') {
                    // Generic API format: status field indicates success
                    $isSuccess = true;
                }

                if ($isSuccess) {
                    $messageRequestId = $responseData['messageRequestId'] ?? 'N/A';
                    $destinationCount = isset($responseData['destinationAddress']) && is_array($responseData['destinationAddress']) ? count($responseData['destinationAddress']) : 0;
                    $incorrectCount = isset($responseData['incorrectNum']) && is_array($responseData['incorrectNum']) ? count($responseData['incorrectNum']) : 0;

                    $successMsg = "SMS sent successfully (Request ID: {$messageRequestId})";
                    if ($destinationCount > 0) {
                        $successMsg .= " to {$destinationCount} recipient(s)";
                    }
                    if ($incorrectCount > 0) {
                        $successMsg .= " (Note: {$incorrectCount} invalid number(s) found)";
                    }
                    return $returnDetails ? ['success' => true, 'message' => $successMsg, 'response' => $responseData, 'messageRequestId' => $messageRequestId] : true;
                } else {
                    // Request processed but not successful (invalid numbers or other issues)
                    $errorMsg = 'Unknown error';

                    // Check for invalid phone numbers first (most common issue)
                    if (isset($responseData['incorrectNum']) && is_array($responseData['incorrectNum']) && !empty($responseData['incorrectNum'])) {
                        $errorMsg = 'Invalid phone number(s): ' . implode(', ', $responseData['incorrectNum']);
                    } elseif (isset($responseData['message']) && is_string($responseData['message'])) {
                        // Check if message is an actual error message (not the SMS content)
                        $testMessages = ['test message from golden dream', 'test message'];
                        $messageLower = strtolower(trim($responseData['message']));
                        if (!in_array($messageLower, $testMessages)) {
                            $errorMsg = $responseData['message'];
                        }
                    }

                    if ($errorMsg === 'Unknown error') {
                        if (isset($responseData['error'])) {
                            $errorMsg = is_string($responseData['error']) ? $responseData['error'] : json_encode($responseData['error']);
                        } elseif (isset($responseData['errorMessage'])) {
                            $errorMsg = is_string($responseData['errorMessage']) ? $responseData['errorMessage'] : json_encode($responseData['errorMessage']);
                        } elseif ($hasMessageRequestId) {
                            // Has messageRequestId but also has issues - partial success scenario
                            $errorMsg = 'Request processed but some numbers may be invalid. Check incorrectNum field in response.';
                        } elseif (!empty($response)) {
                            $errorMsg = 'Invalid response format. Expected messageRequestId or status field.';
                        }
                    }

                    $error = "API returned error: " . $errorMsg;
                    error_log("SMS API " . $error);
                    error_log("SMS API Full Response: " . $response);
                    return $returnDetails ? ['success' => false, 'error' => $error, 'httpCode' => $httpCode, 'response' => $responseData ?: $response] : false;
                }
            } else {
                // HTTP error - format response for better error message
                $errorDetails = 'No response received';
                if (!empty($response)) {
                    $responseData = json_decode($response, true);
                    if (is_array($responseData)) {
                        $errorDetails = isset($responseData['message']) ? $responseData['message'] : (isset($responseData['error']) ? $responseData['error'] :
                            json_encode($responseData));
                    } else {
                        $errorDetails = substr($response, 0, 300);
                    }
                }
                $error = "HTTP Error " . $httpCode . ": " . $errorDetails;
                error_log("SMS API " . $error);
                error_log("SMS API Full Response: " . ($response ?: 'empty'));
                return $returnDetails ? ['success' => false, 'error' => $error, 'httpCode' => $httpCode, 'response' => $response] : false;
            }
        } catch (Exception $e) {
            $error = "Exception: " . $e->getMessage();
            error_log("Error sending SMS: " . $error);
            return $returnDetails ? ['success' => false, 'error' => $error] : false;
        }
    }

    public function sendBulkSMS($phoneNumbers, $message, $customerNames = null, $amounts = null)
    {
        if (!$this->isConfigured()) {
            error_log("SMS API is not configured or inactive");
            return false;
        }

        try {
            // Format phone numbers (add country code if not present)
            $formattedNumbers = [];
            foreach ($phoneNumbers as $phone) {
                if (substr($phone, 0, 2) !== '91') {
                    $phone = '91' . $phone;
                }
                $formattedNumbers[] = $phone;
            }

            // For bulk SMS, we'll send individual messages with templates
            $successCount = 0;
            $totalCount = count($phoneNumbers);

            for ($i = 0; $i < $totalCount; $i++) {
                $phone = $phoneNumbers[$i];
                $customerName = $customerNames ? ($customerNames[$i] ?? null) : null;
                $amount = $amounts ? ($amounts[$i] ?? null) : null;

                if ($this->sendSMS($phone, $message, $customerName, $amount)) {
                    $successCount++;
                }

                // Add small delay to avoid rate limiting
                usleep(100000); // 0.1 second delay
            }

            return $successCount === $totalCount;
        } catch (Exception $e) {
            error_log("Error sending bulk SMS: " . $e->getMessage());
            return false;
        }
    }

    private function formatTemplateMessage($customerName, $amount)
    {
        $template = $this->config['MessageTemplate'];

        // Replace variables in the template
        $message = str_replace('{var1}', $customerName, $template);
        $message = str_replace('{var2}', $amount, $message);

        return $message;
    }

    /**
     * Send welcome SMS to new customer using welcome template
     * @param string $phoneNumber Customer phone number
     * @param string $customerName Customer name
     * @param string $customerUniqueID Customer unique ID
     * @return bool Success status
     */
    public function sendWelcomeSMS($phoneNumber, $customerName, $customerUniqueID)
    {
        if (!$this->isConfigured()) {
            error_log("SMS API is not configured or inactive");
            return false;
        }

        try {
            // Format phone number (add country code if not present)
            if (substr($phoneNumber, 0, 2) !== '91') {
                $phoneNumber = '91' . $phoneNumber;
            }

            // Welcome template details from screenshot
            // Template ID: 1007289085098641045
            // Template: "Dear var, welcome to PROGEEDEE Ventures Private Limited. You have successfully registered for the Golden Dream Savings Plan. Your Customer ID is var. Visit https://la.goldendream.in/ for more details."
            // Source Address: PGDVTR
            // Variables: var1 = Customer Name, var2 = Customer Unique ID

            $welcomeTemplate = "Dear {var1}, welcome to PROGEEDEE Ventures Private Limited. You have successfully registered for the Golden Dream Savings Plan. Your Customer ID is {var2}. Visit https://la.goldendream.in/ for more details.";

            // Format the welcome message
            $message = str_replace('{var1}', $customerName, $welcomeTemplate);
            $message = str_replace('{var2}', $customerUniqueID, $message);

            // Prepare API request data with welcome template ID
            $apiData = [
                'customerId' => $this->config['CustomerID'],
                'destinationAddress' => [$phoneNumber],
                'dltTemplateId' => '1007289085098641045', // Welcome template ID
                'entityId' => $this->config['DLTEntityID'],
                'message' => $message,
                'messageType' => 'SERVICE_EXPLICIT',
                'sourceAddress' => 'PGDVTR' // Welcome template source address
            ];

            // Send request to Airtel SMS API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->config['APIEndpoint']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apiData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'accept: application/json',
                'content-type: application/json'
            ]);
            // Use Basic Authentication
            curl_setopt($ch, CURLOPT_USERPWD, $this->config['Username'] . ':' . $this->config['Password']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Log the response
            error_log("Welcome SMS API Response: " . $response);
            error_log("Welcome SMS API HTTP Code: " . $httpCode);

            if ($curlError) {
                error_log("Welcome SMS API CURL Error: " . $curlError);
                return false;
            }

            // Check if request was successful
            if ($httpCode == 200) {
                $responseData = json_decode($response, true);
                if (isset($responseData['status']) && $responseData['status'] === 'success') {
                    return true;
                } else {
                    error_log("Welcome SMS API returned error: " . ($responseData['message'] ?? 'Unknown error'));
                    return false;
                }
            } else {
                error_log("Welcome SMS API HTTP Error: " . $httpCode . " - " . $response);
                return false;
            }
        } catch (Exception $e) {
            error_log("Error sending welcome SMS: " . $e->getMessage());
            return false;
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function testConnection()
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'SMS API is not configured'];
        }

        try {
            // Test with a simple message
            $testMessage = "Test message from Golden Dream SMS API";
            return $this->sendSMS('8197458962', $testMessage);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
