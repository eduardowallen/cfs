<?php

class Database extends PDO {

	public function __construct() {
		try {
		    parent::__construct('mysql:dbname='.DB_NAME.';host='.DB_HOST, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		} catch (PDOException $e) {
		    echo 'Connection failed: ' . $e->getMessage();
		}
	}
}

?>
