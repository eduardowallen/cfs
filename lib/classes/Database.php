<?php

class Database extends PDO {
	
	private $db;
	
	public function __construct() {
		
		try {
		    //$this->db = new PDO('mysql:dbname='.DB_NAME.';host='.DB_HOST, DB_USER, DB_PASS);
		    $this->db = parent::__construct('mysql:dbname='.DB_NAME.';host='.DB_HOST, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		} catch (PDOException $e) {
		    echo 'Connection failed: ' . $e->getMessage();
		}
		
	}	
	
}

?>