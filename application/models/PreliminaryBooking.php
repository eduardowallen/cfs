<?php

class PreliminaryBooking extends Model {
	
	protected $user_object;
	
	public function load($foo, $bar) {
		
		parent::load($foo, $bar);
		$this->user_object = new User;
		$this->user_object->load($this->user, 'id');
		
	}
	
	public function save() {
		
		$stmt = $this->db->prepare("SELECT * FROM preliminary_booking WHERE user = ? AND position = ?");
		$stmt->execute(array($this->user, $this->position));
		$res = $stmt->fetchAll();
		
		if (count($res) > 0) {
			return false;
		} else {
			return parent::save();
		}
		
	}

	// Preliminary bookings
	public function del_pre_booking($id, $user_id, $position) {
		$stmt_history = $this->db->prepare("INSERT INTO preliminary_booking_history SELECT id, user, fair, position, categories, options, articles, amount, commodity, arranger_message, booking_time, ? AS deletion_time, NULL AS deletion_message FROM preliminary_booking WHERE id = ? AND user = ? AND position = ?");
		$stmt_history->execute(array(time(), $id, $user_id, $position));
		$stmt = $this->db->prepare("DELETE FROM preliminary_booking WHERE id = ? AND user = ?");
		$stmt->execute(array($id, $user_id));
	}
	
	public static function getPreliminariesByFair($fairId) {
		if (!$fairId) {
			return;
		}

		global $globalDB;
		$stmt = $globalDB->prepare("SELECT * FROM preliminary_booking WHERE `fair`=?");
		$stmt->execute(array($fairId));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$preliminaries = array();

		foreach ($result as $res) {
			$prel = new PreliminaryBooking;
			$prel->loadFromArray($res);
			$preliminaries[] = $prel;
		}

		return $preliminaries;
	}
	public function delete($message='') {
		
		$stmt_history = $this->db->prepare("INSERT INTO preliminary_booking_history (id, user, fair, position, categories, options, articles, amount, commodity, arranger_message, booking_time, deletion_time, deletion_message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt_history->execute(array($this->id, $this->user, $this->fair, $this->position, $this->categories, $this->options, $this->articles, $this->amount, $this->commodity, $this->arranger_message, $this->booking_time, time(), $message));
		$stmt_delete = $this->db->prepare("DELETE FROM preliminary_booking WHERE id = ?");
		$stmt_delete->execute(array($this->id));
	}
	public function accept() {
		
		$stmt_history = $this->db->prepare("INSERT INTO preliminary_booking_accepted (id, user, fair, position, categories, options, articles, amount, commodity, arranger_message, booking_time, accepted_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt_history->execute(array($this->id, $this->user, $this->fair, $this->position, $this->categories, $this->options, $this->articles, $this->amount, $this->commodity, $this->arranger_message, $this->booking_time, time()));
		$stmt_delete = $this->db->prepare("DELETE FROM preliminary_booking WHERE id = ?");
		$stmt_delete->execute(array($this->id));
	}
}

?>