<?php

$parts = explode('/', dirname(dirname(__FILE__)));
$parts = array_slice($parts, 0, -1);
define('ROOT', implode('/', $parts).'/');

session_start();
require_once ROOT.'config/config.php';
require_once ROOT.'lib/functions.php';

$lang = (isset($_COOKIE['language'])) ? $_COOKIE['language'] : 'eng';
define('LANGUAGE', $lang);
$translator = new Translator($lang);

$globalDB = new Database;
global $globalDB;

function __autoload($className) {
	if (file_exists(ROOT.'lib/classes/'.$className.'.php')) {
		require_once(ROOT.'lib/classes/'.$className.'.php');

	} else if (file_exists(ROOT.'application/models/'.$className.'.php')) {
		require_once(ROOT.'application/models/'.$className.'.php');
	}
}

if (isset($_POST['findUser'])) {
	if (userLevel() != 4) {
		exit;
	}

	$stmt = $globalDB->prepare("SELECT `id`, `company`, `orgnr`, `name`, `contact_phone2`, `email`, `last_login`, `created` FROM `user` WHERE `company` LIKE '%?'");
	$stmt->execute(array($_POST['findUser']));
	$stmt->fetchAll(PDO::FETCH_OBJ);
	var $response = $stmt;

	echo json_encode($response);
/*
	$response = array();

	foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $user) {

		$stmt2 = $globalDB->prepare("SELECT COUNT( fair ) AS fair_count FROM fair_user_relation WHERE user = ?");
		$stmt2->execute(array($user['id']));
		$result = $stmt2->fetchAll(PDO::FETCH_ASSOC);
		$exhibitors = array();
		$fairs = 0;
		$currentFair = 0;
		$counter = array();
		foreach ($result as $res) {
			if (intval($res['id']) > 0) {
				$ex = new User;
				$ex->load($res['id'], 'id');
				//$ex->set('ex_count', $res['ex_count']);
				
				$stmt3 = $this->Exhibitor->db->prepare("SELECT COUNT(fair) AS fair_count FROM fair_user_relation WHERE user = ?");
				$stmt3->execute(array($res['id']));
				$result2 = $stmt3->fetch(PDO::FETCH_ASSOC);
				$ex->set('fair_count', $result2['fair_count']);
				
				$exhibitors[] = $ex;
				if ($res['fair'] != $currentFair) {
					$fairs++;
					$currentFair = $res['fair'];
				}
				if (array_key_exists($res['id'], $counter))
					$counter[$res['id']] += 1;
				else
					$counter[$res['id']] = 1;
			}
		}
		
		$unique = array();
		for ($i=0; $i<count($exhibitors); $i++) {
			$exhibitors[$i]->set('ex_count', $counter[$exhibitors[$i]->get('id')]);
			if (!array_key_exists($exhibitors[$i]->get('id'), $unique))
				$unique[$exhibitors[$i]->get('id')] = $exhibitors[$i];
		}
	}
*/

	//echo json_encode($answer);
	exit;
}
