<?php

class Exhibitor extends User {

	protected $fairs;
	protected $exhibitor_categories = array();

	public function __construct() {
		parent::__construct();
		$this->table_name = 'user';
	}

	public function load($key, $by) {

		$stmt = $this->db->prepare("SELECT * FROM exhibitor WHERE `".$by."` = ?");
		$stmt->execute(array($key));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		$commodity = $res['commodity'];
		if ($res > 0) {
			
			foreach ($res as $property=>$value) {
				$this->$property = $value;
				$this->db_keys[] = $property;
			}
			
			$this->exhibitor_id = $this->id;
			$this->exhibitor_category = $this->category;
			$this->spot_commodity = $commodity;

			$this->loaded = true;
			parent::load($this->user, 'id');

			$stmt = $this->db->prepare("SELECT * FROM fair_user_relation WHERE user = ?");
			$stmt->execute(array($this->user));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($result > 0) {
				foreach ($result as $res) {
					$this->fairs[] = $res['fair'];
				}
			}
			
			$stmt = $this->db->prepare("SELECT * FROM exhibitor_category_rel WHERE exhibitor = ?");
			$stmt->execute(array($this->exhibitor_id));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($result > 0) {
				foreach ($result as $res) {
					$this->exhibitor_categories[] = $res['category'];
				}
			}

			return true;
		}

	}

	public function wasLoaded() {
		return (isset($this->loaded)) ? $this->loaded : false;
	}

	public function save() {
		
		if ($this->wasLoaded()) {
			$sql = "UPDATE exhibitor SET user = ?, fair = ?, position = ?, category = ?, presentation = ?, commodity = ?, arranger_message = ?, booking_time = ? WHERE id = ?";
			$params = array($this->user, $this->fair, $this->position, $this->category, $this->presentation, $this->commodity, $this->arranger_message, time(), $this->exhibitor_id);
		} else {
			$sql = "INSERT INTO exhibitor (user, fair, position, category, presentation, commodity, arranger_message, booking_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
			$params = array($this->user, $this->fair, $this->position, $this->category, $this->presentation, $this->commodity, $this->arranger_message, time());
		}
		
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
		
		return ($this->wasLoaded()) ? $this->exhibitor_id : $this->db->lastInsertId();
	}


	// Preliminary bookings
	public function del_pre_booking($id, $user_id, $position){
		$sql = "DELETE FROM `preliminary_booking` WHERE id = '{$id}' AND user = '{$user_id}'";
		$query = $this->db->query($sql);
		$reset_pos = "UPDATE `fair_map_position` SET `status` = '0' WHERE id = '{$position}' LIMIT 1";
		$query2 = $this->db->query($reset_pos);
	}

	public function del_booking($id, $user_id, $position){
		$sql = "DELETE FROM `exhibitor` WHERE id = '{$id}' AND user = '{$user_id}'";
		$query = $this->db->query($sql);
		$reset_pos = "UPDATE `fair_map_position` SET `status` = '0' WHERE id = '{$position}' LIMIT 1";
		$query2 = $this->db->query($reset_pos);
	}
	
	public function get($att) {
		if ($att == 'website') {
			$val = parent::get($att);
			if (!preg_match('/^http/', $val) && $val != '')
				$val = 'http://'.$val;
			
			return $val;
		}
		return parent::get($att);
	}

}

?>
