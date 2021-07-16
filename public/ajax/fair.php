<?php
$parts = explode('/', dirname(dirname(__FILE__)));
$parts = array_slice($parts, 0, -1);
define('ROOT', implode('/', $parts).'/');

session_start();
require_once ROOT.'config/config.php';
require_once ROOT.'lib/functions.php';

$globalDB = new Database;
global $globalDB;

spl_autoload_register(function ($className) {
	if (file_exists(ROOT.'lib/classes/'.$className.'.php')) {
		require_once(ROOT.'lib/classes/'.$className.'.php');
		
	} else if (file_exists(ROOT.'application/models/'.$className.'.php')) {
		require_once(ROOT.'application/models/'.$className.'.php');
	}
});
if (isset($_POST['checkName'])) {
	
	$user = new User;
	$user->load($_SESSION['user_id'], 'id');
	
	$url = makeUrl($_POST['checkName']);
	
	$stmt = $user->db->prepare("SELECT id FROM fair WHERE url = ? LIMIT 1");
	$stmt->execute(array($url));
	$result = $stmt->fetchAll();
	if ($result) {
		echo json_encode(array('url'=>$url, 'status'=>'conflict'));
	} else {
		echo json_encode(array('url'=>$url, 'status'=>'ok'));
	}
	
} else if (!empty($_POST["newOption"])) {
	$fairOption = new FairExtraOption();

	$fairOption->set("text", $_POST["value"]);
	$fairOption->set("fair", $_POST["newOption"]);
	$fairOption->save();

	echo json_encode(array("id" => $fairOption->db->lastInsertId()));

} else if (!empty($_POST["deleteOption"])) {
	$fairOption = new FairExtraOption();
	$fairOption->load($_POST["deleteOption"], "id");

	$fairOption->delete();

} else if (!empty($_POST["saveOption"])) {
	$fairOption = new FairExtraOption();
	$fairOption->load($_POST["saveOption"], "id");

	$fairOption->set("text", $_POST["value"]);
	$fairOption->save();
}
if (isset($_POST['getDefaultReservationDate'])) {
	$fair = new Fair();
	$fair->loadsimple($_SESSION['user_fair'], 'id');
	if ($fair->wasLoaded()) {
		echo json_encode(date('d-m-Y H:i', $fair->get('default_reservation_date')));
	}
}
?>