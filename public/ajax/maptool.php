<?php

$parts = explode('/', dirname(dirname(__FILE__)));
$parts = array_slice($parts, 0, -1);
define('ROOT', implode('/', $parts).'/');

session_start();
require_once ROOT.'config/config.php';
require_once ROOT.'lib/functions.php';

//Autoload any classes that are required
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

spl_autoload_register( 'cb_autoload' );

$lang = (isset($_COOKIE['language'])) ? $_COOKIE['language'] : 'eng';
define('LANGUAGE', $lang);
$translator = new Translator($lang);

$globalDB = new Database;
global $globalDB;

function userHasAccess() {
	
}
if (isset($_GET['checkIfLocked'])) {
	$fair = new Fair();
	$fair->loadself($_GET['checkIfLocked'], 'id');
	if ($fair->isLocked()) {
		echo true;
	}
	exit;
}
if (isset($_POST['markPositionAsBeingEdited'])) {
	$pos = new FairMapPosition();
	$pos->load($_POST['markPositionAsBeingEdited'], 'id');
	$pos->set('being_edited', $_SESSION['user_id']);
	$pos->set('edit_started', time());
	$pos->save();
	exit;
	
}

if (isset($_POST['markPositionAsNotBeingEdited'])) {
	$pos = new FairMapPosition();
	$pos->load($_POST['markPositionAsNotBeingEdited'], 'id');
	$pos->set('being_edited', 0);
	$pos->set('edit_started', 0);
	$pos->save();
	exit;
	
}

if (isset($_POST['delete_copied_fairreg'])) {
	$fair_registration = new FairRegistration();
	$fair_registration->load($_POST['delete_copied_fairreg'], 'id');
	if ($fair_registration->wasLoaded()) {
		unset($_SESSION['copied_fair_registration']);
		$fair_registration->accept();
	}
}

if (isset($_GET['getBusyStatus'])) {
	$pos = new FairMapPosition();
	$pos->load($_GET['getBusyStatus'], 'id');

	$map = new FairMap();
	$map->load($pos->get('map'), 'id');

	$num_prel_bookings = 0;

	if (userLevel() > 1 && userCanAdminFair($map->get('fair'), $map->get('id'))) {
		/* Prepare a SQL statement for getting the number of prelimnary bookings for a position */
		$num_prel_booking_stmt = $globalDB->prepare("SELECT COUNT(*) AS cnt FROM preliminary_booking WHERE position = ?");

		/* Fetch any preliminary bookings for this position */
		$num_prel_booking_stmt->execute(array($pos->get('id')));
		$num_prel_result = $num_prel_booking_stmt->fetchObject();

		if (isset($num_prel_result->cnt)) {
			$num_prel_bookings = $num_prel_result->cnt;
		}

	} else if (userLevel() == 1) {
		/* Check if this Exhibitor has preliminary booked this position */
		$user = new User();
		$user->load($_SESSION['user_id'], 'id');
		$preliminaries = $user->getPreliminaries();
		foreach ($preliminaries as $p) {
			if ($p['position'] == $pos->get('id')) {
				++$num_prel_bookings;
				break;
			}
		}
	}

	header('Content-type: application/json; charset=utf-8');
	echo json_encode(array(
		'position' => $pos->get('id'),
		'being_edited' => ($pos->get('being_edited') > 0),
		'edit_started' => $pos->get('edit_started'),
		'statusText' => ($num_prel_bookings > 0 && $pos->get('status') == 0 ? 'applied' : $pos->getStatusText())
	));

	exit;
}

/*
if (isset($_POST['getPreliminary'])) {
	
	$query = $globalDB->query("SELECT prel.*, user.* FROM preliminary_booking AS prel LEFT JOIN user ON prel.user = user.id WHERE position = '".$_POST['getPreliminary']."'");
	$result = $query->fetch(PDO::FETCH_ASSOC);
	
	$result['categories'] = explode('|', $result['categories']);
	
	//die(json_encode($result));
	
}
*/
if (isset($_POST['init'])) {

	$map = new FairMap();
	$map->load($_POST['init'], 'id');

	$fair = new Fair();
	$fair->loadsimple($map->get('fair'), 'id');
	if ($fair->wasLoaded()) {
		$fairInvoice = new FairInvoice();
		$fairInvoice->load($map->get('fair'), 'fair');
	} else {
		$fair->loadsimple($_SESSION['user_fair'], 'id');
		if ($fair->wasLoaded()) {
			$fairInvoice = new FairInvoice();
			$fairInvoice->load($fair->get('id'), 'fair');
		}
	}
	$prels = array();
	if (userLevel() == 1) {
		$user = new User();
		$user->load($_SESSION['user_id'], 'id');
		$preliminaries = $user->getPreliminaries();
		foreach ($preliminaries as $p) {
			$prels[] = $p['position'];
		}
	}

	$userId = (userLevel() > 0) ? $_SESSION['user_id'] : 0;
	$ret = array(
		'userlevel' => userLevel(),
		'user_id' =>  $userId,
		'preliminaries' => $prels,
		'id'=> $map->get('id'),
		'fair'=> $fair->get('id'),
		'fairGroup'=> $fair->get('group'),
		'defaultreservationdate'=> date('d-m-Y H:i', $fair->get('default_reservation_date')),
		'currency'=> $fair->get('currency'),
		'name'=>$map->get('name'),
		'image'=>$map->get('large_image'),
		'large_image'=>$map->get('large_image'),
		'islocked'=>$fair->isLocked(),
		'positions'=>array()
	);

	if (userLevel() > 1) {
		/* Prepare a SQL statement for getting the number of prelimnary bookings for a position */
		$num_prel_booking_stmt = $globalDB->prepare("SELECT COUNT(*) AS cnt FROM preliminary_booking WHERE position = ?");
	}

	foreach ($map->get('positions') as $pos) {
		$ex = $pos->get('exhibitor');
		$cats = array();
		$opts = array();
		$arts = array();
		$num_prel = 0;
		$applied = 0;
		unset($ex->password);
		if (is_object($ex)) {
			$ex->set('commodity', $ex->get('spot_commodity'));
			foreach ($ex->get('exhibitor_categories') as $cat) {
				$c = new ExhibitorCategory();
				$c->load($cat, 'id');
				if ($c->wasLoaded()) {
					$c->set('category_id', $cat);
					$cats[] = $c;
				}
			}
			$ex->set('categories', $cats);
			foreach ($ex->get('exhibitor_options') as $opt) {
				$o = new FairExtraOption();
				$o->load($opt, 'id');
				if ($o->wasLoaded()) {
					$o->set('option_id', $opt);
					$opts[] = $o;
				}
			}
			$ex->set("options", $opts);
			$articles = $ex->get('exhibitor_articles');
			$amount = $ex->get('exhibitor_articles_amount');
			if (!empty($articles) && !empty($amount)) {
				foreach (array_combine($articles, $amount) as $art => $qt) {
					//file_put_contents('php://stderr', print_r($art, TRUE));
					$a = new FairArticle();
					$a->load($art, 'id');
					if ($a->wasLoaded()) {
						$a->set('article_id', $art);
						$a->set('amount', $qt);
						$arts[] = $a;
					}
				}
			}
			$ex->set("articles", $arts);
		} else if (userLevel() > 1) {
			$applied = 0;
			if (userCanAdminFair($map->get('fair'), $map->get('id'))) {
				/* Fetch any preliminary bookings for this position */
				$num_prel_booking_stmt->execute(array($pos->get('id')));
				$num_prel_result = $num_prel_booking_stmt->fetchObject();

				if ($num_prel_result->cnt > 0) {
					$applied = 1;
					$num_prel = $num_prel_result->cnt;
				}
			}
		} else if (in_array($pos->get('id'), $prels)) {
			$applied = 1;
		}

		/*	If this position has been edited for a very long time, reset the flags!
			Maybe the user just shut down the page or some error occured? */
		if ($pos->get('being_edited') > 0 && $pos->get('edit_started')+60*3 < time()) {
			$pos->set('being_edited', 0);
			$pos->set('edit_started', 0);
			$pos->save();
		}

		$length = array_push($ret["positions"], array(
			'id' => $pos->get('id'),
			'x' => $pos->get('x'),
			'y' => $pos->get('y'),
			'name' => $pos->get('name'),
			'area' => $pos->get('area'),
			'price' => $pos->get('price'),
			'vat' => $fairInvoice->get('pos_vat'),
			'information' => $pos->get('information'),
			'status' => $pos->get('status'),
			'statusText' => $pos->getStatusText(),
			'exhibitor' => $ex,
			'expires' => date('d-m-Y H:i', strtotime($pos->get('expires'))),
			'applied' => $applied,
			'num_prel_bookings' => $num_prel,
			'being_edited' => $pos->get('being_edited'),
			'edit_started' => $pos->get('edit_started')
		));
		if (isset($user)) {
			$ret["positions"][$length - 1]["preliminaries"] = $user->getPreliminaries();
		}
	}

	echo json_encode($ret);
	exit;

}

if (isset($_POST['deleteMarker'])) {

	if (userLevel() < 2)
		exit;

	$pos = new FairMapPosition();
	$pos->load($_POST['deleteMarker'], 'id');
	$pos->delete();
	exit;

}


if (isset($_POST['bookPosition'])) {

	if (userLevel() < 1)
		exit;

	$pos = new FairMapPosition();
	$pos->load($_POST['bookPosition'], 'id');
	
	/* Delete existing exhibitor if position is booked */
	if ($pos->get('status') === 0) {
		$exhibitor = new Exhibitor();
	} else {
		$exhibitor = new Exhibitor();
		$exhibitor->load($pos->get('id'), 'position');
	}

	if (isset($_POST['user']) && userLevel() > 1) {
		$exhibitor->set('user', $_POST['user']);
	} else {
		$exhibitor->set('user', $_SESSION['user_id']);
	}

	$exhibitor->set('fair', $_POST['fair']);
	$exhibitor->set('position', $_POST['bookPosition']);
	$exhibitor->set('commodity', $_POST['commodity']);
	$exhibitor->set('arranger_message', $_POST['arranger_message']);
	$exhibitor->set('booking_time', time());
	$exhibitor->set('edit_time', 0);
	$exhibitor->set('clone', 0);
	$exhibitor->set('status', 2);
	$exId = $exhibitor->save();

	$pos->set('status', 2);
	$pos->set('expires', '0000-00-00 00:00:00');
	$pos->save();

	if ($exhibitor->wasLoaded()) {
		$stmt = $pos->db->prepare("SELECT id FROM exhibitor_invoice WHERE exhibitor = ? AND status = 1");
		$stmt->execute(array($exId));
		$result = $stmt->fetch();
		if ($result > 0) {
			$stmt = $pos->db->prepare("UPDATE exhibitor_invoice SET status = 2 WHERE exhibitor = ? AND id = ?");
			$stmt->execute(array($exId, $result['id']));
		}
		/* Remove old categories for this booking */
		$stmt = $pos->db->prepare("DELETE FROM exhibitor_category_rel WHERE exhibitor = ?");
		$stmt->execute(array($exId));
		/* Remove old options for this booking */
		$stmt = $pos->db->prepare("DELETE FROM exhibitor_option_rel WHERE exhibitor = ?");
		$stmt->execute(array($exId));
		/* Remove old articles for this booking */
		$stmt = $pos->db->prepare("DELETE FROM exhibitor_article_rel WHERE exhibitor = ?");
		$stmt->execute(array($exId));
	}

	if (isset($_POST['categories']) && is_array($_POST['categories'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_category_rel` (`exhibitor`, `category`) VALUES (?, ?)");
		foreach ($_POST['categories'] as $cat) {
			$stmt->execute(array($exId, $cat));
		}
	}

	if (isset($_POST['options']) && is_array($_POST['options'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");
		foreach ($_POST['options'] as $opt) {								
			$stmt->execute(array($exId, $opt));
		}
	}

	if (isset($_POST['articles']) && is_array($_POST['articles'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
		foreach (array_combine($_POST['articles'], $_POST['artamount']) as $art => $amount) {
			$stmt->execute(array($exId, $art, $amount));
		}
	}
	$fair = new Fair();
	$fair->loadsimple($exhibitor->get('fair'), 'id');

	/* Check mail settings and send only if setting is set */
	if ($fair->wasLoaded()) {
		$mailSettings = json_decode($fair->get("mail_settings"));
		if (isset($mailSettings->BookingCreated) && is_array($mailSettings->BookingCreated)) {
			if (in_array("1", $mailSettings->BookingCreated)) {
				$user = new User();
				$user->load2($exhibitor->get('user'), 'id');

				if ($fair->get('contact_name') == '')
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('windowtitle'));
				else
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('contact_name'));
				/* Prepare to send the mail */
				if ($user->get('contact_email') == '')
				$recipient = array($user->get('email'), $user->get('company'));
				else
				$recipient = array($user->get('contact_email'), $user->get('name'));
				/* UPDATED TO FIT MAILJET */
				$mail_user = new Mail();
				$mail_user->setTemplate('booking_created_receipt');
				$mail_user->setFrom($from);
				$mail_user->setRecipient($recipient);
				/* Setting mail variables */
				$mail_user->setMailVar('exhibitor_company', $user->get('company'));
				$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
				$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
				$mail_user->setMailVar('event_email', $fair->get('contact_email'));
				$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
				$mail_user->setMailVar('event_website', $fair->get('website'));
				$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
				$mail_user->setMailVar('position_name', $pos->get('name'));
				$mail_user->setMailVar('position_area', $pos->get('area'));
				$mail_user->sendMessage();
			}
		}
	}
	exit;
}


if (isset($_POST['fairRegistration'])) {
	
	if (userLevel() == 1) {

		if ($_POST['fair'] > 0) {
			$fair = new Fair();
			$fair->loadsimple($_POST['fair'], 'id');
		} else {
			$fair = new Fair();
			$fair->loadsimple($_SESSION['user_fair'], 'id');
		}
		$user = new User();
		$user->load($_SESSION['user_id'], 'id');

		if ($fair->wasLoaded() && $user->wasLoaded()) {

			$category_ids = '';
			if (isset($_POST['categories']) && is_array($_POST['categories'])) {
				$category_ids = implode('|', $_POST['categories']);
			}

			$option_ids = '';
			if (isset($_POST['options']) && is_array($_POST['options'])) {
				$option_ids = implode('|', $_POST['options']);
			}

			$article_ids = '';
			$article_amounts = '';
			if (!empty($_POST['articles']) && !empty($_POST['artamount'])) {
					$article_ids = implode('|', $_POST['articles']);
					$article_amounts = implode('|', $_POST['artamount']);				
			}			
			$registration = new FairRegistration();
			$registration->set('user', $user->get('id'));
			$registration->set('fair', $fair->get('id'));
			$registration->set('categories', $category_ids);
			$registration->set('options', $option_ids);
			$registration->set('articles', $article_ids);
			$registration->set('amount', $article_amounts);
			$registration->set('commodity', $_POST['commodity']);
			$registration->set('arranger_message', $_POST['arranger_message']);
			$registration->set('area', $_POST['area']);
			$registration->set('booking_time', time());
			$registration->save();

			/* Connect user to fair */
			if (!userIsConnectedTo($fair->get('id'))) {
				$stmt = $registration->db->prepare("INSERT INTO fair_user_relation (`fair`, `user`, `connected_time`) VALUES (?, ?, ?)");
				$stmt->execute(array($fair->get('id'), $user->get('id'), time()));
			}

			/* Prepare to send the mail */
			if ($fair->get('contact_name') == '')
			$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('windowtitle'));
			else
			$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('contact_name'));
			
			if ($user->get('contact_email') == '')
			$recipient = array($user->get('email'), $user->get('company'));
			else
			$recipient = array($user->get('contact_email'), $user->get('name'));
			/* UPDATED TO FIT MAILJET */
			$mail_user = new Mail();
			$mail_user->setTemplate('registration_created_receipt');
			$mail_user->setFrom($from);
			$mail_user->setRecipient($recipient);
			/* Setting mail variables */
			$mail_user->setMailVar('exhibitor_company', $user->get('company'));
			$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
			$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
			$mail_user->setMailVar('event_email', $fair->get('contact_email'));
			$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
			$mail_user->setMailVar('event_website', $fair->get('website'));
			$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
			$mail_user->setMailVar('comment', $_POST['arranger_message']);
			$mail_user->setMailVar('position_area', $_POST['area']);
			$mail_user->sendMessage();

			/* Check mail settings and send only if setting is set */
			$mailSettings = json_decode($fair->get("mail_settings"));
			if (is_array($mailSettings->RegistrationCreated)) {
				if (in_array("0", $mailSettings->RegistrationCreated)) {
					$organizer = new User();
					$organizer->load2($fair->get('created_by'), 'id');
					/* Prepare to send the mail */
					if ($organizer->get('contact_email') == '')
					$recipient = array($organizer->get('email'), $organizer->get('company'));
					else
					$recipient = array($organizer->get('contact_email'), $organizer->get('name'));
					$mail_organizer = new Mail();
					$mail_organizer->setTemplate('registration_created_confirm');
					$mail_organizer->setFrom($from);
					$mail_organizer->setRecipient($recipient);
					/* Setting mail variables */
					$mail_organizer->setMailVar('exhibitor_company', $user->get('company'));
					$mail_organizer->setMailVar('event_contact', $fair->get('contact_name'));
					$mail_organizer->setMailVar('event_name', $fair->get('windowtitle'));
					$mail_organizer->setMailVar('event_url', BASE_URL . $fair->get('url'));
					$mail_organizer->setMailVar('comment', $_POST['arranger_message']);
					$mail_organizer->setMailVar('position_area', $_POST['area']);
					$mail_organizer->sendMessage();
				}
			}
		}
	}

	exit;
}

if (isset($_POST['reservePosition'])) {

	if (userLevel() < 1)
		exit;

	$pos = new FairMapPosition();
	$pos->load($_POST['reservePosition'], 'id');
	
	//Delete existing exhibitor if position is booked
	if ($pos->get('status') === 0) {
		$exhibitor = new Exhibitor();
	} else {
		$exhibitor = new Exhibitor();
		$exhibitor->load($pos->get('id'), 'position');
	}

	if (isset($_POST['user']) && userLevel() > 1) {
		$exhibitor->set('user', $_POST['user']);
	} else {
		$exhibitor->set('user', $_SESSION['user_id']);
	}

	$exhibitor->set('fair', $_POST['fair']);
	$exhibitor->set('position', $_POST['reservePosition']);
	$exhibitor->set('commodity', $_POST['commodity']);
	$exhibitor->set('arranger_message', $_POST['arranger_message']);
	$exhibitor->set('booking_time', time());
	$exhibitor->set('edit_time', 0);
	$exhibitor->set('clone', 0);
	$exhibitor->set('status', 1);
	$exId = $exhibitor->save();

	$pos->set('status', 1);
	$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));
	$pos->save();

	if ($exhibitor->wasLoaded()) {
		$stmt = $pos->db->prepare("SELECT id FROM exhibitor_invoice WHERE exhibitor = ? AND status = 2");
		$stmt->execute(array($exId));
		$result = $stmt->fetch();
		if ($result > 0) {
			$stmt = $pos->db->prepare("UPDATE exhibitor_invoice SET status = 1 WHERE exhibitor= ? AND id = ?");
			$stmt->execute(array($exId, $result['id']));
		}
		// Remove old categories for this booking
		$stmt = $pos->db->prepare("DELETE FROM exhibitor_category_rel WHERE exhibitor = ?");
		$stmt->execute(array($exId));
		// Remove old options for this booking
		$stmt = $pos->db->prepare("DELETE FROM exhibitor_option_rel WHERE exhibitor = ?");
		$stmt->execute(array($exId));
		// Remove old articles for this booking
		$stmt = $pos->db->prepare("DELETE FROM exhibitor_article_rel WHERE exhibitor = ?");
		$stmt->execute(array($exId));
	}

	if (isset($_POST['categories']) && is_array($_POST['categories'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_category_rel` (`exhibitor`, `category`) VALUES (?, ?)");
		foreach ($_POST['categories'] as $cat) {
			$stmt->execute(array($exId, $cat));
		}
	}

	if (isset($_POST['options']) && is_array($_POST['options'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");

		foreach ($_POST['options'] as $opt) {								
			$stmt->execute(array($exId, $opt));
		}

		$options = array($option_id, $option_text, $option_price, $option_vat);
	}

	if (isset($_POST['articles']) && is_array($_POST['articles'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
		$arts = $_POST['articles'];
		$amounts = $_POST['artamount'];

		foreach (array_combine($arts, $amounts) as $art => $amount) {
			$stmt->execute(array($exId, $art, $amount));
		}
	}
	
	$fair = new Fair();
	$fair->loadsimple($exhibitor->get('fair'), 'id');
	
	if ($fair->wasLoaded()) {
		/* Check mail settings and send only if setting is set */
		$mailSettings = json_decode($fair->get("mail_settings"));
		if (isset($mailSettings->ReservationCreated) && is_array($mailSettings->ReservationCreated)) {
			$user = new User();
			$user->load2($exhibitor->get('user'), 'id');
			if ($fair->get('contact_name') == '')
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('windowtitle'));
			else
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('contact_name'));
			if (in_array("1", $mailSettings->ReservationCreated)) {
				/* Prepare to send the mail */
				if ($user->get('contact_email') == '')
				$recipient = array($user->get('email'), $user->get('company'));
				else
				$recipient = array($user->get('contact_email'), $user->get('name'));
				/* UPDATED TO FIT MAILJET */
				$mail_user = new Mail();
				$mail_user->setTemplate('reservation_created_receipt');
				$mail_user->setFrom($from);
				$mail_user->setRecipient($recipient);
				/* Setting mail variables */
				$mail_user->setMailVar('exhibitor_company', $user->get('company'));
				$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
				$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
				$mail_user->setMailVar('event_email', $fair->get('contact_email'));
				$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
				$mail_user->setMailVar('event_website', $fair->get('website'));
				$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
				$mail_user->setMailVar('position_name', $pos->get('name'));
				$mail_user->setMailVar('position_area', $pos->get('area'));
				$mail_user->sendMessage();
			}
		}
	}
	exit;
}

if (isset($_POST['editBooking'])) {

	if (userLevel() < 1)
		exit;

	$pos = new FairMapPosition();
	$pos->load($_POST['editBooking'], 'id');

	$exhibitor = new Exhibitor();
	$exhibitor->load($_POST['exhibitor_id'], 'id');
	if (!$exhibitor->wasLoaded()) {
		die('exhibitor not found');
	}

	if (isset($_POST['user']) && userLevel() > 1) {
		$exhibitor->set('user', $_POST['user']);
	} else {
		$exhibitor->set('user', $_SESSION['user_id']);
	}

	$exhibitor->set('commodity', $_POST['commodity']);
	$exhibitor->set('arranger_message', $_POST['arranger_message']);
	$exhibitor->set('clone', 0);
	$exId = $exhibitor->save();

	// Remove old categories for this booking
	$stmt = $pos->db->prepare("DELETE FROM exhibitor_category_rel WHERE exhibitor = ?");
	$stmt->execute(array($exId));
	// Remove old options for this booking
	$stmt = $pos->db->prepare("DELETE FROM exhibitor_option_rel WHERE exhibitor = ?");
	$stmt->execute(array($exId));
	// Remove old articles for this booking
	$stmt = $pos->db->prepare("DELETE FROM exhibitor_article_rel WHERE exhibitor = ?");
	$stmt->execute(array($exId));

	if (isset($_POST['categories']) && is_array($_POST['categories'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_category_rel` (`exhibitor`, `category`) VALUES (?, ?)");
		foreach ($_POST['categories'] as $cat) {
			$stmt->execute(array($exId, $cat));
		}
	}

	if (isset($_POST['options']) && is_array($_POST['options'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");
		foreach ($_POST['options'] as $opt) {								
			$stmt->execute(array($exId, $opt));
		}
	}

	if (isset($_POST['articles']) && is_array($_POST['articles'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
		$arts = $_POST['articles'];
		$amounts = $_POST['artamount'];
		foreach (array_combine($arts, $amounts) as $art => $amount) {
			$stmt->execute(array($exId, $art, $amount));							
		}
	}
	
	// If this is a reservation (status is 1), then also set the expiry date
	if (isset($_POST['expires'])) {
		$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));
		$pos->save();
	}
	exit;
}

if (isset($_POST['preliminary'])) {
	
	if (userLevel() == 1) {

		$pos = new FairMapPosition();
		$pos->load($_POST['preliminary'], 'id');	

		$map = new FairMap();
		$map->load($pos->get('map'), 'id');

		$fair = new Fair();
		$fair->loadsimple($map->get('fair'), 'id');

		$user = new User();
		$user->load2($_SESSION['user_id'], 'id');

		if ($fair->wasLoaded() && $user->wasLoaded()) {
			$category_ids = '';
			if (isset($_POST['categories']) && is_array($_POST['categories'])) {
				$category_ids = implode('|', $_POST['categories']);
			}

			$option_ids = '';
			if (isset($_POST['options']) && is_array($_POST['options'])) {
				$option_ids = implode('|', $_POST['options']);
			}

			$article_ids = '';
			$article_amounts = '';
			if (!empty($_POST['articles']) && !empty($_POST['artamount'])) {
				$article_ids = implode('|', $_POST['articles']);
				$article_amounts = implode('|', $_POST['artamount']);
			}

			$pb = new PreliminaryBooking();
			$pb->set('user', $user->get('id'));
			$pb->set('fair', $fair->get('id'));
			$pb->set('position', $pos->get('id'));
			$pb->set('categories', $category_ids);
			$pb->set('options', $option_ids);
			$pb->set('articles', $article_ids);
			$pb->set('amount', $article_amounts);
			$pb->set('commodity', $_POST['commodity']);
			$pb->set('arranger_message', $_POST['arranger_message']);
			$pb->set('booking_time', time());
			$pb->save();

			$mailSettings = json_decode($fair->get("mail_settings"));
			/* Check mail settings and send only if setting is set */
			if (isset($mailSettings->PreliminaryCreated) && is_array($mailSettings->PreliminaryCreated)) {
				if (in_array("0", $mailSettings->PreliminaryCreated)) {
					$organizer = new User();
					$organizer->load2($fair->get('created_by'), 'id');
					/* Prepare to send the mail */
					if ($organizer->get('contact_email') == '')
					$recipient = array($organizer->get('email'), $organizer->get('company'));
					else
					$recipient = array($organizer->get('contact_email'), $organizer->get('name'));
					/* UPDATED TO FIT MAILJET */
					$mail_organizer = new Mail();
					$mail_organizer->setTemplate('preliminary_created_confirm');
					$mail_organizer->setFrom($from);
					$mail_organizer->setRecipient($recipient);
					/* Setting mail variables */
					$mail_organizer->setMailVar('exhibitor_company', $user->get('company'));
					$mail_organizer->setMailVar('event_name', $fair->get('windowtitle'));
					$mail_organizer->setMailVar('event_url', BASE_URL . $fair->get('url'));
					$mail_organizer->setMailVar('position_name', $pos->get('name'));
					$mail_organizer->setMailVar('position_area', $pos->get('area'));
					$mail_organizer->setMailVar('comment', $_POST['arranger_message']);
					$mail_organizer->sendMessage();
				}
			}
			/* Prepare to send the mail */
			if ($user->get('contact_email') == '')
			$recipient = array($user->get('email'), $user->get('company'));
			else
			$recipient = array($user->get('contact_email'), $user->get('name'));
			/* UPDATED TO FIT MAILJET */
			$mail_user = new Mail();
			$mail_user->setTemplate('preliminary_created_receipt');
			$mail_user->setFrom($from);
			$mail_user->setRecipient($recipient);
			/* Setting mail variables */
			$mail_user->setMailVar('exhibitor_company', $user->get('company'));
			$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
			$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
			$mail_user->setMailVar('event_email', $fair->get('contact_email'));
			$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
			$mail_user->setMailVar('event_website', $fair->get('website'));
			$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
			$mail_user->setMailVar('position_name', $pos->get('name'));
			$mail_user->setMailVar('position_area', $pos->get('area'));
			$mail_user->setMailVar('comment', $_POST['arranger_message']);
			$mail_user->sendMessage();
		}
	}
	exit;
}
if (isset($_POST['cancelPreliminary'])) {
	if (userLevel() == 1) {
		$pb = new PreliminaryBooking();
		$pb->loadsafe($_POST['cancelPreliminary'], 'position', $_SESSION['user_id'], 'user');
		$pb->del_pre_booking($pb->get('id'), $_SESSION['user_id'], $_POST['cancelPreliminary']);
	}
	exit;
}

if (isset($_POST['cancelBooking'])) {

	if (userLevel() > 1) {

		$position = new FairMapPosition();
		$position->load($_POST['cancelBooking'], 'id');

		$status = $position->get('status');

		$fairMap = new FairMap();
		$fairMap->load($position->get('map'), 'id');

		$fair = new Fair();
		$fair->loadsimple($fairMap->get('fair'), 'id');

		$ex = new Exhibitor();
		$ex->load($position->get('id'), 'position');
		$ex->delete($_POST['comment']);
		$position->set('status', 0);
		$position->set('expires', '0000-00-00 00:00:00');
		$position->save();
		if (isset($_POST['comment']))
		$comment = htmlspecialchars_decode($_POST['comment']);
		if ($fair->wasLoaded()) {
			$mailSettings = json_decode($fair->get("mail_settings"));
			/* Check mail settings and send only if setting is set */
			if (isset($mailSettings->BookingCancelled) && is_array($mailSettings->BookingCancelled)) {
				/* Prepare to send the mail */
				if ($fair->get('contact_name') == '')
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('windowtitle'));
				else
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('contact_name'));
				if (in_array("1", $mailSettings->BookingCancelled)) {
					$user = new User();
					$user->load2($ex->get('user'), 'id');
					if ($user->get('contact_email') == '')
					$recipient = array($user->get('email'), $user->get('company'));
					else
					$recipient = array($user->get('contact_email'), $user->get('name'));
					/* UPDATED TO FIT MAILJET */
					$mail_user = new Mail();
					$mail_user->setTemplate('booking_cancelled_receipt');
					$mail_user->setFrom($from);
					$mail_user->setRecipient($recipient);
					/* Setting mail variables */
					$mail_user->setMailVar('position_name', $position->get('name'));
					$mail_user->setMailVar('exhibitor_company', $user->get('company'));
					$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
					$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
					$mail_user->setMailVar('event_email', $fair->get('contact_email'));
					$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
					$mail_user->setMailVar('event_website', $fair->get('website'));
					$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
					if ($comment)
					$mail_user->setMailVar('comment', $comment);
					$mail_user->sendMessage();
					
				}
			}
		}
	}

	exit;

}

if (isset($_POST['savePosition'])) {

	if (userLevel() < 2)
		exit;

	$pos = new FairMapPosition();
	if ((int)$_POST['savePosition'] > 0) {
		$pos->load((int)$_POST['savePosition'], 'id');
	} else {
		$pos->set('map', $_POST['map']);
		$pos->set('x', $_POST['x']);
		$pos->set('y', $_POST['y']);
		$pos->set('status', 0);
	}

	$pos->set('name', $_POST['name']);
	$pos->set('area', $_POST['area']);
	$pos->set('price', $_POST['price']);
	$pos->set('information', $_POST['information']);
	$pos->save();

	exit;

}

if (isset($_POST['movePosition'])) {

	$pos = new FairMapPosition();
	$pos->load((int)$_POST['movePosition'], 'id');
	$pos->set('x', $_POST['x']);
	$pos->set('y', $_POST['y']);
	$pos->save();

}

if (isset($_POST['getUserCommodity'])) {
	if (userLevel() < 1) {
		exit;
	}

	$user = new User();
	$user->load((int)$_POST['userId'], 'id');
	$answer = array('commodity' => '');
	if ($user->wasLoaded()) {
		$answer['commodity'] = $user->get('commodity');
	}
	echo json_encode($answer);
	exit;
}

if (isset($_POST['emailExists'])) {
	$user = new User();
	$user->load2($_POST['email'], 'email');
	echo json_encode(array('emailExists' => $user->wasLoaded()));	
	exit;
}

if (isset($_POST["aliasExists"])) {
	$user = new User();
	$user->load2($_POST["alias"], "alias");
	echo json_encode(array("aliasExists" => $user->wasLoaded()));
}

if (isset($_POST['connectToFair'])) {
	$response = array();
	if (isset($_SESSION['user_id']) && !userIsConnectedTo($_POST['fairId'])) {
		$fair = new Fair();
		$fair->loadsimple($_POST['fairId'], 'id');
		if ($fair->wasLoaded()) {
			$sql = "INSERT INTO `fair_user_relation`(`fair`, `user`, `connected_time`) VALUES (?,?,?)";
			$stmt = $globalDB->prepare($sql);
			$stmt->execute(array($_POST['fairId'], $_SESSION['user_id'], time()));
			$response['message'] = $translator->{'Connected to fair'}.' '.$fair->get('windowtitle');
			$response['success'] = true;
		} else {
			$response['message'] = $translator->{'Unable to connect to fair.'};
			$response['success'] = false;
		}
	} else {
		$response['message'] = $translator->{'Unable to connect to fair.'};
		$response['success'] = false;
	}
	echo json_encode($response);
}

if (isset($_GET['prel_bookings_list'], $_GET['position'])) {

	$position = new FairMapPosition();
	$position->load($_GET['position'], 'id');

	if ($position->wasLoaded()) {

		$fair_map = new FairMap();
		$fair_map->load($position->get('map'), 'id');

		if ($fair_map->wasLoaded() && userCanAdminFair($fair_map->get('fair'), $fair_map->get('id'))) {

			$stmt = $globalDB->prepare("SELECT * FROM preliminary_booking WHERE position = ?");
			$stmt->execute(array($position->get('id')));
			$result = array();

			foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $prel_booking) {
				$stmt2 = $globalDB->prepare("SELECT `name`, `area`, `price` FROM `fair_map_position` WHERE `id` = ? LIMIT 0, 1");
				$stmt2->execute(array($prel_booking->position));
				$position = $stmt2->fetch(PDO::FETCH_ASSOC);

				$user = new User();
				$user->load($prel_booking->user, 'id');
				$prel_booking->company = $user->get('company');
				$prel_booking->booking_time = date('d-m-Y H:i', $prel_booking->booking_time);
				$prel_booking->standSpace = $position;
				$prel_booking->denyUrl = BASE_URL . 'administrator/deleteBooking/' . $prel_booking->id . "/" . $_GET["position"];
				$prel_booking->denyImgUrl = BASE_URL . 'images/icons/delete.png';
				$prel_booking->baseUrl = BASE_URL;
				$result[] = $prel_booking;
			}

			header('Content-type: application/json; charset=utf-8');
			echo json_encode($result);
		}
	}
}

if (isset($_POST['reserve_preliminary'])) {

	if (userLevel() < 1)
		exit;

	$pb = new PreliminaryBooking();
	$pb->load($_POST['reserve_preliminary'], 'id');

	if ($pb->wasLoaded()) {
		$pos = new FairMapPosition();
		$pos->load($pb->get('position'), 'id');
		$booking_time = $pb->get('booking_time');

		$pos->set('status', 1);
		$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));

		$ex = new Exhibitor();
		$ex->set('user', $pb->get('user'));
		$ex->set('fair', $pb->get('fair'));
		$ex->set('position', $pb->get('position'));
		$ex->set('commodity', $_POST['commodity']);
		$ex->set('arranger_message', $pb->get('arranger_message'));
		$ex->set('edit_time', time());
		$ex->set('clone', 0);
		$ex->set('status', 1);
		
		$exId = $ex->save();
		$pos->save();
		$pb->accept();

		if (isset($_POST['categories']) && is_array($_POST['categories'])) {
			$stmt = $pos->db->prepare("INSERT INTO `exhibitor_category_rel` (`exhibitor`, `category`) VALUES (?, ?)");
			foreach ($_POST['categories'] as $cat) {
				$stmt->execute(array($exId, $cat));
			}
		}

		if (isset($_POST['options']) && is_array($_POST['options'])) {
			$stmt = $pos->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");
			foreach ($_POST['options'] as $opt) {								
				$stmt->execute(array($exId, $opt));
			}
		}

		if (isset($_POST['articles']) && is_array($_POST['articles'])) {
			$stmt = $pos->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
			$arts = $_POST['articles'];
			$amounts = $_POST['artamount'];
			foreach (array_combine($arts, $amounts) as $art => $amount) {
				$stmt->execute(array($exId, $art, $amount));							
			}
		}
		
		$fair = new Fair();
		$fair->loadsimple($ex->get('fair'), 'id');

		$user = new User();
		$user->load2($ex->get('user'), 'id');

		/* Check mail settings and send only if setting is set */
		if ($fair->wasLoaded()) {
			$mailSettings = json_decode($fair->get("mail_settings"));
			if (isset($mailSettings->PreliminaryToReservation) && is_array($mailSettings->PreliminaryToReservation)) {
				if ($fair->get('contact_name') == '')
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('windowtitle'));
				else
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('contact_name'));

				if (in_array("1", $mailSettings->PreliminaryToReservation)) {
					if ($user->get('contact_email') == '')
					$recipient = array($user->get('email'), $user->get('company'));
					else
					$recipient = array($user->get('contact_email'), $user->get('name'));
					/* UPDATED TO FIT MAILJET */
					$mail_user = new Mail();
					$mail_user->setTemplate('preliminary_to_reservation_receipt');
					$mail_user->setFrom($from);
					$mail_user->setRecipient($recipient);
					/* Setting mail variables */
					$mail_user->setMailVar('exhibitor_company', $user->get('company'));
					$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
					$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
					$mail_user->setMailVar('event_email', $fair->get('contact_email'));
					$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
					$mail_user->setMailVar('event_website', $fair->get('website'));
					$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
					$mail_user->setMailVar('position_name', $pos->get('name'));
					$mail_user->setMailVar('position_area', $pos->get('area'));
					$mail_user->sendMessage();
				}
			}
		}
	}
	exit;
}

if (isset($_POST['book_preliminary'])) {

	if (userLevel() < 1)
		exit;

	$pb = new PreliminaryBooking();
	$pb->load($_POST['book_preliminary'], 'id');

	if ($pb->wasLoaded()) {
		$pos = new FairMapPosition();
		$pos->load($pb->get('position'), 'id');
		$booking_time = $pb->get('booking_time');

		$pos->set('status', 2);
		$pos->set('expires', '0000-00-00 00:00:00');

		$ex = new Exhibitor();
		$ex->set('user', $pb->get('user'));
		$ex->set('fair', $pb->get('fair'));
		$ex->set('position', $pb->get('position'));
		$ex->set('commodity', $_POST['commodity']);
		$ex->set('arranger_message', $pb->get('arranger_message'));
		$ex->set('booking_time', $pb->get('booking_time'));
		$ex->set('edit_time', time());
		$ex->set('clone', 0);
		$ex->set('status', 2);
		
		$exId = $ex->save();
		$pos->save();
		$pb->accept();

		if (isset($_POST['categories']) && is_array($_POST['categories'])) {
			$stmt = $pos->db->prepare("INSERT INTO `exhibitor_category_rel` (`exhibitor`, `category`) VALUES (?, ?)");
			foreach ($_POST['categories'] as $cat) {
				$stmt->execute(array($exId, $cat));
			}
		}

		if (isset($_POST['options']) && is_array($_POST['options'])) {
			$stmt = $pos->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");
			foreach ($_POST['options'] as $opt) {								
				$stmt->execute(array($exId, $opt));
			}
		}

		if (isset($_POST['articles']) && is_array($_POST['articles'])) {
			$stmt = $pos->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
			$arts = $_POST['articles'];
			$amounts = $_POST['artamount'];
			foreach (array_combine($arts, $amounts) as $art => $amount) {
				$stmt->execute(array($exId, $art, $amount));
			}
		}
		
		$fair = new Fair();
		$fair->loadsimple($ex->get('fair'), 'id');

		/* Check mail settings and send only if setting is set */
		if ($fair->wasLoaded()) {
			$mailSettings = json_decode($fair->get("mail_settings"));
			if (isset($mailSettings->PreliminaryToBooking) && is_array($mailSettings->PreliminaryToBooking)) {
				if (in_array("1", $mailSettings->PreliminaryToBooking)) {
					$user = new User();
					$user->load2($ex->get('user'), 'id');
			
					if ($fair->get('contact_name') == '')
					$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('windowtitle'));
					else
					$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('contact_name'));
					/* Prepare to send the mail */
					if ($user->get('contact_email') == '')
					$recipient = array($user->get('email'), $user->get('company'));
					else
					$recipient = array($user->get('contact_email'), $user->get('name'));
					/* UPDATED TO FIT MAILJET */
					$mail_user = new Mail();
					$mail_user->setTemplate('preliminary_to_booking_receipt');
					$mail_user->setFrom($from);
					$mail_user->setRecipient($recipient);
					/* Setting mail variables */
					$mail_user->setMailVar('exhibitor_company', $user->get('company'));
					$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
					$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
					$mail_user->setMailVar('event_email', $fair->get('contact_email'));
					$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
					$mail_user->setMailVar('event_website', $fair->get('website'));
					$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
					$mail_user->setMailVar('position_name', $pos->get('name'));
					$mail_user->setMailVar('position_area', $pos->get('area'));
					$mail_user->sendMessage();
				}
			}
		}
	}
	exit;
}

if (isset($_POST["getGridSettings"])) {
	$id = $_POST["getGridSettings"];
	
	$map = new FairMap();
	$map->load($id, 'id');

	echo $map->get("grid_settings");
}
if (isset($_POST["setGridSettings"])) {
	$id = $_POST["setGridSettings"];
	$settings = $_POST["gridSettings"];

	$map = new FairMap();
	$map->load($id, "id");
	$map->set("grid_settings", $settings);
	$map->save();
}
if (isset($_POST["saveToolboxPosition"])) {
	setcookie("gridtoolbox_position", $_POST["saveToolboxPosition"], time() + (3600 * 24 * 365));
}
if (isset($_POST["getToolboxPosition"])) {
	echo $_COOKIE["gridtoolbox_position"];
}
?>
