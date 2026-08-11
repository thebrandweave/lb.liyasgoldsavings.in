<?php
/**
 * WhatsApp Cloud API Webhook Handler
 * 
 * Receives incoming customer replies (text, image, document, audio, video, sticker, etc.)
 * from Meta Cloud API and stores them in the database + local uploads/whatsapp directory.
 */

// Buffer all output to ensure zero extraneous characters break Meta Webhook verification
ob_start();

// Disable direct error display for webhook reliability
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . "/../config/config.php";


class WhatsAppWebhookHandler
{
    private $conn;
    private $config;
    private $logFile;
    private $uploadDir;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->logFile = __DIR__ . '/../config/whatsapp_log.txt';
        $this->uploadDir = __DIR__ . '/../uploads/whatsapp/';

        if (!is_dir($this->uploadDir)) {
            @mkdir($this->uploadDir, 0755, true);
        }

        $this->ensureTablesExist();
        $this->loadConfig();
    }

    private function log($event, $detail = '')
    {
        $line = date('Y-m-d H:i:s') . ' | WEBHOOK=' . $event;
        if ($detail !== '') {
            $clean = str_replace(["\r", "\n"], ' ', $detail);
            if (strlen($clean) > 500) {
                $clean = substr($clean, 0, 500) . '...';
            }
            $line .= ' | ' . $clean;
        }
        $line .= PHP_EOL;
        @file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }

    private function ensureTablesExist()
    {
        if (!$this->conn) return;

        try {
            // Create WhatsAppReplies table if missing
            $sql = "CREATE TABLE IF NOT EXISTS WhatsAppReplies (
                ReplyID INT AUTO_INCREMENT PRIMARY KEY,
                MessageID VARCHAR(255) UNIQUE,
                SenderPhone VARCHAR(50) NOT NULL,
                SenderName VARCHAR(150) DEFAULT NULL,
                MessageType VARCHAR(50) NOT NULL DEFAULT 'text',
                MessageBody TEXT DEFAULT NULL,
                MediaID VARCHAR(255) DEFAULT NULL,
                MediaMimeType VARCHAR(100) DEFAULT NULL,
                LocalFilePath VARCHAR(255) DEFAULT NULL,
                RawPayload LONGTEXT DEFAULT NULL,
                Status VARCHAR(50) DEFAULT 'Unread',
                ReceivedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_sender_phone (SenderPhone),
                INDEX idx_message_type (MessageType),
                INDEX idx_received_at (ReceivedAt)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $this->conn->exec($sql);

            // Add VerifyToken column to WhatsAppAPIConfig if missing
            $stmt = $this->conn->query("SHOW COLUMNS FROM WhatsAppAPIConfig LIKE 'VerifyToken'");
            if (!$stmt->fetch()) {
                $this->conn->exec("ALTER TABLE WhatsAppAPIConfig ADD COLUMN VerifyToken VARCHAR(255) DEFAULT 'liyas_whatsapp_verify_token_2026'");
            }
        } catch (Exception $e) {
            error_log("WhatsApp Webhook table check error: " . $e->getMessage());
        }
    }

    private function loadConfig()
    {
        if (!$this->conn) return;
        try {
            $stmt = $this->conn->query("SELECT * FROM WhatsAppAPIConfig WHERE Status = 'Active' ORDER BY ConfigID DESC LIMIT 1");
            $this->config = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->config = null;
        }
    }

    public function handleVerification()
    {
        $mode = $_GET['hub_mode'] ?? $_GET['hub.mode'] ?? $_REQUEST['hub_mode'] ?? $_REQUEST['hub.mode'] ?? '';
        $token = $_GET['hub_verify_token'] ?? $_GET['hub.verify_token'] ?? $_REQUEST['hub_verify_token'] ?? $_REQUEST['hub.verify_token'] ?? '';
        $challenge = $_GET['hub_challenge'] ?? $_GET['hub.challenge'] ?? $_REQUEST['hub_challenge'] ?? $_REQUEST['hub.challenge'] ?? '';

        $expectedToken = $this->config['VerifyToken'] ?? 'liyas_whatsapp_verify_token_2026';
        if (empty($expectedToken)) {
            $expectedToken = 'liyas_whatsapp_verify_token_2026';
        }

        // Clean any output buffer before returning response to Meta
        if (ob_get_length()) {
            ob_clean();
        }

        if ($mode === 'subscribe' && ($token === $expectedToken || $token === 'liyas_whatsapp_verify_token_2026')) {
            $this->log('VERIFICATION_SUCCESS', 'Challenge=' . $challenge);
            header('Content-Type: text/plain; charset=utf-8');
            http_response_code(200);
            echo $challenge;
            exit();
        } else {
            $this->log('VERIFICATION_FAILED', 'ReceivedToken=' . $token . ' ExpectedToken=' . $expectedToken);
            header('Content-Type: text/plain; charset=utf-8');
            http_response_code(403);
            echo "Verification token mismatch";
            exit();
        }
    }


    public function handleIncomingMessage()
    {
        $rawPayload = file_get_contents('php://input');
        if (empty($rawPayload)) {
            http_response_code(200);
            echo "EMPTY_PAYLOAD";
            exit();
        }

        $data = json_decode($rawPayload, true);
        if (!$data || !isset($data['entry'])) {
            http_response_code(200);
            echo "NO_ENTRY";
            exit();
        }

        foreach ($data['entry'] as $entry) {
            $changes = $entry['changes'] ?? [];
            foreach ($changes as $change) {
                $val = $change['value'] ?? [];
                if (!isset($val['messages']) || empty($val['messages'])) {
                    continue;
                }

                $contacts = $val['contacts'] ?? [];
                $senderName = $contacts[0]['profile']['name'] ?? '';

                foreach ($val['messages'] as $msg) {
                    $this->processSingleMessage($msg, $senderName, $rawPayload);
                }
            }
        }

        http_response_code(200);
        echo "EVENT_RECEIVED";
        exit();
    }

    private function processSingleMessage($msg, $senderName, $rawPayload)
    {
        $msgId = $msg['id'] ?? ('msg_' . time() . '_' . rand(1000, 9999));
        $senderPhone = preg_replace('/\D/', '', (string)($msg['from'] ?? ''));
        $msgType = $msg['type'] ?? 'text';
        $timestamp = isset($msg['timestamp']) ? date('Y-m-d H:i:s', (int)$msg['timestamp']) : date('Y-m-d H:i:s');

        $messageBody = '';
        $mediaId = null;
        $mimeType = null;
        $localFilePath = null;

        switch ($msgType) {
            case 'text':
                $messageBody = $msg['text']['body'] ?? '';
                break;

            case 'image':
                $mediaId = $msg['image']['id'] ?? null;
                $mimeType = $msg['image']['mime_type'] ?? 'image/jpeg';
                $messageBody = $msg['image']['caption'] ?? '[Image Received]';
                break;

            case 'document':
                $mediaId = $msg['document']['id'] ?? null;
                $mimeType = $msg['document']['mime_type'] ?? 'application/pdf';
                $filename = $msg['document']['filename'] ?? 'Document';
                $caption = $msg['document']['caption'] ?? '';
                $messageBody = $filename . ($caption ? (' - ' . $caption) : '');
                break;

            case 'audio':
                $mediaId = $msg['audio']['id'] ?? null;
                $mimeType = $msg['audio']['mime_type'] ?? 'audio/ogg';
                $messageBody = '[Voice Note / Audio]';
                break;

            case 'video':
                $mediaId = $msg['video']['id'] ?? null;
                $mimeType = $msg['video']['mime_type'] ?? 'video/mp4';
                $messageBody = $msg['video']['caption'] ?? '[Video Received]';
                break;

            case 'sticker':
                $mediaId = $msg['sticker']['id'] ?? null;
                $mimeType = $msg['sticker']['mime_type'] ?? 'image/webp';
                $messageBody = '[Sticker Received]';
                break;

            case 'button':
                $messageBody = $msg['button']['text'] ?? '[Button Reply]';
                break;

            case 'interactive':
                $messageBody = $msg['interactive']['button_reply']['title'] ?? $msg['interactive']['list_reply']['title'] ?? '[Interactive Response]';
                break;

            case 'location':
                $lat = $msg['location']['latitude'] ?? '';
                $lng = $msg['location']['longitude'] ?? '';
                $name = $msg['location']['name'] ?? '';
                $messageBody = 'Location: ' . ($name ? ($name . ' ') : '') . '(' . $lat . ', ' . $lng . ')';
                break;

            default:
                $messageBody = '[' . ucfirst($msgType) . ' Received]';
                break;
        }

        // Download media if mediaId exists and Meta Access Token is configured
        if ($mediaId && !empty($this->config['AccessToken'])) {
            $localFilePath = $this->downloadMedia($mediaId, $mimeType, $msgType);
        }

        try {
            $stmt = $this->conn->prepare("
                INSERT INTO WhatsAppReplies 
                (MessageID, SenderPhone, SenderName, MessageType, MessageBody, MediaID, MediaMimeType, LocalFilePath, RawPayload, Status, ReceivedAt)
                VALUES 
                (:msgId, :senderPhone, :senderName, :msgType, :messageBody, :mediaId, :mimeType, :localFilePath, :rawPayload, 'Unread', :receivedAt)
                ON DUPLICATE KEY UPDATE 
                    MessageBody = VALUES(MessageBody),
                    LocalFilePath = COALESCE(VALUES(LocalFilePath), LocalFilePath),
                    Status = 'Unread';
            ");

            $stmt->execute([
                ':msgId' => $msgId,
                ':senderPhone' => $senderPhone,
                ':senderName' => $senderName,
                ':msgType' => $msgType,
                ':messageBody' => $messageBody,
                ':mediaId' => $mediaId,
                ':mimeType' => $mimeType,
                ':localFilePath' => $localFilePath,
                ':rawPayload' => $rawPayload,
                ':receivedAt' => $timestamp
            ]);

            $this->log('MESSAGE_STORED', "From={$senderPhone} Type={$msgType} File={$localFilePath}");
        } catch (Exception $e) {
            $this->log('STORAGE_ERROR', $e->getMessage());
        }
    }

    private function downloadMedia($mediaId, $mimeType, $msgType)
    {
        $accessToken = $this->config['AccessToken'] ?? '';
        $endpoint = rtrim($this->config['APIEndpoint'] ?? 'https://graph.facebook.com/v25.0', '/');
        $mediaInfoUrl = $endpoint . '/' . $mediaId;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $mediaInfoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $res = curl_exec($ch);
        curl_close($ch);

        if (!$res) return null;

        $info = json_decode($res, true);
        $downloadUrl = $info['url'] ?? null;
        if (!$downloadUrl) return null;

        // Determine file extension
        $ext = 'bin';
        if (strpos($mimeType, 'image/jpeg') !== false || strpos($mimeType, 'jpg') !== false) $ext = 'jpg';
        elseif (strpos($mimeType, 'image/png') !== false) $ext = 'png';
        elseif (strpos($mimeType, 'image/webp') !== false) $ext = 'webp';
        elseif (strpos($mimeType, 'pdf') !== false) $ext = 'pdf';
        elseif (strpos($mimeType, 'audio/ogg') !== false || strpos($mimeType, 'opus') !== false) $ext = 'ogg';
        elseif (strpos($mimeType, 'audio/mpeg') !== false || strpos($mimeType, 'mp3') !== false) $ext = 'mp3';
        elseif (strpos($mimeType, 'video/mp4') !== false) $ext = 'mp4';

        $filename = 'wa_' . $msgType . '_' . time() . '_' . substr(md5($mediaId), 0, 8) . '.' . $ext;
        $savePath = $this->uploadDir . $filename;

        // Download binary file contents from Meta CDN URL
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $downloadUrl);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
        ]);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 60);
        $fileBytes = curl_exec($ch2);
        $httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);

        if ($httpCode === 200 && $fileBytes) {
            if (file_put_contents($savePath, $fileBytes)) {
                return 'uploads/whatsapp/' . $filename;
            }
        }

        return null;
    }
}

// Router for Webhook execution
$handler = new WhatsAppWebhookHandler();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $handler->handleVerification();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $handler->handleIncomingMessage();
} else {
    http_response_code(405);
    echo "Method Not Allowed";
}
