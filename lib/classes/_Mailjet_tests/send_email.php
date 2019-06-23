<?php
/**
 * Created by PhpStorm.
 * User: trinaxrobin
 * Date: 2019-02-26
 * Time: 18:55
 */
			
require_once '/var/www/lib/classes/Mailjet.php';

$subject = "Testar mailjet";
$message = "Hejsan, <strong>vi testar</strong> nu lite mailjetintegration.";
$recipients = 'eduardo.wallen@chartbooker.com';

$mj = new Mailjet('v3');

$mj->sendMessage('email', 'test.server@chartbookerdemo.com', 'Chartbookerdemo', $subject, $message, $recipients);
