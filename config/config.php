<?php

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    // Base URL configuration
    public static $baseUrl = "https://lb.liyasgoldsavings.in/";

    public function getConnection()
    {
        $this->conn = null;

        // Automatically detect if running locally (localhost / Windows)
        $isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || 
                       (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] === '127.0.0.1') ||
                       strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($isLocalhost) {
            $this->host = "localhost";
            $this->db_name = "liyaslb"; 
            $this->username = "root";
            $this->password = "";
        } else {
            $this->host = "localhost";
            $this->db_name = "u232955123_LB_DB";
            $this->username = "u232955123_LB_DB";
            $this->password = "Brandweave@24";
        }

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            $this->conn = null;
        }
    
        return $this->conn;
    }
}


class EmailConfig {
    public static $smtpHost = "smtp.hostinger.com";
    public static $smtpPort = 465;
    public static $smtpUser = "info@liyasgoldsavings.in"; // Set Hostinger SMTP username
    public static $smtpPass = "vwd4-qpbx-ibti-mkph";                         // Set Hostinger SMTP password
    public static $fromEmail = "info@liyasgoldsavings.in";
    public static $fromName = "Liyas Gold Savings Careers";
}



// Global Date & Time Formatter with IST (+5:30) Offset
if (!function_exists('formatDateTime')) {
    function formatDateTime($datetime, $format = 'M d, Y h:i A')
    {
        if (empty($datetime)) {
            return '-';
        }
        try {
            $date = new DateTime($datetime);
            $date->modify('+5 hours +30 minutes');
            return $date->format($format);
        } catch (Exception $e) {
            return $datetime;
        }
    }
}

if (!function_exists('formatTime')) {
    function formatTime($datetime, $format = 'h:i A')
    {
        if (empty($datetime)) {
            return '-';
        }
        try {
            $date = new DateTime($datetime);
            $date->modify('+5 hours +30 minutes');
            return $date->format($format);
        } catch (Exception $e) {
            return $datetime;
        }
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'M d, Y')
    {
        if (empty($date)) {
            return '-';
        }
        try {
            $datetime = new DateTime($date);
            $datetime->modify('+5 hours +30 minutes');
            return $datetime->format($format);
        } catch (Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($datetime)
    {
        if (empty($datetime)) {
            return '-';
        }
        try {
            $now = new DateTime();
            $ago = new DateTime($datetime);
            $ago->modify('+5 hours +30 minutes');
            $diff = $now->diff($ago);

            if ($diff->y > 0) {
                return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
            } elseif ($diff->m > 0) {
                return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
            } elseif ($diff->d > 0) {
                if ($diff->d == 1) {
                    return 'Yesterday';
                }
                return $diff->d . ' days ago';
            } elseif ($diff->h > 0) {
                return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
            } elseif ($diff->i > 0) {
                return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
            } else {
                return 'Just now';
            }
        } catch (Exception $e) {
            return $datetime;
        }
    }
}

?>
