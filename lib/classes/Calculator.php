<?php
	/* 
		Calculator
		Ursprungligen skriven av : Andréas Forsbom
		Kontakta : andreas@trinax.se vid frågor
		2013-08-16

		Denna klass kan multiplikation, subtraktion, addition och division.
	*/
	class Calculator{
		/* 
			Denna metod adderar $a med $b
		*/
		public function add($a, $b){
			return floatval($a) + floatval($b);
		}

		/* 
			Denna metod subtraherar $a med $b
		*/
		public function sub($a, $b){
			return floatval($a) - floatval($b);
		}

		/* 
			Denna metod multiplicerar $a med $b
		*/
		public function multi($a, $b){
			return floatval($a) * floatval($b);
		}

		/* 
			Denna metod dividerar $a med $b
		*/
		public function div($a, $b){
			return floatval($a) / floatval($b);
		}

		/* Denna metod räknar ut kvadratmeter och priset för detta utrymme */
		public function m2price($a, $b, $pricePerUnit){
			return floatval($this->multi($a, $b)) * floatval($pricePerUnit);
		}
	}
?>
