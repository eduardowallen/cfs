<?php

if (!defined('ROOT')) {
	define('ROOT', dirname(dirname(__FILE__)).'/');
	session_start();


	require_once ROOT.'config/config.php';
	require_once ROOT.'lib/functions.php';
}

//Autoload any classes that are required
if (!function_exists('__autoload')) {
	function __autoload($className) {
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
	  
	  // This is the else without the else, but works exactly as else
	  error_log("Class not found: ".$className);
	  //throw new Exception("500");
	  return false;
	}
}

if (!defined('LANGUAGE')) {
	define('LANGUAGE', 'sv');
}

$globalDB = new Database;
global $globalDB;

// Cron job logic starts here

$statement = $globalDB->prepare("SELECT fmp.id, 
								fmp.name AS position_name, 
								e.id AS exhibitor, 
								u.email AS exhibitor_email, 
								u.name AS user_name, 
								uc.email AS organizer_email, 
								f.url,
								f.name AS fair_name,
								DATEDIFF(fmp.expires, ?) AS diff, 
								fmp.expires, 
								reminder_day1, 
								reminder_day2, 
								reminder_day3, 
								reminder_note1, 
								reminder_note2, 
								reminder_note3 
							FROM fair_map_position AS fmp 
							INNER JOIN fair_map AS fm ON fm.id = fmp.map 
							INNER JOIN fair AS f ON f.id = fm.fair 
							INNER JOIN exhibitor AS e ON e.position = fmp.id 
							INNER JOIN user AS u ON u.id = e.user 
							INNER JOIN user AS uc ON uc.id = f.created_by 
							HAVING diff > 0 
								AND (diff = reminder_day1 OR 
								diff = reminder_day2 OR 
								diff = reminder_day3)");

$statement->execute(array(date('Y-m-d')));
$expiring_positions = $statement->fetchAll(PDO::FETCH_CLASS);

foreach ($expiring_positions as $position) {

	// Which of the 3 dates were matched?
	$number = 1;
	if ($position->diff == $position->reminder_day2) {
		$number = 2;
	} else if ($position->diff == $position->reminder_day3) {
		$number = 3;
	}

	// Send mail to exhibitor
	$to = $position->exhibitor_email;
	if (defined('TESTSERV')) {
		$to = 'example@chartbooking.com';
	}

<<<<<<< HEAD
	$mail = new Mail($to, 'stand_place_remind' . $number, $position->url . EMAIL_FROM_DOMAIN);
=======
	$mail = new Mail($to, 'stand_place_remind' . $number, $position->url . '@chartbooker.com', $position->fair_name);
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
	$mail->setMailVar('reminder_note', $position->{'reminder_note' . $number});
	$mail->setMailVar('event_name', $position->fair_name);
	$mail->setMailVar('url', BASE_URL . $position->url);
	$mail->setMailVar('exhibitor_name', $position->user_name);
	$mail->setMailVar('position_name', $position->position_name);
	$mail->setMailVar('date_expires', $position->expires);
	$mail->setMailVar('days_until_expiration', $position->diff);
	$mail->send();

	// Send mail to organizer
	$to = $position->organizer_email;
	if (defined('TESTSERV')) {
		$to = 'example@chartbooking.com';
	}

<<<<<<< HEAD
	$mail = new Mail($to, 'stand_place_remind_org' . $number, $position->url . EMAIL_FROM_DOMAIN);
=======
	$mail = new Mail($to, 'stand_place_remind_org' . $number, $position->url . '@chartbooker.com', $position->fair_name);
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
	$mail->setMailVar('reminder_note', $position->{'reminder_note' . $number});
	$mail->setMailVar('event_name', $position->fair_name);
	$mail->setMailVar('url', BASE_URL . $position->url);
	$mail->setMailVar('exhibitor_name', $position->user_name);
	$mail->setMailVar('position_name', $position->position_name);
	$mail->setMailVar('date_expires', $position->expires);
	$mail->setMailVar('days_until_expiration', $position->diff);
	$mail->send();
#echo "Skickade ett meddelande\n";
}
?>
