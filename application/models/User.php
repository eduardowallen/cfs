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

	public function getPreliminaries($id=null) {
		$id = ($id != null) ? $id : $this->id ;
		$stmt = $this->db->prepare("SELECT * FROM preliminary_booking WHERE user = ?");
		$stmt->execute(array($id));
		$res = $stmt->fetchAll();
		$prels = array();
		if (count($res) > 0) {
			foreach ($res as $r) {
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
			$stmt = $this->db->prepare("SELECT id FROM user WHERE `alias` = ? AND password = ? AND locked = ? LIMIT 0,1");
			$stmt->execute(array($user, $this->bCrypt($pass, $user), 0));
		//}
		$res = $stmt->fetch();
		if ($res > 0) {
			$this->load($res['id'], 'id');
			$this->set('last_login', time());
			$this->set('total_logins', $this->get('total_logins') + 1);
			$this->save();
			$this->load($res['id'], 'id');
			return true;
		} else {
			return false;
		}
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
		$sql = "SELECT user.* FROM user, fair_user_relation WHERE user.level=1 AND user.id = fair_user_relation.user AND fair_user_relation.fair = ?";
		$stmt = $globalDB->prepare($sql);
		$stmt->execute(array($fairId));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $res) {
			$user = new User;
			$user->loadFromArray($res);
			$users[] = $user;
		}
		return $users;
	}

	public static function getExhibitorsForArranger($arrId) {
		global $globalDB;
		$users = array();
		$sql = "SELECT user.* FROM user,fair_user_relation,fair WHERE fair.created_by=? AND fair_user_relation.fair = fair.id  AND user.id = fair_user_relation.user AND user.level=1";
		$stmt = $globalDB->prepare($sql);
		$stmt->execute(array($arrId));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $res) {
			$user = new User;
			$user->loadFromArray($res);
			$users[] = $user;
		}
		return $users;
	}
}

?>
