<?php

class Database
{
    private $host = "217.21.84.1";
    private $db_name = "u232955123_LAGD_DB";
    private $username = "u232955123_LAGD_USER";
    private $password = "4huM=!Z3D|j";
    public $conn;
    

    //public $host = "localhost";
    //public $db_name = "u229215627_goldenDreamSQL";
    //public $username = "root";
    //public $password = "";
    //public $conn;

    // Base URL configuration
    public static $baseUrl = "http://localhost/la.goldendream.in/";

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

?>
