<?php

session_start();

define('ROOT', dirname(dirname(__FILE__)).'/');

require_once ROOT.'../config/config.php';
require_once ROOT.'../lib/functions.php';

header('Location: '.BASE_URL.'user/login');

?>