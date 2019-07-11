<?php

if (!defined('ROOT')) {
	define('ROOT', dirname(dirname(__FILE__)).'/');
	session_start();
	require_once ROOT.'config/config.php';
	require_once ROOT.'lib/functions.php';
}

//Autoload any classes that are required
if (!function_exists('cb_autoload')) {
	function cb_autoload($className) {
		if (file_exists(ROOT.'lib/classes/'.$className.'.php')) {
			require_once(ROOT.'lib/classes/'.$className.'.php');
			return true;
		} else if (file_exists(ROOT.'application/controllers/'.$className.'.php')) {
			require_once(ROOT.'application/controllers/'.$className.'.php');
			return true;
		} else if (file_exists(ROOT.'application/models/'.$className.'.php')) {
			require_once(ROOT.'application/models/'.$className.'.php');
			return true;
		}
	  return false;
	}
}
spl_autoload_register( 'cb_autoload' );
if (!defined('LANGUAGE')) {
	define('LANGUAGE', 'sv');
}

$globalDB = new Database;
global $globalDB;
/*
// Cron job logic starts here

$statement = $globalDB->prepare("SELECT fmp.id, 
								fmp.name AS position_name, 
								e.id AS exhibitor, 
								e.clone AS clone
								u.contact_email AS exhibitor_email, 
								u.name AS user_name, 
								u.company AS user_company,
								uc.contact_email AS organizer_email, 
								f.url,
								f.name AS fair_name,
								f.id AS fair_id,
								DATEDIFF(fmp.expires, ?) AS diff, 
								fmp.expires, 
								reminder_day1, 
								reminder_note1
							FROM fair_map_position AS fmp 
							INNER JOIN fair_map AS fm ON fm.id = fmp.map 
							INNER JOIN fair AS f ON f.id = fm.fair 
							INNER JOIN exhibitor AS e ON e.position = fmp.id 
							INNER JOIN user AS u ON u.id = e.user 
							INNER JOIN user AS uc ON uc.id = f.created_by
							WHERE fmp.expires != '0000-00-00 00:00:00' AND reminder_day1 > 0 AND clone = 0
							HAVING diff > 0 AND (diff = reminder_day1)");

$statement->execute(array(date('Y-m-d')));

$expiring_positions = $statement->fetchAll(PDO::FETCH_CLASS);

foreach ($expiring_positions as $position) {

	// Which of the 3 dates were matched?
	$number = 1;
	$fair = new Fair();
	$fair->loadsimple($position->fair_id, 'id');

	$mailSettings = json_decode($fair->get("mail_settings"));
	$reminder_note = $position->{'reminder_note1'};
	if ($reminder_note != '') {
		$reminder_note = '<br>'.$reminder_note;
	}

	if (is_array($mailSettings->reservationReminders)) {

		$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("windowtitle"));

		if (in_array("0", $mailSettings->reservationReminders)) {
			/* Prepare to send the mail */
			/*
			$recipient = array($position->organizer_email => $position->organizer_email);
			if (defined('TESTSERV')) {
				$recipient = array('eduardo.wallen@chartbooker.com' => 'eduardo.wallen@chartbooker.com');
			}
			/* UPDATED TO FIT MAILJET */
			/*
			$mail = new Mail();
			$mail->setTemplate('stand_place_remind_org1');
			$mail->setFrom($from);
			$mail->setRecipient($recipient);
			/* Setting mail variables */
			/*
			$mail->setMailVar('reminder_note', $reminder_note);
			$mail->setMailVar('event_name', $fair->get('windowtitle'));
			$mail->setMailVar('event_email', $fair->get('contact_email'));
			$mail->setMailVar('event_phone', $fair->get('contact_phone'));
			$mail->setMailVar('event_contact', $fair->get('contact_name'));
			$mail->setMailVar('event_website', $fair->get('website'));
			$mail->setMailVar('event_url', BASE_URL . $position->url);
			$mail->setMailVar('exhibitor_company', $position->user_company);
			$mail->setMailVar('position_name', $position->position_name);
			$mail->setMailVar('expirationdate', $position->expires);
			$mail->sendMessage();
			//$mail->setMailVar('days_until_expiration', $position->diff);
		}

		if (in_array("1", $mailSettings->reservationReminders)) {

			// Send mail to exhibitor
			$recipient = array($position->exhibitor_email, $position->exhibitor_email);
			if (defined('TESTSERV')) {
				$recipient = array('eduardo.wallen@chartbooker.com', 'eduardo.wallen@chartbooker.com');
			}
				$mail = new Mail();
				$mail->setTemplate('stand_place_remind1');
				$mail->setFrom($from);
				$mail->setRecipient($recipient);
				$mail->setMailVar('reminder_note', $reminder_note);
				$mail->setMailVar('event_name', $fair->get('windowtitle'));
				$mail->setMailVar('event_url', BASE_URL . $position->url);
				$mail->setMailVar('exhibitor_company_name', $position->user_company);
				$mail->setMailVar('position_name', $position->position_name);
				$mail->setMailVar('expirationdate', $position->expires);
				$mail->sendMessage();
				//$mail->setMailVar('days_until_expiration', $position->diff);
		}
	}
}
*/
?>
