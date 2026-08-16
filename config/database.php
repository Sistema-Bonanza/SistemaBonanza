<?php

class Database{
    private $host = '127.0.0.1';
    private $db = 'sistema_bonanza';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';
    private $pdo;


    public function __construct(){
       $this->connect();
    }

    public function connect(){
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try{
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (\PDOException $e){
            throw new \PDOException("Error de conexion: " . $e->getMessage(),(int)$e->getCode());

            
        }
    }
    
    public function getConnection(){
        return $this->pdo;
    }
}

