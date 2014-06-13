<?php

class Administrator extends User {

	protected $fairs = array();
	protected $maps = array();

	public function __construct() {
		parent::__construct();
		$this->table_name = 'user';

	}

	public function load($key, $by) {
		parent::load($key, $by);
		if ($this->wasLoaded()) {

			$stmt = $this->db->prepare("SELECT * FROM fair_user_relation WHERE user = ?");
			$stmt->execute(array($this->id));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($result > 0) {
				$maps = array();
				foreach ($result as $res) {
					$this->fairs[] = $res['fair'];
					foreach(explode('|', $res['map_access']) as $m) {
						array_push($this->maps, $m);
					}
				}
			}
		}
	}

	function save() {

		if ($this->id == 0) {

			$arr = array_merge(range(0, 9), range('a', 'z'));
			shuffle($arr);
			$str = substr(implode('', $arr), 0, 10);
			
			$this->setPassword($str);

			$me = new User;
			$me->load($_SESSION['user_id'], 'id');
			
			$mail = new Mail($_POST['email'], 'administrator_new_account', $fair->get("url") . "@chartbooker.com", $fair->get("name"));
			$mail->setMailVar('url', BASE_URL.$_SESSION['outside_fair_url']);
			$mail->setMailVar('alias', $this->alias);
			$mail->setMailVar('password', $str);
			$mail->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
			$mail->setMailVar('creator_name', $me->get('name'));
			$mail->send();
		}

		$id = parent::save();
		return $id;

	}
	public function addRelation($fair){
		$stmt = $this->db->prepare("SELECT id, level FROM user WHERE email = ?");
		$stmt->execute(array($this->email));
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if( $result > 0 ){
			$id = $result['id'];
			$level = $result['level'];
			$DbQ = $this->db->prepare("SELECT map_access FROM fair_user_relation WHERE fair = ? AND user = ? ");
			$DbQ->execute(array($fair, $id));
			$DbQ_res = $DbQ->fetch(PDO::FETCH_ASSOC);
			if($DbQ_res > 0){
				$imploded = ( isset($_POST['maps']) AND count($_POST['maps']) > 0  ) ? implode( '|', $_POST['maps'] ) : array() ;
				if( count($imploded) > 0 ){
					$dbh = $this->db->prepare("UPDATE fair_user_relation SET map_access=? WHERE user=? AND fair = ?");
					$dbh->execute( array($imploded, $id, $fair) );
				}
			}else{
				if( $level == '2' ){
					$imploded = ( isset($_POST['maps']) AND count($_POST['maps']) > 0  ) ? implode( '|', $_POST['maps'] ) : array() ;
					if( count($imploded) > 0){
						echo 'that';
						$dbh = $this->db->prepare("INSERT INTO fair_user_relation ('fair', 'user', 'map_access', 'connected_time') VALUES(?, ?, ?, ?)");
						$dbh->execute( array($fair, $id, $imploded, time()) );
					}
				}
			}
		}
	}
	public function delete() {

		$stmt = $this->db->prepare("DELETE FROM fair_user_relation WHERE user = ?");
		$stmt->execute(array($this->id));

		parent::delete();
	}

}

?>