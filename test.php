<?php
class Database {
    private $host = "217.21.84.1";
    private $db_name = "u232955123_LAGD_DB";
    private $username = "u232955123_LAGD_USER";
    private $password = "4huM=!Z3D|j";
    public $conn;

    public function connect() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Database connected successfully";
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
}

$db = new Database();
$db->connect();
