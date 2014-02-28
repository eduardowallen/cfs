<?php

class Fair extends Model {

	protected $maps = array();
	protected $exhibitors = array();
	protected $categories = array();
	protected $preliminaries = array();
	protected $logo;

	public function load($key, $by, $disregardLocked=false) {
		
		parent::load($key, $by);
		if ($this->wasLoaded()) {
			$this->fetchExternal('FairMap', 'maps', 'fair', $this->id);
			if (file_exists(ROOT.'public/images/fairs/'.$this->id.'/'.$this->id.'.jpg')) {
				$this->logo = 'images/fairs/'.$this->id.'/'.$this->id.'.jpg';
			} else if (file_exists(ROOT.'public/images/fairs/'.$this->id.'/'.$this->id.'.png')) {
				$this->logo = 'images/fairs/'.$this->id.'/'.$this->id.'.png';
			}
			//if (!$disregardLocked)
				//$this->isLocked();
			$this->fetchExternal('Exhibitor', 'exhibitors', 'fair', $this->id);
			$this->fetchExternal('ExhibitorCategory', 'categories', 'fair', $this->id);

			$stmt = $this->db->prepare("SELECT * FROM preliminary_booking WHERE fair = ?");
			$stmt->execute(array($this->id));
			$res = $stmt->fetchAll();

			if (count($res) > 0) {
				foreach ($res as $r) {
					$u = new User;
					$u->load($r['user'], 'id');
					$this->preliminaries[] = array('user'=>$u, 'position'=>$r['position']);
				}
			}
		}

	}
	
	public function publicView() {
		
	}

	private function makeUrl($str, $ignoreCaps=true) {
		if ($ignoreCaps)
			$str = mb_strtolower($str, 'UTF-8');

		//Common chars
		$search = array('å', 'ä', 'ö', 'é', '&', ' ', '/');
		$replace = array('a', 'a', 'o', 'e', 'och', '-', '-');

		//Replace the common chars
		$str = str_replace($search, $replace, $str);

		//Eliminate other forbidden chars
		$str = preg_replace('/([^-a-z0-9._])/i', '', $str);

		//Don't allow more than one dash in a row for aesthetic reasons
		$str = preg_replace('/-{2,}/', '-', $str);

		return $str;
	}


	public function save() {

		if ($this->id == 0) {
			$this->set('creation_time', time());
		}

		$this->set('url', $this->makeUrl($this->name));
		$id = parent::save();
		
		if ($this->id == 0) {
			$stmt = $this->db->prepare("INSERT INTO exhibitor_category (name, fair) VALUES (?, ?)");
			$stmt->execute(array('Other', $id));
		}
		
		if (!file_exists(ROOT.'public/images/fairs/'.$id)) {
			mkdir(ROOT.'public/images/fairs/'.$id);
			mkdir(ROOT.'public/images/fairs/'.$id.'/maps');
			chmod(ROOT.'public/images/fairs/'.$id, 0775);
			chmod(ROOT.'public/images/fairs/'.$id.'/maps', 0775);
		}

		return $id;

	}

	public function delete() {
		foreach ($this->maps as $map) {
			$map->delete();
		}
		foreach ($this->exhibitors as $ex) {
			$ex->delete();
		}
		foreach ($this->categories as $cat) {
			$cat->delete();
		}

		$stmt = $this->db->prepare("DELETE FROM preliminary_booking WHERE fair = ?");
		$stmt->execute(array($this->id));
		
		$stmt = $this->db->prepare("DELETE FROM exhibitor WHERE fair = ?");
		$stmt->execute(array($this->id));
		
		$stmt = $this->db->prepare("DELETE FROM fair_user_relation WHERE fair = ?");
		$stmt->execute(array($this->id));
		
		if (file_exists(ROOT.'public/images/fairs/'.$this->id.'/'.$this->id.'.jpg')) {
			unlink(ROOT.'public/images/fairs/'.$this->id.'/'.$this->id.'.jpg');
		}

		if (file_exists(ROOT.'public/images/fairs/'.$this->id.'/'.$this->id.'.png')) {
			unlink(ROOT.'public/images/fairs/'.$this->id.'/'.$this->id.'.png');
		}

		parent::delete();

	}

	public function get($property) {
		$result = parent::get($property);
		if ($property === "categories") {
			usort($result, function ($a, $b) {
				return strcmp(mb_strtoupper($a->name, "UTF-8"), mb_strtoupper($b->name, "UTF-8"));
			});
		}
		return $result;
	}

	private function isLocked(){
		if($this->get('approved') == 2 AND userLevel() != 4){
			header("Location: ".BASE_URL.'locked');
		}
	}

}

?>