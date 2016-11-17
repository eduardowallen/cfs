<?php

class ExhibitorInvoiceCredited extends Model {
	
	public function loadids($key, $by) {
		$stmt = $this->db->prepare("SELECT `id`, `cid` FROM exhibitor_invoice_credited WHERE `".$by."` = ?");
		//echo "SELECT * FROM ".$this->table_name." WHERE `".$by."` = ".$key;
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