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

	foreach ($map->get('positions') as $pos) {
		$ex = $pos->get('exhibitor');
		$cats = array();
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
			
		}
		
		if (in_array($pos->get('id'), $prels)) {
			$applied = 1;
		} else {
			$applied = 0;
		}

		$ret['positions'][] = array(
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
			'being_edited' => $pos->get('being_edited'),
			'edit_started' => $pos->get('edit_started')
		);
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

	$map = new FairMap;
	$map->load($_POST['map'], 'id');

	$pos = new FairMapPosition;
	$pos->load($_POST['bookPosition'], 'id');
	
	//Delete existing exhibitor if position is reserved
	if ($pos->get('status') > 0) {
		$stmt = $pos->db->prepare("DELETE FROM exhibitor WHERE position = ?");
		$stmt->execute(array($pos->get('id')));
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
		$stmt = $pos->db->prepare("DELETE FROM exhibitor WHERE position = ?");
		$stmt->execute(array($pos->get('id')));
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
	
	$map = new FairMap;
	$map->load($_POST['map'], 'id');

	$pos = new FairMapPosition;
	$pos->load($_POST['editBooking'], 'id');

	$ex = new Exhibitor;
	$ex->load($_POST['exhibitor_id'], 'id');
	if (!$ex->wasLoaded()) {
		die('exhibitor not found');
	}
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
	}

	exit;
	
}

if (isset($_POST['preliminary'])) {
	
	if (userLevel() == 1) {
		
		foreach ($_POST['preliminary'] as $index=>$prel) {
			
			$pb = new PreliminaryBooking;
			$pb->set('user', $_SESSION['user_id']);
			$pb->set('fair', $_SESSION['user_fair']);
			$pb->set('position', $prel);
			$pb->set('categories', implode('|', $_POST['categories'][$index]));
			$pb->set('commodity', $_POST['commodity'][$index]);
			$pb->set('arranger_message', $_POST['message'][$index]);
			$pb->set('booking_time', time());
			$pb->save();
			
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
?>
