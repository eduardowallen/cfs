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
	
}

?>