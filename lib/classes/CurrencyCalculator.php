<?php
	/* 
		CurrencyCalculator
		Urspringligen skriven av : Andréas Forsbom
		Kontakta : andreas@trinax.se vid frågor
		2013-08-06

		Denna klass möjliggör konvertering från en valuta till en annan
	*/

	class CurrencyCalculator{
		private $values = array('SEK', 'USD', 'EUR', 'GBP', 'PEN'); // Detta är valutorna som är kompatibla med klassen, lägg till fler här för att kunna konvertera flera valutor.
		private $db;

		/*
			Klassens konstruktor:

			Denna konstruktorn hämtar de nuvarande valutavärdena 
			från databasen och ser efter om datumet är dagens datum..
			Är det inte samma datum så anropar den metoden valueLoop...
		*/
		public function __construct(){
			global $globalDB;
			$this->db = $globalDB;

			$date = date('Y-m-d');
			$storedDate = $this->getDateOfStoredValues();
			if(!empty($storedDate)): // Om ett datum finns i databasen
				if($storedDate != $date): // Om datumet inte stämmer med dagens datum
					$this->valueLoop();
				endif;
			else: // Om ett datum icke finns i databasen
				$this->valueLoop();
			endif;
		}

		/*
			private function getDateOfStoredValues();

			Denna metod hämtar ut datumet då valutorna senast sparades i databasen.
		*/
		private function getDateOfStoredValues(){
			$statement = $this->db->prepare('SELECT DISTINCT lastupdate FROM currencies');
			$statement->execute();
			$date = $statement->fetch();
			return $date['lastupdate'];
		}

		/*
			private function valueLoop()

			Denna metod itererar igenom varje valuta som finns i klassen
			för att sedan anropa storeValues, detta för att konvertera alla valutor
			till alla valutor.
		*/
		private function valueLoop(){
			foreach($this->values as $from):
				foreach($this->values as $to):
					if($from != $to):
						$this->storeValues($from, $to);
					endif;
				endforeach;
			endforeach;
		}

		/*
			private function storeValues($from, $to);

			Denna metod anropas endast inom klassen, den anropas från denna klassens konstruktor...
			Den hämtar det nuvarande valutavärdet med hjälp av cURL och sparar sedan ner det i databasen.
		*/
		private function storeValues($from, $to){
			/* Hämta information med cURL */
			$requestUrl = "http://www.google.com/ig/calculator?hl=en&q=1".$from."=?".$to;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $requestUrl);
			$data = curl_exec($ch);
			/* Hämta information med cURL */

			/* Omvandla google's trasiga JSON sträng till en giltig JSON sträng */
			$response =  str_replace('lhs', '"lhs"', $data);
			$response =  str_replace('rhs', '"rhs"', $response);
			$response =  str_replace('icc', '"icc"', $response);
			$response =  str_replace('error', '"error"', $response);

			/* Omvandla JSON strängen till en array */
			$response = json_decode($response, true);

			/* Fixa till valutans värde */
			$response = explode(" ", $response['rhs']);
			$response = explode(".", $response[0]);
			$value = $response[0].'.'.substr($response[1], 0, 4);

			/* Spara värdet till databasen */
			$statement = $this->db->prepare('SELECT * FROM currencies WHERE `from` = ? AND `to` = ?');
			$statement->execute(array($from, $to));
			$data = $statement->fetchAll();

			if(!empty($data)) :
				$statement = $this->db->prepare('UPDATE currencies SET value = ?, lastupdate = ? WHERE `from` = ? and `to` = ?');
				$statement->execute(array($value, $from, $to, date('Y-m-d')));
			else:
				$statement = $this->db->prepare('INSERT INTO currencies(`from`, `to` , value, lastupdate) VALUES(?, ?, ?, ?)');
				$statement->execute(array($from, $to, $value, date('Y-m-d')));
			endif;
		}

		/*
			$value = fetchValue($from, $to);

			Denna metoden hämtar valutans värde från 
			databasen.
		*/
		private function fetchValue($from, $to){
			$statement = $this->db->prepare("SELECT value FROM currencies WHERE `from`= ? AND `to`= ?");
			$statement->execute(array($from, $to));
			$value = $statement->fetch();
			return floatval($value['value']);
		}

		/*
			$convertedValue = convertValue($value, $from, $to);

			Omvandla en valuta till en annan, 
			valutorna som stöds ser du i klassens privata array $values

			Ex: omvandla 100 SEK till USD

			$currencyConverter = new CurrencyCalculator();
			$usdfromsek = $currencyConverter->convertValue('100',);

		*/
		public function convertValue($value, $from, $to){
			$toWorth = $this->fetchValue($from, $to);
			return floatval(floatval($toWorth) * floatval($value));
		}
	}
?>
