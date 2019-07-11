<?php
class FairRegistration extends Model {

	protected $user_object;
	
	public function load($foo, $bar) {
		
		parent::load($foo, $bar);
		$this->user_object = new User;
		$this->user_object->load($this->user, 'id');
		
	}
	public function delete($message='') {

		$stmt_history = $this->db->prepare("INSERT INTO fair_registration_history (id, user, fair, categories, options, articles, amount, commodity, arranger_message, area, booking_time, deletion_time, deletion_message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt_history->execute(array($this->id, $this->user, $this->fair, $this->categories, $this->options, $this->articles, $this->amount, $this->commodity, $this->arranger_message, $this->area, $this->booking_time, time(), $message));
		$stmt_delete = $this->db->prepare("DELETE FROM fair_registration WHERE id = ?");
		$stmt_delete->execute(array($this->id));
	}
	public function accept() {
		$stmt_history = $this->db->prepare("INSERT INTO fair_registration_accepted (id, user, fair, categories, options, articles, amount, commodity, arranger_message, area, booking_time, accepted_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt_history->execute(array($this->id, $this->user, $this->fair, $this->categories, $this->options, $this->articles, $this->amount, $this->commodity, $this->arranger_message, $this->area, $this->booking_time, time()));
		$stmt_delete = $this->db->prepare("DELETE FROM fair_registration WHERE id = ?");
		$stmt_delete->execute(array($this->id));
	}
}
?>