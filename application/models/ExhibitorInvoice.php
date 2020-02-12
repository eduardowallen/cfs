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
		error_log(var_dump($this));
		if ($this->wasLoaded()) {
			error_log('Invoice was loaded in FairInvoice.php on line 24.');
			$exhibitor = new Exhibitor();
			$exhibitor->load2($this->exhibitor, 'id');
			if ($exhibitor->wasLoaded()) {
				error_log('Exhibitor was loaded in FairInvoice.php on line 28.');
				$position = new FairMapPosition();
				$position->load2($exhibitor->get('position'), 'id');
				if ($position->wasLoaded()) {
					error_log('Position was loaded in FairInvoice.php on line 32.');
					$invoice_file = ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$this->exhibitor.'/'.$rec_billing_company_name . '-' . $position->get('name'). '-' . $this->id . '.pdf';
					rm($invoice_file);
					error_log('Invoice was removed in FairInvoiceController.php on line 35.');
					$stmt = $this->db->prepare("DELETE FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ?");
					$stmt->execute($this->id, $this->fair);
					$stmt = $this->db->prepare("DELETE FROM exhibitor_invoice WHERE row_id = ?");
					$stmt->execute($this->row_id);
				} else error_log("The position could not be loaded");
			} else error_log("The exhibitor could not be loaded");
		} else error_log("The file with row id ".$this->row_id." could not be deleted.");
	}
}

?>