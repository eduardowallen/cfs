<?php
class FairRegistration extends Model {
	protected $fair = null;
	protected $author = null;

	public function load($key, $by) {
		if (parent::load($key, $by)) {
			$this->fair = new Fair();
			$this->fair->load($this->fair_id, 'id');
		}
	}
}
?>