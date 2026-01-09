<?php

class Database
{
    // Live database configuration
    private $host = "localhost";
    private $db_name = "u229215627_sp_goldenDream";
    private $username = "u229215627_sp_goldenDream";
    private $password = "GoldenDream@025";
    public $conn;

    // public $host = "localhost";
    // public $db_name = "u229215627_sp_goldenDream";
    // public $username = "root";
    // public $password = "";
    // public $conn;

    // Database configurations
    public static $main_db = "u229215627_goldenDreamSQL"; 
    public static $shop_db = "u229215627_sp_goldenDream"; 
    // public static $shop_db = "u229215627_sp_goldenDream";

    
    // Base URL configuration for live environment
    public static $baseUrl = "https://shop.goldendream.in/";

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Log the error for debugging
            error_log("Database connection failed: " . $e->getMessage());
            
            // Redirect to local error page
            header("Location: " . self::$baseUrl . "error.php");
            exit();
        }

        return $this->conn;
    }
}

// JWT secret key for token generation and verification
if (!defined('JWT_SECRET')) {
    define('JWT_SECRET', 'goldendream_super_secret_key_2024!@#');
}
// JWT encryption key (32 bytes for AES-256, derived from passphrase)
if (!defined('JWT_ENCRYPT_KEY')) {
    define('JWT_ENCRYPT_KEY', hash('sha256', 'goldendream_super_passphrase_2024', true)); 
}
// Helper functions for encrypting and decrypting JWTs
if (!function_exists('encrypt_jwt')) {
    function encrypt_jwt($jwt) {
        $iv = openssl_random_pseudo_bytes(16);
        $ciphertext = openssl_encrypt($jwt, 'AES-256-CBC', JWT_ENCRYPT_KEY, 0, $iv);
        return base64_encode($iv . $ciphertext);
    }
    function decrypt_jwt($encrypted) {
        $data = base64_decode($encrypted);
        $iv = substr($data, 0, 16);
        $ciphertext = substr($data, 16);
        return openssl_decrypt($ciphertext, 'AES-256-CBC', JWT_ENCRYPT_KEY, 0, $iv);
    }
}

?>
