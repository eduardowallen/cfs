<?php
	/* Denna klass är till för att räkna ut valutor... */
	class CurrencyCalculator{
		/* 

		Usage: $convertedValue = convertValues($amount, $from, $to);

		$amount = Detta är värdet du vill omvandla
		$from = Detta är valutan du vill omvandla från
		$to = Detta är valutan du vill omvandla till

		*/

		public static function convertValues($amount, $from, $to){
			/* Hämta information med cURL */
			$requestUrl = "http://www.google.com/ig/calculator?hl=en&q=".$amount."".$from."=?".$to;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $requestUrl);
			$data = curl_exec($ch);
			/* Hämta information med cURL */

			/* Plocka ut relevant information */
			$response = utf8_encode($data);
			echo json_decode($response);
		
		}
	}
?>
