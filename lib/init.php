<?php
define('APP_VERSION', '3.0.7');

//Display errors in dev mode
function setReporting() {
	if (DEV == true) {
		error_reporting(E_ALL);
		ini_set('display_errors','true');
	} else {
		error_reporting(0);
		ini_set('display_errors','false');
	}
}

//Main Call Function
function callHook() {
	global $url;

	$urlArray = array();
	$urlArray = explode("/",$url);

	$controller = ucfirst($urlArray[0]);

	if (array_key_exists(1, $urlArray) && $urlArray[1] != '')
		$action = $urlArray[1];
	else
		$action = 'index';

	$queryString = array();
	if (array_key_exists(2, $urlArray))
		$queryString = $urlArray[2];

	if ($controller == '')
		$controller = 'Start';

	$controllerName = strtolower($controller);

	$model = $controller;
	$controller .= 'Controller';

	//Special case
	if (!file_exists(ROOT.'application/controllers/'.$controller.'.php') && $controller != 'StartController') {
		$_SESSION['visitor'] = (isset($urlArray[1]) && $urlArray[1] == 'visitor');

		
		if (!isset($_COOKIE['language'])) {
			$controller = 'ChooseLangController';
			$action = "chooseLang";
			$model = "ChooseLang";
		} else {
			$countView = true;
			$controller = 'MapToolController';
			$action = 'map';
			$model = 'MapTool';

			$urlArray = array('', '', $urlArray[0]);
			if (strtolower($urlArray[2]) != 'favicon.ico'){
				/*echo '<pre>';
				print_r($urlArray);*/
				if (strtolower($urlArray[2]) != 'images')
					$_SESSION['outside_fair_url'] = strtolower($urlArray[2]);
			}
		}
	}

	// Make sure that signed in users can't use the system without terms approval!

	if (isset($_SESSION['user_id'])) {
		$me = new User;
		$me->load2($_SESSION['user_id'], 'id');
		if (!$_SESSION['user_terms_approved']) {
			$url = $urlArray[0] . '/' . $action;
			// Whitelist URLs that can be accessed without approved terms
			if (!in_array($url, array('user/terms', 'translate/language', 'user/confirm/*/*', 'user/logout'))) {
				header('Location: ' . BASE_URL . 'user/terms?next=' . $url);
				exit;
			}
		}

		if ($me->get('level') == 3 && !$_SESSION['user_pub_approved'] && $_SESSION['user_terms_approved']) {
			$url = $urlArray[0] . '/' . $action;
			// Whitelist URLs that can be accessed without approved terms
			if (!in_array($url, array('user/pub', 'translate/language', 'user/confirm/*/*', 'user/logout'))) {
				header('Location: ' . BASE_URL . 'user/pub?next=' . $url);
				exit;
			}
		}
	}


//}

	$dispatch = new $controller($model, $controllerName, $action);
	if (isset($countView)) {
		$stmt = $dispatch->db->prepare("UPDATE fair SET page_views = `page_views`+1 WHERE url = ?");
		$stmt->execute(array(strtolower($urlArray[2])));
	}

	if (method_exists($controller, $action)) {
		call_user_func_array(array($dispatch, $action), array_slice($urlArray, 2));
		$dispatch->render();
	} else {
		//Error handling
		throw new Exception('Action ' . $action . ' not found on ' . $controller, 404);
	}
}

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

define('TIMEZONE', 'GMT+1');
if (!defined("ENT_HTML5")) {
	define("ENT_HTML5", 48);
}

setReporting();

try {
	callHook();
} catch (Exception $ex) {

}
?>
