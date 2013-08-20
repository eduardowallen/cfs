<?php
$parts = explode('/', dirname(dirname(__FILE__)));
$parts = array_slice($parts, 0, -1);
define('ROOT', implode('/', $parts).'/');

session_start();
require_once ROOT.'config/config.php';
require_once ROOT.'lib/functions.php';

$globalDB = new Database;
global $globalDB;

function __autoload($className) {
	if (file_exists(ROOT.'lib/classes/'.$className.'.php')) {
		require_once(ROOT.'lib/classes/'.$className.'.php');
		
	} else if (file_exists(ROOT.'application/models/'.$className.'.php')) {
		require_once(ROOT.'application/models/'.$className.'.php');
	}
}

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
	
}
?>