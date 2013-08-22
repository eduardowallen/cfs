<?php

// Looks for and includes requested <form> files.
class Form
{
  static function Load($formName, $variables = array())
  {
    if (file_exists(ROOT.'application/forms/'.$formName.'.php'))
    {
      extract($variables);
      ob_start(); // Capture output instead of outputting it
        include ROOT.'application/forms/'.$formName.'.php';
      return ob_get_clean(); // Return captured output as a variable
    }
    else
      return false;
  }
  
  // Executes Load($formName) and converts the string to be used as a JS variable
  static function LoadForJS($formName, $variables = array())
  {
    $output = self::Load($formName, $variables);
    
    $output = str_replace(array("'", "\'"), "\n", $output);
    $output = str_replace(array("\r\n", "\r"), "\n", $output);
    $lines = explode("\n", $output);
    $new_lines = array();

    foreach ($lines as $i => $line) {
        if(!empty($line))
            $new_lines[] = trim($line);
    }
    return implode($new_lines);
  }
}

?>
