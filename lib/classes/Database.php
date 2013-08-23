<?php

// Implements a wrapper for the PDO (Php Data(base) Object).
// Wrapped functions check the return values and on failure log them to the DatbaseError.log file.
// For information about functions, check the PDO manual: http://www.php.net/manual/en/class.pdo.php
class Database extends PDO {
	
	private $db;
  private $logfile;
	
	public function __construct() {
		
		try {
		    //$this->db = new PDO('mysql:dbname='.DB_NAME.';host='.DB_HOST, DB_USER, DB_PASS);
		    $this->db = parent::__construct('mysql:dbname='.DB_NAME.';host='.DB_HOST, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		} catch (PDOException $e) {
		    echo 'Connection failed: ' . $e->getMessage();
		}
    if(parent::errorCode() > 0)
      error_log("Error initializing database: ".print_r(parent::errorInfo(),true));
    
    $this->logfile = fopen(ROOT.'DatabaseError.log', 'a');
	}
  
  public function __destruct() {
  
    fclose($this->logfile);
  }
  
  function prepare($query) {
  
    $stmt = parent::prepare($query);
    if(!$stmt) {
      
      $error_array = parent::errorInfo();
      fwrite($this->logfile,
          "Error executing function prepare: ".$error_array[2]."\n".
          $query."\n\n"
        );
      fflush($this->logfile);
      return $stmt;
    }
    
    return new StatementWrapper($stmt, $this->logfile);
  }
  
  function query($query) {
  
    $stmt = parent::query($query);
    if(!$stmt) {
      
      $error_array = parent::errorInfo();
      fwrite($this->logfile,
          "Error executing function query: ".$error_array[2]."\n".
          $query."\n\n"
        );
      fflush($this->logfile);
      return $stmt;
    }
    
    return new StatementWrapper($stmt, $this->logfile);
  }
  
  function exec($query) {
  
    $rows = parent::exec($query);
    // exec() returns an int on success, (bool)false on failure, so it's important to check the type match with triple-equal.
    if($rows === false) {
      
      $error_array = parent::errorInfo();
      fwrite($this->logfile,
          "Error executing function query: ".$error_array[2]."\n".
          $query."\n\n"
        );
      fflush($this->logfile);
    }
    // Also check if the query was unsuccessful, issue a warning then
    else if($rows == false) {
      
      fwrite($this->logfile,
          "Warning: query altered 0 rows. Is the query working as intended?\n".
          $query."\n\n"
        );
      fflush($this->logfile);
    }
    
    return $rows;
  }
	
}

?>