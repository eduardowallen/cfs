<?php

class FairMapPosition extends Model {

	protected $exhibitor = null;
	protected $preliminaries = array();
	protected $statusText;

	public function load($val, $key) {

		parent::load($val, $key);
		if ($this->wasLoaded()) {
			$this->statusText = $this->getStatusText();
			if ($this->status > 0) {
				if (strtotime($this->expires) < time() && $this->status == 1) {
					//Reset to 'open'
					$this->set('status', 0);
					$this->set('expires', '0000-00-00');
					$this->save();
					//Delete exhibitor for position
					$stmt = $this->db->prepare("DELETE FROM exhibitor WHERE position = ?");
					$stmt->execute(array($this->id));
				} else {
					$ex = new Exhibitor;
					$ex->load($this->id, 'position');
					if ($ex->wasLoaded())
						$this->exhibitor = $ex;
				}
			} else {
				$stmt = $this->db->prepare("SELECT id FROM preliminary_booking WHERE position = ?");
				$stmt->execute(array($this->id));
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $res) {
					$pb = new PreliminaryBooking;
					$pb->load($res['id'], 'id');
					$this->preliminaries[] = $pb;
				}
			}
		}

	}

	public function getStatusText() {
		$statuses = array('open', 'reserved', 'booked');
		return $statuses[$this->status];
	}
	
	public function save() {
		if (!$this->wasLoaded()) {
			$this->set('created_by', $_SESSION['user_id']);
		} else {
			logToDB($this->db, 'POSITION_UPDATED', array('val'=>'something'));
		}
		return parent::save();
	}

	public function delete() {

		$params = array($this->id);

		$stmt = $this->db->prepare("DELETE FROM exhibitor WHERE position = ?");
		$stmt->execute($params);

		$stmt = $this->db->prepare("DELETE FROM preliminary_booking WHERE position = ?");
		$stmt->execute($params);

		parent::delete();

	}
	
}

?>
