<?php
class Database
{
    private $host = "gateway01.ap-southeast-1.prod.aws.tidbcloud.com";
    private $port = "4000";
    private $db = "datadriven_db";

    // Use the credentials from your TiDB Cloud connection screen
    private $user = "3U5vQDsRypssWT5.root";
    private $pass = "8nGZXCEoKHuRZEJW";

    private $ca = "E:\PHP\UPDATED DATABASE\isrgrootx1.pem";

    private $conn;

    public function connect()
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_SSL_CA => $this->ca,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true
            ];

            $this->conn = new PDO(
                $dsn,
                $this->user,
                $this->pass,
                $options
            );

            // Force TiDB Cloud session timezone to match local time (+08:00)
            $this->conn->exec("SET time_zone = '+08:00'");

            return $this->conn;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            die("Database connection failed. Please check your TiDB configuration.");
        }
    }
}