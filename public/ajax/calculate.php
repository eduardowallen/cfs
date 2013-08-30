<?php
	/* 
	
	Denna fil är utformad för att anropas med
	AJAX via maptool för omvandla valuta, 
	få ut pris per kvm och liknande funktioner 

	Ursprungligen skriven av : Andréas Forsbom
	Kontakta : andreas@trinax.se vid frågor
	2013-08-16

	*/

	
	/*
		Skicka en POST-request och skicka med getKvm=1 för att köra denna funktion
	*/
error_reporting(E_ALL);
ini_set('display_errors','true');
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

	if(isset($_POST['getKvm'])):
		/*
			Skicka med X och Y i POST-requesten,
			X skall motsvara bredd för en plats
			Y skall motsvara "höjd" för en plats
		*/
		if(isset($_POST['x']) && isset($_POST['y'])):
			$x = floatval($_POST['x']);
			$y = floatval($_POST['y']);
			$calculator = new Calculator;
			$kvm = $calculator->multi($x, $y);
			echo $kvm;
		else:	// Skriv ut felmeddelande om X och Y inte är satta.
			echo 'Err: Ange X och Y värde';
		endif;
	endif;


	/*
		Skicka en POST-request och skicka med getKvmPrice=1 för att köra denna funktion
	*/
	if(isset($_POST['getKvmPrice'])):
		/*
			Skicka med X, Y och PRICE i POST-requesten,
			X skall motsvara bredd för en plats
			Y skall motsvara "höjd" för en plats
			Price skall motsvara priset per kvm
		*/
		if(isset($_POST['x']) && isset($_POST['y']) && isset($_POST['price'])):
			$x = floatval($_POST['x']);
			$y = floatval($_POST['y']);
			$price = floatval($_POST['price']);
			$calculator = new Calculator;
			$kvm = $calculator->m2price($x, $y, $price);
			echo $kvm;
		else:	// Skriv ut felmeddelande om X, Y Price inte är satta.
			echo 'Err: Ange X, Y och Pris';
		endif;
	endif;

	/*
		Skicka en POST-request och skicka med getConverted=1 för att köra denna funktion
	*/
	if(isset($_POST['getConverted'])):
		
		/*
			Skicka med from, to och amount i POST-requesten,
		*/
		if(isset($_POST['from']) && isset($_POST['to']) && isset($_POST['amount'])):
			$from = $_POST['from'];
			$to = $_POST['to'];
			$amount = floatval($_POST['amount']);
			$calculator = new CurrencyCalculator;
			$converted = $calculator->convertValue($from, $to, $amount);
			echo $converted;
		else:	// Skriv ut felmeddelande om X, Y och amount inte är satta.
			echo 'Err: Ange valutan du ska konvertera från, ange valutan du ska konvertera till, ange hur mycket du vill konvertera';
		endif;
	endif;

	/*
		Skicka en POST-request och skicka med multi=1 för att köra denna funktion!
	*/
	if(isset($_POST['multi'])):
		/*
			Skicka med antal & pris i POST-requesten,
		*/
		if(isset($_POST['antal']) && isset($_POST['pris'])):
			$pris = $_POST['pris'];

			$amount = floatval($_POST['antal'] * $_POST['pris']);
			echo $amount;
		else: // Skriv ut felmeddelande om X, Y och amount inte är satta.
			echo 'Ange antal & pris!';
		endif;
	endif;

	/*
		Skicka en POST-request och skicka med sum = 1 för att köra denna funktion!
	*/
	if(isset($_POST['sum'])):
		/*
			Skicka med tal1 och tal2 i POST-requesten,
		*/
		if(isset($_POST['tal1']) && isset($_POST['tal2'])):
			$tal1 = $_POST['tal1'];
			$tal2 = $_POST['tal2'];
	
			$amount = floatval($_POST['antal'] + $_POST['pris']);
			echo $amount;
		else: // Skriv ut felmeddelande om X, Y och amount inte är satta.
			echo 'Ange  tal1 och tal2!';
		endif;
	endif;
?>