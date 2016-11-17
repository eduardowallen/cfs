<?php 

class PreliminaryBookingHistory extends Model {

	protected $user_object;
	
	public function load($foo, $bar) {
		
		parent::load($foo, $bar);
		$this->user_object = new User;
		$this->user_object->load($this->user, 'id');
		
	}

}

?>