<?php
/**
 * Created by PhpStorm.
 * User: trinaxrobin
 * Date: 2019-02-25
 * Time: 12:46
 */

require_once '../Mailjet.php';

/**
 * v4 av API används vid skickning av SMS
 */

$message = "Hej\nVi testar SMS via Mailjet";
$recipients = [
	'0701234567',
	'0702344568'
];


$mj = new Mailjet('v4');

foreach ($recipients as $recipient) {
	$mj->sendMessage('sms', '', '', '', $message, $recipient);
}
/* 	Mailjet's API dokumentation för SMS saknar information om multipla sms mottagare såvitt jag kan se,
	därav denna foreach()
 */


// Kastar fel om uppstår