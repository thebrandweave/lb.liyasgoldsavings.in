<?php

class Database
{
    private $host = "localhost";
    private $db_name = "u232955123_goldenDreamDB";
    private $username = "u232955123_goldenDreamDB";
    private $password = "Brandweave@25";
    public $conn;

    // Base URL configuration
    public static $baseUrl = "https://goldendream.in/";

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
            $baseUrl = "https://goldendream.in/";

            header("Location: " . $baseUrl . "noInternet/");
        }

        return $this->conn;
    }
}