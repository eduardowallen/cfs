<?php

//Display errors in dev mode
function setReporting() {
	if (DEV == true) {
		error_reporting(E_ALL);
		ini_set('display_errors','false');
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

	$dispatch = new $controller($model, $controllerName, $action);
	if (isset($countView)) {
		$stmt = $dispatch->db->prepare("UPDATE fair SET page_views = `page_views`+1 WHERE url = ?");
		$stmt->execute(array(strtolower($urlArray[2])));
	}

	if (method_exists($controller, $action)) {
		call_user_func_array(array($dispatch, $action), array_slice($urlArray, 2));
	} else {
		//Error handling
		throw new Exception("404");
	}
}

//Autoload any classes that are required
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

$lang = (isset($_COOKIE['language'])) ? $_COOKIE['language'] : 'eng';
define('LANGUAGE', $lang);
$translator = new Translator($lang);

$globalDB = new Database;
global $globalDB;

define('TIMEZONE', 'GMT' . getGMToffset());
if (!defined("ENT_HTML5")) {
	define("ENT_HTML5", 48);
}

setReporting();
callHook();
