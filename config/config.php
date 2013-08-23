<?php

//php settings
ini_set('max_execution_time', 60);
putenv('PATH=/bin:/usr/bin:/usr/local/bin');

//Dev mode
define('DEV', 'true');

//path settings
define('BASE_URL', 'http://chartbookertest/');

//Database settings
define('DB_HOST', 'localhost');
define('DB_USER', 'chartbooker');
define('DB_PASS', 'LeifWall');
define('DB_NAME', 'chartbooker');

//E-mail settings
define('SMTP_SERVER', 'localhost');
define('SMTP_PORT', 25);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('EMAIL_FROM_NAME', 'Chartbooker');
if (DEV) {
	define('EMAIL_FROM_ADDRESS', 'test.server@chartbooker.com');
} else {
	define('EMAIL_FROM_ADDRESS', 'info@chartbooker.com');
}

?>
