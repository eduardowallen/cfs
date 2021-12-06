<?php
$parts = explode('/', dirname(dirname(__FILE__)));
$parts = array_slice($parts, 0, -1);
define('ROOT', implode('/', $parts).'/');

session_start();
require_once ROOT.'lib/classes/Database.php';
require_once ROOT.'config/config.php';
require_once ROOT.'lib/functions.php';

$globalDB = new Database;
global $globalDB;

$lang = (isset($_COOKIE['language'])) ? $_COOKIE['language'] : 'eng';
define('LANGUAGE', $lang);

spl_autoload_register(function ($className) {
	if (file_exists(ROOT.'lib/classes/'.$className.'.php')) {
		require_once(ROOT.'lib/classes/'.$className.'.php');
		
	} else if (file_exists(ROOT.'application/models/'.$className.'.php')) {
		require_once(ROOT.'application/models/'.$className.'.php');
	}
});

if (isset($_POST['ajaxContent'])) {
	
	switch($_POST['ajaxContent']) {
		
		case 'help-0':
			$stmt = $globalDB->prepare("SELECT * FROM page_content WHERE page = ? AND language = ?");
			$stmt->execute(array('help', LANGUAGE));
			$pageContent = $stmt->fetch(PDO::FETCH_ASSOC);
			$content = $pageContent['content'];
			break;
			
		case 'contact-0':
			$stmt = $globalDB->prepare("SELECT email, contact_info AS content FROM fair WHERE url = ?");
			$stmt->execute(array($_SESSION['outside_fair_url']));
			$pageContent = $stmt->fetch(PDO::FETCH_ASSOC);
			$content = '<h3><a href="mailto:'.$pageContent['email'].'">'.$pageContent['email'].'</a></h3>'.$pageContent['content'];
			break;
			
		default:
			$content = '';
			break;
		
	}
	
	echo $content;
	exit;
	
}
?>