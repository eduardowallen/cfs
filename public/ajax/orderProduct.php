<?php
	/**
		This file takes the selected products and stores them inside a temporary database table
	***/
	error_reporting(E_ALL);
	ini_set('display_errors','true');

	$parts = explode('/', dirname(dirname(__FILE__)));
	$parts = array_slice($parts, 0, -1);
	define('ROOT', implode('/', $parts).'/');

	session_start();
	require_once ROOT.'config/config.php';
	require_once ROOT.'lib/functions.php';
	require_once ROOT.'lib/classes/Translator.php';

	spl_autoload_register(function ($className) {
		if (file_exists(ROOT.'lib/classes/'.$className.'.php')) {
			require_once(ROOT.'lib/classes/'.$className.'.php');

		} else if (file_exists(ROOT.'application/models/'.$className.'.php')) {
			require_once(ROOT.'application/models/'.$className.'.php');
		}
	});

	$globalDB = new Database;
	global $globalDB;

	$article = $_POST['article'];
	$amount = $_POST['amount'];
	$fair = $_POST['fair'];
	$position = $_POST['position'];
	$level = $_POST['level'];

	$statement = $globalDB->prepare('SELECT * FROM exhibitor_orders WHERE fair = ? AND exhibitor = ? AND article = ? AND level = ?');
	$statement->execute(array($fair, $position, $article, $level));
	$result = $statement->fetchAll();

	if(count($result) > 0){
		$statement = $globalDB->prepare('UPDATE exhibitor_orders SET amount = ? WHERE fair = ? AND exhibitor = ? AND article = ? AND level = ?');
		$statement->execute(array($amount, $fair, $position, $article, $level));
	} else {
		$statement = $globalDB->prepare('INSERT INTO exhibitor_orders(fair, exhibitor, article, amount, level) VALUE(?, ?, ?, ?, ?)');
		$statement->execute(array($fair, $position, $article, $amount, $level));
	}
?>
