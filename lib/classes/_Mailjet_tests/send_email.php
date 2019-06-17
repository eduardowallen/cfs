<?php
/**
 * Created by PhpStorm.
 * User: trinaxrobin
 * Date: 2019-02-26
 * Time: 18:55
 */

require_once '../Mailjet.php';

$subject = "Testar mailjet";
$message = "Hejsan, <strong>vi testar</strong> nu lite mailjetintegration.";
$recipients = [
	'robin@trinax.se',
	'robin+2@trinax.se',
];

$mj = new Mailjet('v3.1');

$mj->sendMessage('email', '', '', $subject, $message, $recipients);
