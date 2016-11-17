<?php

class Exhibitor extends User {

	protected $fairs;
	protected $exhibitor_categories = array();
	protected $exhibitor_options = array();
	protected $exhibitor_articles_amount = array();
	protected $exhibitor_articles = array();

	public function __construct() {
		parent::__construct();
		$this->table_name = 'user';
	}


	public function loadDeleted($key, $by) {

		$stmt = $this->db->prepare("SELECT * FROM exhibitor_history WHERE `".$by."` = ?");
		$stmt->execute(array($key));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		$commodity = $res['commodity'];
		if ($res > 0) {
			
			foreach ($res as $property=>$value) {
				$this->$property = $value;
				$this->db_keys[] = $property;
			}
			
			$this->exhibitor_id = $this->id;
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

			$stmt = $this->db->prepare("SELECT * FROM exhibitor_article_rel AS ear LEFT JOIN fair_article AS fa ON ear.article = fa.id WHERE exhibitor = ? AND ear.amount != 0");
			$stmt->execute(array($this->exhibitor_id));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($result > 0) {
				foreach ($result as $res) {
					$this->exhibitor_articles[] = $res['article'];
					$this->exhibitor_articles_amount[] = $res['amount'];
				}
			}

			return true;
		}

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

			$stmt = $this->db->prepare("SELECT * FROM exhibitor_option_rel AS eol LEFT JOIN fair_extra_option AS feo ON eol.option = feo.id WHERE exhibitor = ? AND eol.option != 0");
			$stmt->execute(array($this->exhibitor_id));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($result > 0) {
				foreach ($result as $res) {
					$this->exhibitor_options[] = $res['option'];
				}
			}

			$stmt = $this->db->prepare("SELECT * FROM exhibitor_article_rel AS ear LEFT JOIN fair_article AS fa ON ear.article = fa.id WHERE exhibitor = ? AND ear.amount != 0");
			$stmt->execute(array($this->exhibitor_id));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($result > 0) {
				foreach ($result as $res) {
					$this->exhibitor_articles[] = $res['article'];
					$this->exhibitor_articles_amount[] = $res['amount'];
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
			$sql = "UPDATE exhibitor SET user = ?, fair = ?, position = ?, commodity = ?, arranger_message = ?, edit_time = ?, clone = ?, status = ? WHERE id = ?";
			$params = array($this->user, $this->fair, $this->position, $this->commodity, $this->arranger_message, time(), $this->clone, $this->status, $this->exhibitor_id);
		} else {
			$sql = "INSERT INTO exhibitor (user, fair, position, commodity, arranger_message, booking_time, edit_time, clone, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$params = array($this->user, $this->fair, $this->position, $this->commodity, $this->arranger_message, time(), $this->edit_time, $this->clone, $this->status);
		}
		
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
		
		return ($this->wasLoaded()) ? $this->exhibitor_id : $this->db->lastInsertId();
	}

	public function delete() {
		$stmt_history = $this->db->prepare("INSERT INTO exhibitor_history SELECT * FROM exhibitor WHERE id = ?");
		$stmt_history->execute(array($this->exhibitor_id));
		$stmt = $this->db->prepare("DELETE FROM exhibitor WHERE id = ?");
		$stmt->execute(array($this->exhibitor_id));
	}


	// Preliminary bookings
	public function del_pre_booking($id, $user_id, $position){
		$stmt_history = "INSERT INTO `preliminary_booking_history` SELECT * FROM `preliminary_booking` WHERE id = '{$id}' AND user = '{$user_id}'";
		$query3 = $this->db->query($stmt_history);
		$sql = "DELETE FROM `preliminary_booking` WHERE id = '{$id}' AND user = '{$user_id}'";
		$query = $this->db->query($sql);
	}

	public function del_booking($id, $user_id, $position){
		$stmt_history = "INSERT INTO exhibitor_history SELECT * FROM exhibitor WHERE id = '{$id}' AND user = '{$user_id}'";
		$query3 = $this->db->query($stmt_history);
		$sql = "DELETE FROM `exhibitor` WHERE id = '{$id}' AND user = '{$user_id}'";
		$query = $this->db->query($sql);
		$reset_pos = "UPDATE `fair_map_position` SET `status` = '0' WHERE id = '{$position}' LIMIT 1";
		$query2 = $this->db->query($reset_pos);
	}

	// Confirm cloned reservation
	public function verify_reservation($exid, $hash, $type){
		$ex = new Exhibitor;
		$ex->load($exid, 'id');

		$userid = $ex->get('user');
		$position = $ex->get('position');

		$user = new User;
		$user->load2($ex->get('user'), 'id');

		$fair = new Fair();
		$fair->load2($ex->get('fair'), 'id');

		$newexpirationdate = date('Y-m-d H:i:s', $fair->get('accepted_clone_date'));

		$hashcheck = md5($exid.BASE_URL.$user->get('alias'));

		$exlink = new ExhibitorLink();
		$exlink->load2($exid, 'exhibitor');

		$linkstatus = $exlink->get('status');

		if ($hash = $hashcheck) {
			if ($linkstatus == 1) {
				if ($type == 'accept') {
					$stmt_verify_ex = "UPDATE exhibitor SET `clone` = '0' WHERE id = '{$exid}'";
					$query = $this->db->query($stmt_verify_ex);
					$stmt_verify_pos = "UPDATE fair_map_position SET `expires` = '{$newexpirationdate}' WHERE id = '{$position}'";
					$query1 = $this->db->query($stmt_verify_pos);
					$stmt_update_link = "UPDATE exhibitor_link SET `status` = 0 WHERE exhibitor = '{$exid}'";
					$query2 = $this->db->query($stmt_update_link);
				}
				if ($type == 'deny') {
					$stmt_history = "INSERT INTO exhibitor_history SELECT * FROM exhibitor WHERE id = '{$exid}' AND user = '{$userid}'";
					$query3 = $this->db->query($stmt_history);
					$sql = "DELETE FROM `exhibitor` WHERE id = '{$exid}' AND user = '{$userid}'";
					$query = $this->db->query($sql);
					$reset_pos = "UPDATE `fair_map_position` SET `status` = '0', `expires` = '0000-00-00 00:00:00' WHERE id = '{$position}' LIMIT 1";
					$query2 = $this->db->query($reset_pos);
					$stmt_update_link = "UPDATE exhibitor_link SET `status` = 0 WHERE exhibitor = '{$exid}'";
					$query2 = $this->db->query($stmt_update_link);
				}
			}
		}
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
