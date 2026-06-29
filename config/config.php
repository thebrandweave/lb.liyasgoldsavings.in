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
            $this->db_name = "la-main";
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
          die("Database Error: " . $e->getMessage());
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
    public static $fromName = "Liyas Gold and Diamonds Careers";
}
