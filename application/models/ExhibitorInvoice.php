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
	public function perm_delete() {
		// USE THIS FUNCTION WITH CAUTION!
		// This function does not take into consideration other files in the same folder as it removes all records related to the invoices from the database permanently.
		// Must be completed with if-else statements regarding the issue above.
		if ($this->wasLoaded()) {
			//error_log('Invoice was loaded in FairInvoice.php on line 27.');
			$stmt = $this->db->prepare("SELECT text FROM exhibitor_invoice_rel WHERE `invoice` = ? AND `fair` = ? AND type = 'space'");
			$stmt->execute(array($this->id, $this->fair));
			$invoiceposname = $stmt->fetch(PDO::FETCH_ASSOC);
			$invoice_file = '';
			$invoice_file .= ROOT.'public/invoices/fairs/'.$this->fair.'/exhibitors/'.$this->exhibitor.'/'.str_replace('/', '-', $this->r_name) . '-' . $invoiceposname['text']. '-' . $this->id . '.pdf';
			if (file_exists($invoice_file))
				unlink($invoice_file);
			else error_log('Invoice path '.$invoice_file.' does not exist in FairInvoiceController.php on line 35.');
			$stmt = $this->db->prepare("DELETE FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ?");
			$stmt->execute(array($this->id, $this->fair));
			$stmt = $this->db->prepare("DELETE FROM exhibitor_invoice WHERE row_id = ?");
			$stmt->execute(array($this->row_id));
		}
	}
}

?>