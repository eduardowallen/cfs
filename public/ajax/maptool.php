<?php

$parts = explode('/', dirname(dirname(__FILE__)));
$parts = array_slice($parts, 0, -1);
define('ROOT', implode('/', $parts).'/');

session_start();
require_once ROOT.'config/config.php';
require_once ROOT.'lib/functions.php';
require_once ROOT.'lib/classes/Translator.php';

$globalDB = new Database;
global $globalDB;

function __autoload($className) {
	if (file_exists(ROOT.'lib/classes/'.$className.'.php')) {
		require_once(ROOT.'lib/classes/'.$className.'.php');

	} else if (file_exists(ROOT.'application/models/'.$className.'.php')) {
		require_once(ROOT.'application/models/'.$className.'.php');
	}
}

$lang = (isset($_COOKIE['language'])) ? $_COOKIE['language'] : 'eng';
define('LANGUAGE', $lang);

function userHasAccess() {
	
}

if (isset($_POST['markPositionAsBeingEdited'])) {
	$pos = new FairMapPosition;
	$pos->load($_POST['markPositionAsBeingEdited'], 'id');
	$pos->set('being_edited', $_SESSION['user_id']);
	$pos->set('edit_started', time());
	$pos->save();
	exit;
	
}

if (isset($_POST['markPositionAsNotBeingEdited'])) {
	$pos = new FairMapPosition;
	$pos->load($_POST['markPositionAsNotBeingEdited'], 'id');
	$pos->set('being_edited', 0);
	$pos->set('edit_started', 0);
	$pos->save();
	exit;
	
}

if (isset($_POST['getPreliminary'])) {
	
	$query = $globalDB->query("SELECT prel.*, user.* FROM preliminary_booking AS prel LEFT JOIN user ON prel.user = user.id WHERE position = '".$_POST['getPreliminary']."'");
	$result = $query->fetch(PDO::FETCH_ASSOC);
	
	$result['categories'] = explode('|', $result['categories']);
	
	die(json_encode($result));
	
}

if (isset($_POST['init'])) {

	$map = new FairMap;
	$map->load($_POST['init'], 'id');

	$prels = array();
	if (userLevel() == 1) {
		$user = new User;
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
		'fair'=> $map->get('fair'),
		'name'=>$map->get('name'),
		'image'=>$map->get('large_image'),
		'large_image'=>$map->get('large_image'),
		'positions'=>array()
	);

	if (userLevel() > 1) {
		// Prepare a SQL statement for getting the number of prelimnary bookings for a position
		$num_prel_booking_stmt = $globalDB->prepare("SELECT COUNT(*) AS cnt FROM preliminary_booking WHERE position = ?");
	}

	foreach ($map->get('positions') as $pos) {
		$ex = $pos->get('exhibitor');
		$cats = array();
		$num_prel = 0;
		$applied = 0;
		unset($ex->password);
		//unset($ex->commodity);
		if (is_object($ex)) {
			$ex->set('commodity', $ex->get('spot_commodity'));
			foreach ($ex->get('exhibitor_categories') as $cat) {
				$c = new ExhibitorCategory;
				$c->load($cat, 'id');
				if ($c->wasLoaded()) {
					$c->set('category_id', $cat);
					$cats[] = $c;
				}
			}
			
			$ex->set('categories', $cats);
			
		} else if (userLevel() > 1) {

			$applied = 0;
			if (userCanAdminFair($map->get('fair'), $map->get('id'))) {

				// Fetch any preliminary bookings for this position
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

		$length = array_push($ret["positions"], array(
			'id' => $pos->get('id'),
			'x' => $pos->get('x'),
			'y' => $pos->get('y'),
			'name' => $pos->get('name'),
			'area' => $pos->get('area'),
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

if (isset($_POST['pasteExhibitor'])) {
	
	if (!isset($_SESSION['copied_exhibitor'])) {
		exit;
	}

	$fair = new Fair;

	$pos = new FairMapPosition;
	$pos->load($_POST['pasteExhibitor'], 'id');
	$pos->set('status', 2);
	$pos->save();

	if (preg_match('/uid/', $_SESSION['copied_exhibitor'])) {
		$stmt = $fair->db->prepare("SELECT * FROM user WHERE id = ?");
		$stmt->execute(array(str_replace('uid_', '', $_SESSION['copied_exhibitor'])));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		$values = array($res['id'], $_SESSION['user_fair'], $_POST['pasteExhibitor'], $res['category'], $res['presentation'], $res['commodity'], '', time());
		print_r($values);
	} else {
		$stmt = $fair->db->prepare("SELECT * FROM exhibitor WHERE id = ?");
		$stmt->execute(array($_SESSION['copied_exhibitor']));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		$values = array($res['user'], $res['fair'], $_POST['pasteExhibitor'], $res['category'], $res['presentation'], $res['commodity'], $res['arranger_message'], time());
	}
	
	$sql = "INSERT INTO exhibitor (user, fair, position, category, presentation, commodity, arranger_message, booking_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
	$stmt = $fair->db->prepare($sql);
	$stmt->execute($values);

	unset($_SESSION['copied_exhibitor']);

	exit;
}

if (isset($_POST['deleteMarker'])) {

	if (userLevel() < 2)
		exit;

	$pos = new FairMapPosition;
	$pos->load($_POST['deleteMarker'], 'id');
	$pos->delete();
	exit;

}

if (isset($_POST['bookPosition'])) {

	if (userLevel() < 1)
		exit;

	$map = new FairMap();
	$map->load($_POST['map'], 'id');

	$pos = new FairMapPosition();
	$pos->load($_POST['bookPosition'], 'id');
	
	//Delete existing exhibitor if position is reserved
	if ($pos->get('status') > 0) {
		$ex = new Exhibitor;
		$ex->load($pos->get('id'), 'position');

		if ($ex->wasLoaded()) {
			$ex->delete();
		}
	}

	$ex = new Exhibitor;
	if (isset($_POST['user']) && userLevel() > 1)
		$ex->set('user', $_POST['user']);
	else
		$ex->set('user', $_SESSION['user_id']);
	
	$ex->set('position', $_POST['bookPosition']);
	$ex->set('map', $_POST['map']);
	$ex->set('fair', $map->get('fair'));
	$ex->set('commodity', $_POST['commodity']);
	$ex->set('arranger_message', $_POST['message']);
	$ex->set('category', 0);
	$ex->set('presentation', '');
	$ex->set('edit_time', 0);
	$exId = $ex->save();
	
	$stmt = $pos->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
	foreach ($_POST['categories'] as $cat) {
		$stmt->execute(array($exId, $cat));
	}
	
	$pos->set('status', 2);
	$pos->save();

	exit;

}

if (isset($_POST['reservePosition'])) {

	if (userLevel() < 1)
		exit;

	$map = new FairMap;
	$map->load($_POST['map'], 'id');

	$pos = new FairMapPosition;
	$pos->load($_POST['reservePosition'], 'id');
	
	//Delete existing exhibitor if position is booked
	if ($pos->get('status') > 0) {
		$ex = new Exhibitor;
		$ex->load($pos->get('id'), 'position');

		if ($ex->wasLoaded()) {
			$ex->delete();
		}
	}
	
	$ex = new Exhibitor;
	if (isset($_POST['user']) && userLevel() > 1)
		$ex->set('user', $_POST['user']);
	else
		$ex->set('user', $_SESSION['user_id']);
	$ex->set('position', $_POST['reservePosition']);
	$ex->set('map', $_POST['map']);
	$ex->set('fair', $map->get('fair'));
	$ex->set('commodity', $_POST['commodity']);
	$ex->set('arranger_message', $_POST['message']);
	$ex->set('category', 0);
	$ex->set('presentation', '');
	$ex->set('edit_time', 0);
	$exId = $ex->save();
	
	$stmt = $pos->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
	foreach ($_POST['categories'] as $cat) {
		$stmt->execute(array($exId, $cat));
	}
	
	$pos->set('status', 1);
	$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));
	$pos->save();

	exit;

}

if (isset($_POST['editBooking'])) {
	
	if (userLevel() < 1)
		exit;
	
	$map = new FairMap();
	$map->load($_POST['map'], 'id');

	$pos = new FairMapPosition();
	$pos->load($_POST['editBooking'], 'id');

	$fair = new Fair();
	$fair->load($map->get('fair'), 'id');

	$ex = new Exhibitor;
	$ex->load($_POST['exhibitor_id'], 'id');
	if (!$ex->wasLoaded()) {
		die('exhibitor not found');
	}

	$organizer = new User();
	$organizer->load($fair->get('created_by'), 'id');

	if (isset($_POST['user']) && userLevel() > 1)
		$ex->set('user', $_POST['user']);
	else
		$ex->set('user', $_SESSION['user_id']);

	//$ex->set('position', $_POST['editBooking']);
	//$ex->set('map', $_POST['map']);
	//$ex->set('fair', $map->get('fair'));
	$ex->set('commodity', $_POST['commodity']);
	$ex->set('arranger_message', $_POST['message']);
	//$ex->set('category', 0);
	//$ex->set('presentation', '');
	
	$exId = $ex->save();
	
	$map->db->query("DELETE FROM exhibitor_category_rel WHERE exhibitor = '".intval($_POST['exhibitor_id'])."'");
	
	$stmt = $pos->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
	foreach ($_POST['categories'] as $cat) {
		$stmt->execute(array($exId, $cat));
	}
	
	//$pos->set('status', 1);
	if (isset($_POST['expires'])) {
		$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));
		$pos->save();
		$mail_type = 'reservation';

	} else {
		$mail_type = 'booking';
	}

	$categories = array();
	foreach ($_POST['categories'] as $category_id) {
		$ex_category = new ExhibitorCategory();
		$ex_category->load($category_id, 'id');
		$categories[] = $ex_category->get('name');
	}

	$categories = implode(', ', $categories);
	$time_now = date('d-m-Y H:i');

	$mail_organizer = new Mail($organizer->get('email'), $mail_type . '_edited_confirm');
	$mail_organizer->setMailVar('position_name', $pos->get('name'));
	$mail_organizer->setMailVar('position_information', $pos->get('information'));
	$mail_organizer->setMailVar('edit_time', $time_now);
	$mail_organizer->setMailVar('arranger_message', $_POST['message']);
	$mail_organizer->setMailVar('exhibitor_commodity', $_POST['commodity']);
	$mail_organizer->setMailVar('exhibitor_category', $categories);

	if ($mail_type == 'reservation') {
		$mail_organizer->setMailVar('date_expires', $_POST['expires']);
	}

	$mail_organizer->send();

	$mail_user = new Mail($ex->get('email'), $mail_type . '_edited_receipt');
	$mail_user->setMailVar('position_name', $pos->get('name'));
	$mail_user->setMailVar('position_information', $pos->get('information'));
	$mail_user->setMailVar('edit_time', $time_now);
	$mail_user->setMailVar('arranger_message', $_POST['message']);
	$mail_user->setMailVar('exhibitor_commodity', $_POST['commodity']);
	$mail_user->setMailVar('exhibitor_category', $categories);

	if ($mail_type == 'reservation') {
		$mail_user->setMailVar('date_expires', $_POST['expires']);
	}

	$mail_user->send();

	exit;
	
}

if (isset($_POST['preliminary'])) {
	
	if (userLevel() == 1) {

		$fair = new Fair();
		$fair->load($_SESSION['user_fair'], 'id');

		$user = new User();
		$user->load($_SESSION['user_id'], 'id');

		$organizer = new User();
		$organizer->load($fair->get('created_by'), 'id');

		if ($fair->wasLoaded() && $user->wasLoaded()) {
			foreach ($_POST['preliminary'] as $index=>$prel) {

				$position = new FairMapPosition();
				$position->load($prel, 'id');

				$pb = new PreliminaryBooking();
				$pb->set('user', $user->get('id'));
				$pb->set('fair', $fair->get('id'));
				$pb->set('position', $position->get('id'));
				$pb->set('categories', implode('|', $_POST['categories'][$index]));
				$pb->set('commodity', $_POST['commodity'][$index]);
				$pb->set('arranger_message', $_POST['message'][$index]);
				$pb->set('booking_time', time());
				$pb->save();

				$time_now = date('d-m-Y H:i');

				$categories = array();
				foreach ($_POST['categories'][$index] as $category_id) {
					$ex_category = new ExhibitorCategory();
					$ex_category->load($category_id, 'id');
					$categories[] = $ex_category->get('name');
				}

				$categories = implode(', ', $categories);

				$mail_organizer = new Mail($organizer->get('email'), 'new_preliminary_booking');
				$mail_organizer->setMailVar('position_name', $position->get('name'));
				$mail_organizer->setMailVar('position_information', $position->get('information'));
				$mail_organizer->setMailVar('booking_time', $time_now);
				$mail_organizer->setMailVar('arranger_message', $_POST['message'][$index]);
				$mail_organizer->setMailVar('exhibitor_commodity', $_POST['commodity'][$index]);
				$mail_organizer->setMailVar('exhibitor_category', $categories);
				$mail_organizer->setMailVar('exhibitor_name', $user->get('name'));
				$mail_organizer->send();

				$mail_user = new Mail($user->get('email'), 'receipt_preliminary_booking');
				$mail_user->setMailVar('position_name', $position->get('name'));
				$mail_user->setMailVar('position_information', $position->get('information'));
				$mail_user->setMailVar('booking_time', $time_now);
				$mail_user->setMailVar('arranger_message', $_POST['message'][$index]);
				$mail_user->setMailVar('exhibitor_commodity', $_POST['commodity'][$index]);
				$mail_user->setMailVar('exhibitor_category', $categories);
				$mail_user->setMailVar('exhibitor_name', $user->get('name'));
				$mail_user->send();
			}
		}
	}

	exit;
}

if (isset($_POST['cancelPreliminary'])) {

	if (userLevel() == 1) {

		$pb = new PreliminaryBooking;
		$stmt = $pb->db->prepare("DELETE FROM preliminary_booking WHERE user = ? AND position = ?");
		$stmt->execute(array($_SESSION['user_id'], $_POST['cancelPreliminary']));

	}

	exit;

}

if (isset($_POST['cancelBooking'])) {

	if (userLevel() > 1) {

		$pos = new FairMapPosition($db);
		$pos->load($_POST['cancelBooking'], 'id');
		$pos->set('status', 0);
		$pos->save();

		$stmt = $pos->db->prepare("DELETE FROM exhibitor WHERE position = ?");
		$stmt->execute(array($_POST['cancelBooking']));

	}

	exit;

}

if (isset($_POST['savePosition'])) {

	if (userLevel() < 2)
		exit;

	$pos = new FairMapPosition;
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
	$pos->set('information', $_POST['information']);
	$pos->save();

	exit;

}

if (isset($_POST['movePosition'])) {

	$pos = new FairMapPosition;
	$pos->load((int)$_POST['movePosition'], 'id');
	$pos->set('x', $_POST['x']);
	$pos->set('y', $_POST['y']);
	$pos->save();

}

if (isset($_POST['getUserCommodity'])) {
	if (userLevel() < 1) {
		exit;
	}

	$user = new User;
	$user->load((int)$_POST['userId'], 'id');
	$answer = array('commodity' => '');
	if ($user->wasLoaded()) {
		$answer['commodity'] = $user->get('commodity');
	}
	echo json_encode($answer);
	exit;
}

if (isset($_POST['emailExists'])) {
	$user = new User;
	$user->load($_POST['email'], 'email');
	echo json_encode(array('emailExists' => $user->wasLoaded()));	
	exit;
}

if (isset($_POST['connectToFair'])) {
	$trans = new Translator((isset($_COOKIE['language'])) ? $_COOKIE['language'] : 'eng');
	$response = array();
	if (isset($_SESSION['user_id']) && !userIsConnectedTo($_POST['fairId'])) {
		$sql = "INSERT INTO `fair_user_relation`(`fair`, `user`, `connected_time`) VALUES (?,?,?)";
		$stmt = $globalDB->prepare($sql);
		$stmt->execute(array($_POST['fairId'], $_SESSION['user_id'], time()));
		$fair = new Fair;
		$fair->load($_POST['fairId'], 'id');
		$response['message'] = $trans->{'Connected to fair'}.' '.$fair->get('name');
		$response['success'] = true;
	} else {
		$response['message'] = $trans->{'Unable to connect to fair.'};
		$response['success'] = false;
	}
	echo json_encode($response);
}

if(isset($_POST['makeComment'])){
	session_start();
	$comment = new Comment;
	$me = new User;
	$me->load($_SESSION['user_id'], 'id');
	$author = $me->get('name');
	$dt = $comment->set($_POST['fair'], $_POST['exhibitor'], $author, date('Y-m-d H:i:s'), $_POST['text'], $_POST['position']);
	echo $dt;
}

if(isset($_POST['getComment'])){
	$comment = new Comment;
	$data = $comment->load($_POST['fair'], $_POST['exhibitor'], $_POST['position']);?>
	<ul>
	<?php foreach($data as $comments): ?>
		<li>
			<div class="comment">
			<ul>
				<li><?php echo $comments['author']?> (<?php echo $comments['date']?>) :</li>
			</ul>
			<ul>
				<li><?php echo $comments['comment']?></li>
			</ul>
		</li>
	<?php endforeach; ?>
	</ul>
	<?php
}

if (isset($_GET['prel_bookings_list'], $_GET['position'])) {

	$position = new FairMapPosition();
	$position->load($_GET['position'], 'id');

	if ($position->wasLoaded()) {

		$fair_map = new FairMap();
		$fair_map->load($position->get('map'), 'id');

		if ($fair_map->wasLoaded() && userCanAdminFair($fair_map->get('fair'), $fair_map->get('id'))) {

			$stmt = $globalDB->prepare("SELECT id, user, categories, position, commodity, arranger_message, booking_time FROM preliminary_booking WHERE position = ?");
			$stmt->execute(array($position->get('id')));
			$result = array();

			foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $prel_booking) {
				$stmt2 = $globalDB->prepare("SELECT `name`, `area` FROM `fair_map_position` WHERE `id` = ? LIMIT 0, 1");
				$stmt2->execute(array($prel_booking->position));
				$position = $stmt2->fetch(PDO::FETCH_ASSOC);

				$user = new User();
				$user->load($prel_booking->user, 'id');
				$prel_booking->company = $user->get('company');
				$prel_booking->booking_time = date('d-m-Y H:i', $prel_booking->booking_time) . ' UTC';
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

if (isset($_POST['approve_preliminary'])) {

	$prel_booking = new PreliminaryBooking();
	$prel_booking->load($_POST['approve_preliminary'], 'id');

	if ($prel_booking->wasLoaded()) {

		$position = new FairMapPosition();
		$position->load($prel_booking->get('position'), 'id');

		$exhibitor = new Exhibitor();
		$exhibitor->set('user', $_POST['user']);
		$exhibitor->set('fair', $prel_booking->get('fair'));
		$exhibitor->set('position', $position->get('id'));
		$exhibitor->set('category', 0);
		$exhibitor->set('presentation', '');
		$exhibitor->set('commodity', $_POST['commodity']);
		$exhibitor->set('arranger_message', $_POST['message']);
		$exhibitor->set('edit_time', time());
		$exhibitor_id = $exhibitor->save();
		
		$stmt = $position->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
		foreach ($_POST['categories'] as $cat) {
			$stmt->execute(array($exhibitor_id, $cat));
		}
		
		$position->set('status', 1);
		$position->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));
		$position->save();

		$prel_booking->delete();
	}
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
