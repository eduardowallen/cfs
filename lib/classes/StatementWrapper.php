<?php

// StatementWrapper is a wrapper for the PDOStatement object to allow checking the return values of functions.
//  In case of failure, the function call is logged to the logfile.
// This class is intended to only be used by Database class.
class StatementWrapper {
  public $stmt;
  private $logfile;
  
  function __construct($stmt, $logfile) {
  
    $this->stmt = $stmt;
    $this->logfile = $logfile;
  }
  
  // A function which catches all function calls and passes them through to the underlying PDOStatement object $this->stmt.
  // If a result returns false (assuming here that it indicates an error), information about it is written to the $this->logfile.
  function __call($function, $arguments) {
      
    $result = call_user_func_array(array($this->stmt, $function), $arguments);
    // Some functions might return 0 even on a successful call, therefore it is important to also check the type match with triple-equal.
    if($result === false) {
    
      $error_array = $this->stmt->errorInfo();
      fwrite($this->logfile,
          "Error executing function PDOStatement::".$function.": ".$error_array[2]."\n".
          "Query: ".$this->stmt->queryString."\n".
          print_r($arguments, true)."\n".
          "Backtrace: ".print_r(debug_backtrace(), true)."\n\n"
        );
      fflush($this->logfile);
    }
    return $result;
  }
}

?>