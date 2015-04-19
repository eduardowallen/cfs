<?php	

define('ROOT', dirname(dirname(__FILE__)).'/');

$url = (isset($_GET['url']) && !empty($_GET['url'])) ? $_GET['url'] : '';

require_once ROOT.'lib/bootstrap.php';

?>