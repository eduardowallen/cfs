<?php

class User extends Model {

	protected $static_salt = 'aXQFm.9gR/JCe.HQ';
	protected $preliminaries;

	public function load($foo, $bar) {
		parent::load($foo, $bar);
		if ($this->wasLoaded()) {
			//$this->fetchExternal('PreliminaryBooking', 'preliminaries', 'user', $this->id);
			
		}
	}
	public function loadid($foo, $bar) {
		parent::loadid($foo, $bar);
		if ($this->wasLoaded()) {}
	}

	public function loadAllView($key, $by) {
		$stmt = $this->db->prepare("SELECT `id`, `company`, `orgnr`, `name`, `email`, `last_login`, `created`, `contact_phone2`, `commodity`, `city` FROM ".$this->table_name." WHERE `".$by."` = ?");
		//echo "SELECT * FROM ".$this->table_name." WHERE `".$by."` = ".$key;
		$stmt->execute(array($key));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($res > 0) {

			foreach ($res as $property=>$value) {
				$this->$property = $value;
				$this->db_keys[] = $property;
			}

			$this->loaded = true;
			return true;
		} else {
			$this->loaded = false;
			return false;
		}
	}

	public function bCrypt($pass, $user, $rounds=12) {

	    //Make sure rounds are between 4 and 31
	    if ($rounds < 4)
	        $rounds = 4;
	    else if ($rounds > 31)
	        $rounds = 31;

	 	//Create salt
	 	$salt = $this->static_salt.substr(md5($user), 0, 6);

		//Create prefix for crypt()
	    $prefix = sprintf('$2a$%02d$', $rounds);

	    //Is the salt ok?
	    if (!preg_match('#^[A-Za-z0-9./]{22}$#', $salt))
	       return;

	    //Return hash
	    return crypt($pass, $prefix.$salt);

	}

	public function setPassword($str) {
		$this->set('password', $this->bCrypt($str, $this->get('alias')));
		$this->set('password_changed', time());
	}

	public function getMyPreliminaries($id=null) {
		$id = ($id != null) ? $id : $this->id ;
		$stmt = $this->db->prepare("SELECT * FROM preliminary_booking WHERE user = ?");
		$stmt->execute(array($id));
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$prels = array();
		if (count($res) > 0) {
			foreach ($res as $r) {
				$category_ids = explode("|", $r["categories"]);
				//Get categories for prel booking
				$r["category_list"] = array();
				foreach ($category_ids as $catid) {
					$stmt = $this->db->prepare("SELECT `name` FROM exhibitor_category WHERE id = ?");
					$stmt->execute(array($catid));
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
					if ($result > 0) {
						foreach ($result as $row) {
							$r["category_list"][] = $row['name'];
						}
					}
				}

				$option_ids = explode("|", $r["options"]);
				//Get options for prel booking
				$r["option_list"] = array();
				foreach ($option_ids as $optid) {
					$stmt = $this->db->prepare("SELECT `text` FROM fair_extra_option WHERE `id` = ?");
					$stmt->execute(array($optid));
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
					if ($result > 0) {
						foreach ($result as $row) {
							$r["option_list"][] = $row['text'];
						}
					}
				}
				$prels[] = $r;
			}
		}

		return $prels;
	}
	
	public function getPreliminaries($id=null) {
		$id = ($id != null) ? $id : $this->id ;
		$stmt = $this->db->prepare("SELECT * FROM preliminary_booking WHERE user = ?");
		$stmt->execute(array($id));
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$prels = array();
		if (count($res) > 0) {
			foreach ($res as $r) {
				$category_ids = explode("|", $r["categories"]);
				//Get categories for prel booking
				$r["category_list"] = array();
				foreach ($category_ids as $catid) {
					$stmt = $this->db->prepare("SELECT * FROM exhibitor_category WHERE id = ?");
					$stmt->execute(array($catid));
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
					if ($result > 0) {
						foreach ($result as $row) {
							$r["category_list"][] = $row['name'];
						}
					}
				}

				$option_ids = explode("|", $r["options"]);
				//Get options for prel booking
				$r["option_list"] = array();
				foreach ($option_ids as $optid) {
					$stmt = $this->db->prepare("SELECT * FROM fair_extra_option WHERE `id` = ?");
					$stmt->execute(array($optid));
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
					if ($result > 0) {
						foreach ($result as $row) {
							$r["option_list"][] = $row['text'];
						}
					}
				}
				$r["presentation"] = $this->get("presentation");
				$r["website"] = $this->get("website");
				$r["company"] = $this->get("company");
				$prels[] = $r;
			}
		}

		return $prels;
	}

	public function login($user, $pass) {
		
		/*if ($_SERVER['REMOTE_ADDR'] == '213.66.203.3') {
			$stmt = $this->db->prepare("SELECT id FROM user WHERE `alias` = ? LIMIT 0,1");
			$stmt->execute(array($user));
		} else {*/
		$stmt = $this->db->prepare("SELECT `id`, `alias`, `password` FROM `user` WHERE `alias` = ? AND `locked` = 0 LIMIT 0, 1");
		$stmt->execute(array($user));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
			//$stmt = $this->db->prepare("SELECT id FROM user WHERE `alias` = ? AND password = ? AND locked = ? LIMIT 0,1");
			//$stmt->execute(array($user, $this->bCrypt($pass, $user), 0));
		//}
		if (!empty($res)) {
			if ($res["password"] === $this->bCrypt($pass, $res["alias"])) {
				$this->load($res['id'], 'id');
				$this->set('last_login', time());
				$this->set('total_logins', $this->get('total_logins') + 1);
				$this->save();
				$this->load($res['id'], 'id');

				return true;
			}
		}

		return false;
	}

	public function emailExists($email = '') {
		$stmt = $this->db->prepare("SELECT id FROM user WHERE LOWER(`email`) = LOWER(?)");
		if ($email == '') {
			$stmt->execute(array($this->email));
		} else {
			$stmt->execute(array($email));
		}
		$res = $stmt->fetch();

		if ($res > 0)
			return true;
		else
			return false;
	}
	
	public function aliasExists() {
		$stmt = $this->db->prepare("SELECT id FROM user WHERE LOWER(`alias`) = LOWER(?)");
		$stmt->execute(array($this->get('alias')));
		$res = $stmt->fetch();

		if ($res > 0)
			return true;
		else
			return false;
	}

	function save() {

		if (!$this->wasLoaded()) {
			$this->set('created', time());
		}

		$id = parent::save();
		return $id;

	}
	
	public function delete() {

		$stmt = $this->db->prepare("DELETE FROM fair_user_relation WHERE user = ?");
		$stmt->execute(array($this->id));

		parent::delete();
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

	public static function getExhibitorsForFair($fairId) {
		global $globalDB;
		$users = array();
		$fair = new Fair();
		$fair->loadid($fairId, 'id');
		if ($fair->wasLoaded()) {
			$stmt = $globalDB->prepare("SELECT fair_group_rel.group FROM fair_group_rel WHERE `fair` = ?");
			$stmt->execute(array($fairId));
			$groupId = $stmt->fetch(PDO::FETCH_ASSOC);
			if (count($groupId) > 0) {
				// Get the fairs in the group
				$stmt = $globalDB->prepare("SELECT fair_group_rel.fair FROM fair_group_rel WHERE `group` = ?");
				$stmt->execute(array($groupId['group']));
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				if (count($result) > 0) {
					$fairs_grouped = array();
					foreach ($result as $fairs) {
						$fairs_grouped[] = $fairs['fair'];
					}
					$stmt = $globalDB->prepare("SELECT DISTINCT user.* FROM user, fair_user_relation WHERE user.level=1 AND user.id = fair_user_relation.user AND fair_user_relation.fair IN(".implode(',', $fairs_grouped).")");
					$stmt->execute();
				} else {
					$stmt = $globalDB->prepare("SELECT DISTINCT user.* FROM user, fair_user_relation WHERE user.level=1 AND user.id = fair_user_relation.user AND fair_user_relation.fair = ?");
					$stmt->execute(array($fairId));
				}
			} else {
				$stmt = $globalDB->prepare("SELECT DISTINCT user.* FROM user, fair_user_relation WHERE user.level=1 AND user.id = fair_user_relation.user AND fair_user_relation.fair = ?");
				$stmt->execute(array($fairId));
			}
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $res) {
				$user = new User;
				$user->loadFromArray($res);
				$users[] = $user;
			}
		}

		return $users;
	}

	public static function getExhibitorsForArranger($arrId) {
		global $globalDB;
		$users = array();
		$stmt = $globalDB->prepare("SELECT DISTINCT user.* FROM user,fair_user_relation,fair WHERE fair.created_by=? AND fair_user_relation.fair = fair.id  AND user.id = fair_user_relation.user AND user.level=1");
		$stmt->execute(array($arrId));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $res) {
			$user = new User;
			$user->loadFromArray($res);
			$users[] = $user;
		}
		return $users;
	}

	public static function getExhibitorsForMyFairs() {
		if (userLevel() == 3) {
			return self::getExhibitorsForArranger($_SESSION['user_id']);
		} else {
			$users = array();

			foreach (getMyFairs() as $fair) {
				return self::getExhibitorsForFair($fair->id);
			}

			return $users;
		}
	}
}

?>
