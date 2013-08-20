<?php
if( isset($_POST['query']) ){
	
    $parts = explode('/', dirname(dirname(__FILE__)));
    $parts = array_slice($parts, 0, -1);
    
    define('ROOT', implode('/', $parts).'/');
    
    require_once '../../lib/classes/Translator.php';
    
    $lang = (isset($_COOKIE['language'])) ? $_COOKIE['language'] : 'eng';
    $tran = new Translator($lang);
    
    echo $tran->__get($_POST['query']);
    
}