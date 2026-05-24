<?php 

class MovimentiMethods{
    
    private $db;

    public function __construct(){

    $this->db = @new MySQLi($_ENV['DB_HOST'],
                                $_ENV['DB_USER'],
                                $_ENV['DB_PASS'],
                                $_ENV['DB_NAME']);
        
    }

     public function getConnection() {
        return $this->db;
    }

    
}



?>