<?php

class FairInvoice extends Model {
	

	public function save() {
		
		if ($this->wasLoaded()) {
			$sql = "UPDATE fair_invoice SET reference = ?, company_name = ?, address = ?, zipcode = ?, city = ?, country = ?, orgnr = ?, bank_no = ?, postgiro = ?, vat_no = ?, iban_no = ?, swift_no = ?, swish_no = ?, phone = ?, invoice_id_start = ?, credit_invoice_id_start = ?, pos_vat = ?, default_expirationdate = ?, website = ?, email = ? WHERE fair = ?";
			$params = array($this->reference, $this->company_name, $this->address, $this->zipcode, $this->city, $this->country, $this->orgnr, $this->bank_no, $this->postgiro, $this->vat_no, $this->iban_no, $this->swift_no, $this->swish_no, $this->phone, $this->invoice_id_start, $this->credit_invoice_id_start, $this->pos_vat, $this->default_expirationdate, $this->website, $this->email, $this->fair);
		} else {
			$sql = "INSERT INTO fair_invoice (reference, company_name, address, zipcode, city, country, orgnr, bank_no, postgiro, vat_no, iban_no, swift_no, swish_no, phone, fair, invoice_id_start, credit_invoice_id_start, pos_vat, default_expirationdate, website, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$params = array($this->reference, $this->company_name, $this->address, $this->zipcode, $this->city, $this->country, $this->orgnr, $this->bank_no, $this->postgiro, $this->vat_no, $this->iban_no, $this->swift_no, $this->swish_no, $this->phone, $this->fair, $this->invoice_id_start, $this->credit_invoice_id_start, $this->pos_vat, $this->default_expirationdate, $this->website, $this->email);
		}
		
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
		
		return ($this->wasLoaded()) ? $this->exhibitor_id : $this->db->lastInsertId();
	}

	public function perm_delete() {
		if ($this->wasLoaded()) {
			error_log('Invoice was loaded in FairInvoice.php on line 24.');
			$exhibitor = new Exhibitor();
			$exhibitor->load($this->exhibitor, 'id');
			if ($exhibitor->wasLoaded()) {
				error_log('Exhibitor was loaded in FairInvoice.php on line 28.');
				$position = new FairMapPosition();
				$position->load($exhibitor->get('position'), 'id');
				if ($position->wasLoaded()) {
					error_log('Position was loaded in FairInvoiceC.php on line 32.');
					$invoice_file = ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$this->exhibitor.'/'.$rec_billing_company_name . '-' . $position->get('name'). '-' . $this->id . '.pdf';
					rm($invoice_file);
					error_log('Invoice was removed in FairInvoiceController.php on line 35.');
					$stmt = $this->db->prepare("DELETE FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ?");
					$stmt->execute($this->id, $this->fair);
					$stmt = $this->db->prepare("DELETE FROM fair_invoice WHERE row_id = ?");
					$stmt->execute($this->row_id);
				} else error_log("The position could not be loaded");
			} else error_log("The exhibitor could not be loaded");
		} else error_log("The file with row id ".$this->row_id." could not be deleted.");
	}


}

?>