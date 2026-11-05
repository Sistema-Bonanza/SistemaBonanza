<?php

class Database{
    private $host = '127.0.0.1';          // para servidor Locar: 127.0.0.1   Para servidor digital: sql305.infinityfree.com
    private $db = 'sistema_bonanza';       // Nombre de la base de datos para servidor local: sistema_bonanza   Para servidor digital: if0_43331352_SistemaBonanza
    private $user = 'root';     //para servidor local: root   Para servidor digital: if0_43331352_SistemaBonanza
    private $pass = '';                    //para servidor local: ''   Para servidor digital: Handball1100                                          
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

