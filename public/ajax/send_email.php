<?php
/**
 * Created by PhpStorm.
 * User: trinaxrobin
 * Date: 2019-02-26
 * Time: 18:55

require_once '../Mailjet.php';

$subject = "Testar mailjet";
$message = "Hejsan, <strong>vi testar</strong> nu lite mailjetintegration.";
$recipients = [
	'robin@trinax.se',
	'robin+2@trinax.se',
];

$mj = new Mailjet('v3.1');

$mj->sendMessage('email', '', '', $subject, $message, $recipients);

 */

/**
* Created by Eduardo Wallén
* User: Heastost
* Date: 2019-06-23
*/

//require_once __DIR__."/../Mailjet.php";
$parts = explode('/', dirname(dirname(__FILE__)));
$parts = array_slice($parts, 0, -1);
define('ROOT', implode('/', $parts).'/');

require_once ROOT.'config/config.php';
require_once ROOT.'lib/functions.php';

$to = array('email' => 'eduardo.wallen@chartbooker.com', 'name' => 'Eduardo Wallén');
$reply_to = array('email' => 'eduardo.wallen@hotmail.com', 'name' => 'Eduardo Wallén');

$mj = new Mailjet('v3.1');

$mj->sendMessage('email', '', '', $to, $reply_to, '/var/www/public/invoices/fairs/141/exhibitors/16975/Capeco-A14-55152.pdf');
