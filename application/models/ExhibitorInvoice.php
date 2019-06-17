<?php

class ExhibitorInvoice extends Model {
	
		public function load($key, $by) {
		$stmt = $this->db->prepare("SELECT * FROM ".$this->table_name." WHERE `".$by."` = ? ORDER BY `created` DESC LIMIT 1");
		$stmt->execute(array($key));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($res > 0) {

			foreach ($res as $property=>$value) {
				$this->$property = $value;
				$this->db_keys[] = $property;
			}

			$this->loaded = true;
			return true;
		} else {
			$this->loaded = false;
			return false;
		}
	}
	
}

?>