<?php

class Database
{
   private $host = "145.79.209.58";
    // private $host = "localhost";
    private $db_name = "u232955123_LB_DB";
    private $username = "u232955123_LB_DB";
    private $password = "Brandweave@24";
    public $conn;


    // public $host = "localhost";
    // public $db_name = "la-main";
    // public $username = "root";
    // public $password = "";
    // public $conn;

    // Base URL configuration
    public static $baseUrl = "https://lb.liyasgoldsavings.in/";

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
            $baseUrl = "https://lb.liyasgoldsavings.in/";

            header("Location: " . $baseUrl . "noInternet/");
        }

        return $this->conn;
    }
}
