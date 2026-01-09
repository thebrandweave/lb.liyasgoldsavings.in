<?php
/**
 * WhatsApp Queue Background Processor
 * This script processes queued WhatsApp messages in the background
 * Run this via cron job every minute: * * * * * php /path/to/admin/process_whatsapp_queue.php
 */

require_once("../config/config.php");

class WhatsAppQueueProcessor {
    private $conn;
    private $maxMessagesPerRun = 10; // Process max 10 messages per run
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function processQueue() {
        try {
            // Get pending messages
            $stmt = $this->conn->prepare("
                SELECT QueueID, PhoneNumber, Message, UserID, UserType, Attempts, MaxAttempts 
                FROM WhatsAppQueue 
                WHERE Status = 'Pending' 
                AND (LastAttemptAt IS NULL OR LastAttemptAt < DATE_SUB(NOW(), INTERVAL 5 MINUTE))
                AND Attempts < MaxAttempts
                ORDER BY CreatedAt ASC 
                LIMIT ?
            ");
            $stmt->execute([$this->maxMessagesPerRun]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($messages)) {
                error_log("No pending WhatsApp messages to process");
                return;
            }
            
            foreach ($messages as $message) {
                $this->processMessage($message);
            }
            
        } catch (Exception $e) {
            error_log("Error processing WhatsApp queue: " . $e->getMessage());
        }
    }
    
    private function processMessage($message) {
        try {
            // Update attempt count
            $stmt = $this->conn->prepare("
                UPDATE WhatsAppQueue 
                SET Attempts = Attempts + 1, LastAttemptAt = NOW() 
                WHERE QueueID = ?
            ");
            $stmt->execute([$message['QueueID']]);
            
            // Send WhatsApp message
            $success = $this->sendWhatsAppMessage($message['PhoneNumber'], $message['Message']);
            
            if ($success) {
                // Mark as sent
                $stmt = $this->conn->prepare("
                    UPDATE WhatsAppQueue 
                    SET Status = 'Sent', ProcessedAt = NOW() 
                    WHERE QueueID = ?
                ");
                $stmt->execute([$message['QueueID']]);
                
                error_log("WhatsApp message sent successfully to " . $message['PhoneNumber']);
            } else {
                // Check if max attempts reached
                if ($message['Attempts'] + 1 >= $message['MaxAttempts']) {
                    $stmt = $this->conn->prepare("
                        UPDATE WhatsAppQueue 
                        SET Status = 'Failed', ProcessedAt = NOW() 
                        WHERE QueueID = ?
                    ");
                    $stmt->execute([$message['QueueID']]);
                    
                    error_log("WhatsApp message failed after max attempts for " . $message['PhoneNumber']);
                }
            }
            
        } catch (Exception $e) {
            error_log("Error processing individual WhatsApp message: " . $e->getMessage());
        }
    }
    
    private function sendWhatsAppMessage($phoneNumber, $message) {
        try {
            // Get WhatsApp API configuration
            $stmt = $this->conn->prepare("SELECT APIEndpoint, InstanceID, AccessToken, Status FROM WhatsAppAPIConfig WHERE Status = 'Active' ORDER BY ConfigID DESC LIMIT 1");
            $stmt->execute();
            $whatsappConfig = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$whatsappConfig || $whatsappConfig['Status'] !== 'Active') {
                return false;
            }

            // Format phone number
            if (substr($phoneNumber, 0, 2) !== '91') {
                $phoneNumber = '91' . $phoneNumber;
            }

            // Prepare API URL
            $apiUrl = $whatsappConfig['APIEndpoint'] . 'send?number=' . $phoneNumber . '&type=text&message=' . urlencode($message) . '&instance_id=' . $whatsappConfig['InstanceID'] . '&access_token=' . $whatsappConfig['AccessToken'];

            // Send request with timeout
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 200) {
                $responseData = json_decode($response, true);
                return isset($responseData['status']) && $responseData['status'] === 'success';
            }

            return false;
        } catch (Exception $e) {
            error_log("Error sending WhatsApp message: " . $e->getMessage());
            return false;
        }
    }
}

// Run the processor
$processor = new WhatsAppQueueProcessor();
$processor->processQueue();
?>
