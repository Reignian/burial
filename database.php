<?php

class Database{
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $dbname = 'burial_db';

    protected $connection;

    function connect(){
        try {
            if($this->connection === null){
                $dsn = "mysql:host=$this->host;dbname=$this->dbname";
                $this->connection = new PDO($dsn, $this->username, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            }

            return $this->connection;
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
}