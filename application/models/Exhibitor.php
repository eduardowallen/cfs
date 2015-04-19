<?php

class Exhibitor extends User {

	protected $fairs;
	protected $exhibitor_categories = array();
	protected $exhibitor_options = array();

	public function __construct() {
		parent::__construct();
		$this->table_name = 'user';
	}

	public function load2($key, $by) {

		$stmt1 = $this->db->prepare("SELECT * FROM fair WHERE `id` = ?");
		$stmt2 = $this->db->prepare("SELECT * FROM exhibitor WHERE `fair` = ?");
		$stmt3 = $this->db->prepare("SELECT * FROM user LEFT JOIN exhibitor ON user.id = exhibitor.user WHERE `exhibitor.fair` = ?");
		$stmt4 = $this->db->prepare("SELECT * FROM fair_user_relation WHERE `fair` = ?");
		$stmt5 = $this->db->prepare("SELECT * FROM exhibitor_category WHERE `fair` = ?");
		$stmt6 = $this->db->prepare("SELECT * FROM exhibitor_category_rel LEFT JOIN exhibitor ON exhibitor_category_rel.exhibitor = exhibitor.id WHERE `exhibitor.fair` = ?");
		$stmt7 = $this->db->prepare("SELECT * FROM fair_extra_option WHERE `fair` = ?");
		$stmt8 = $this->db->prepare("SELECT * FROM exhibitor_option_rel LEFT JOIN exhibitor ON exhibitor_option_rel.exhibitor = exhibitor.id WHERE `exhibitor.fair` = ?");
		$stmt9 = $this->db->prepare("SELECT * FROM preliminary_booking WHERE `fair` = ?");
		$allstmt = "$stmt1$stmt2$stmt3$stmt4$stmt5$stmt6$stmt7$stmt8$stmt9";
		$allstmt->execute(array($key));
		$result = $allstmt->fetchAll(PDO::FETCH_ASSOC);
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

			$stmt = $this->db->prepare("SELECT * FROM exhibitor_option_rel WHERE exhibitor = ?");
			$stmt->execute(array($this->exhibitor_id));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($result > 0) {
				foreach ($result as $res) {
					$this->exhibitor_options[] = $res['option'];
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
			$sql = "UPDATE exhibitor SET user = ?, fair = ?, position = ?, category = ?, presentation = ?, commodity = ?, arranger_message = ?, edit_time = ? WHERE id = ?";
			$params = array($this->user, $this->fair, $this->position, $this->category, $this->presentation, $this->commodity, $this->arranger_message, time(), $this->exhibitor_id);
		} else {
			$sql = "INSERT INTO exhibitor (user, fair, position, category, presentation, commodity, arranger_message, booking_time, edit_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$params = array($this->user, $this->fair, $this->position, $this->category, $this->presentation, $this->commodity, $this->arranger_message, time(), $this->edit_time);
		}
		
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
		
		return ($this->wasLoaded()) ? $this->exhibitor_id : $this->db->lastInsertId();
	}

	public function delete() {
		$stmt = $this->db->prepare("DELETE FROM exhibitor WHERE id = ?");
		$stmt->execute(array($this->exhibitor_id));
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

	/* Static methods */

	public static function fetchAll() {
		global $globalDB;
		$stmt = $globalDB->query("SELECT u.*, ex.id AS exhibitor_id FROM exhibitor AS ex INNER JOIN user AS u ON u.id = ex.user GROUP BY u.id ORDER BY company");
		return $stmt->fetchAll(PDO::FETCH_CLASS, 'Exhibitor');
	}
}

?>
