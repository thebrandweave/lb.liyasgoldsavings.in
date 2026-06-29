<?php
require_once __DIR__ . '/config.php';

function sendSMTPMail($to, $subject, $bodyHTML) {
    $host = EmailConfig::$smtpHost;
    $port = EmailConfig::$smtpPort;
    $username = EmailConfig::$smtpUser;
    $password = EmailConfig::$smtpPass;
    $fromEmail = EmailConfig::$fromEmail;
    $fromName = EmailConfig::$fromName;

    // Check if configuration is set
    if (empty($username) || empty($password)) {
        // Fallback or skip if not configured
        error_log("SMTP credentials are not configured in config/config.php. Skipping email send.");
        return false;
    }

    $timeout = 15;
    $socket = @stream_socket_client("ssl://$host:$port", $errno, $errstr, $timeout);
    if (!$socket) {
        throw new Exception("SMTP Connection Failed: $errstr ($errno)");
    }

    // Helper to read SMTP response
    $readResponse = function($socket, $expectedCode) {
        $response = "";
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === " ") {
                break;
            }
        }
        $code = (int)substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new Exception("SMTP Expected code $expectedCode, got: $response");
        }
        return $response;
    };

    try {
        $readResponse($socket, 220);

        $clientHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        fwrite($socket, "EHLO " . $clientHost . "\r\n");
        $readResponse($socket, 250);

        fwrite($socket, "AUTH LOGIN\r\n");
        $readResponse($socket, 334);

        fwrite($socket, base64_encode($username) . "\r\n");
        $readResponse($socket, 334);

        fwrite($socket, base64_encode($password) . "\r\n");
        $readResponse($socket, 235);

        fwrite($socket, "MAIL FROM: <$fromEmail>\r\n");
        $readResponse($socket, 250);

        fwrite($socket, "RCPT TO: <$to>\r\n");
        $readResponse($socket, 250);

        fwrite($socket, "DATA\r\n");
        $readResponse($socket, 354);

        // Build Email Headers
        $headers = [
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$fromEmail>",
            "To: <$to>",
            "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
            "Date: " . date('r'),
            "Message-ID: <" . time() . '-' . uniqid() . '@' . $clientHost . ">"
        ];

        $emailData = implode("\r\n", $headers) . "\r\n\r\n" . $bodyHTML . "\r\n.\r\n";
        
        fwrite($socket, $emailData);
        $readResponse($socket, 250);

        fwrite($socket, "QUIT\r\n");
        fclose($socket);
        return true;
    } catch (Exception $e) {
        if ($socket) {
            fclose($socket);
        }
        throw $e;
    }
}
?>
