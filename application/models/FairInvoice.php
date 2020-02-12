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
}

?>