<?php

class Fair extends Model {

	protected $maps = array();
	protected $exhibitors = array();
	protected $categories = array();
	protected $extraOptions = array();
	protected $articles = array();
	protected $preliminaries = array();
	protected $logo;


	public function loadsimple($key, $by) {
		parent::load($key, $by);	
	}

	public function loadself($key, $by) {
		$stmt = $this->db->prepare("SELECT `id`, `name`, `hidden` FROM `fair` WHERE `".$by."` = ?");
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

	public function load($key, $by, $disregardLocked=false) {
		
		parent::load($key, $by);
		if ($this->wasLoaded()) {
			$this->fetchExternal('FairMap', 'maps', 'fair', $this->id, 'sortorder');

			$last_map_index = count($this->maps) - 1;
			foreach ($this->maps as $index => $map) {
				if ($index > 0) {
					$map->can_move_up = true;
				}

				if ($index < $last_map_index) {
					$map->can_move_down = true;
				}
			}

			if (file_exists(ROOT.'public/images/fairs/'.$this->id.'/logotype/'.$this->id.'_logo.jpg')) {
				$this->logo = 'images/fairs/'.$this->id.'/logotype/'.$this->id.'_logo.jpg';
			} else if (file_exists(ROOT.'public/images/fairs/'.$this->id.'/logotype/'.$this->id.'_logo.png')) {
				$this->logo = 'images/fairs/'.$this->id.'/logotype/'.$this->id.'_logo.png';
			}

			//if (!$disregardLocked)
				//$this->isLocked();
			$this->fetchExternal('Exhibitor', 'exhibitors', 'fair', $this->id);
			$this->fetchExternal('ExhibitorCategory', 'categories', 'fair', $this->id);
			$this->fetchExternal("FairExtraOption", 'extraOptions', 'fair', $this->id);
			$this->fetchExternal("FairArticle", 'articles', 'fair', $this->id);

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

	public function load2($key, $by, $disregardLocked=false) {
		
		parent::load2($key, $by);
		if ($this->wasLoaded()) {
			$this->fetchExternal('FairMap', 'maps', 'fair', $this->id, 'sortorder');
		}
	}

	private function getMapIndex($map_id) {
		foreach ($this->maps as $index => $map) {
			if ($map->get('id') == $map_id) {
				return array(
					'index' => $index, 
					'map' => $map
				);
			}
		}

		return null;
	}

	private function saveMapSortorders() {
		// Save new sortorders
		$sortorder = 1;
		foreach ($this->maps as $map) {
			$map->set('sortorder', $sortorder);
			$map->save();

			++$sortorder;
		}
	}

	public function moveMapUp($map_id) {
		$map = $this->getMapIndex($map_id);

		if ($map !== null) {
			if ($map['map']->can_move_up) {
				$previous_index = $map['index'] - 1;
				$previous = $this->maps[$previous_index];

				// Do the switch
				$this->maps[$previous_index] = $map['map'];
				$this->maps[$map['index']] = $previous;

				$this->saveMapSortorders();
			}
		}
	}

	public function moveMapDown($map_id) {
		$map = $this->getMapIndex($map_id);

		if ($map !== null) {
			if ($map['map']->can_move_down) {
				$next_index = $map['index'] + 1;
				$next = $this->maps[$next_index];

				// Do the switch
				$this->maps[$next_index] = $map['map'];
				$this->maps[$map['index']] = $next;

				$this->saveMapSortorders();
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
			$stmt->execute(array('Övrigt', $id));
		}
		
		if (!file_exists(ROOT.'public/images/fairs/'.$id)) {
			mkdir(ROOT.'public/images/fairs/'.$id);
			mkdir(ROOT.'public/images/fairs/'.$id.'/maps');
			chmod(ROOT.'public/images/fairs/'.$id, 0775);
			chmod(ROOT.'public/images/fairs/'.$id.'/maps', 0775);
		}
		if (!file_exists(ROOT.'public/images/fairs/'.$id.'/logotype')) {
			mkdir(ROOT.'public/images/fairs/'.$id.'/logotype');
			chmod(ROOT.'public/images/fairs/'.$id.'/logotype', 0775);
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
		
		if (file_exists(ROOT.'public/images/fairs/'.$this->id)) {
			unlink(ROOT.'public/images/fairs/'.$this->id);
		}

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
		if ($property === "extraOptions") {
			usort($result, function ($a, $b) {
				return strcmp(mb_strtoupper($a->text, "UTF-8"), mb_strtoupper($b->text, "UTF-8"));
			});
		}		
		if ($property === "articles") {
			usort($result, function ($a, $b) {
				return strcmp(mb_strtoupper($a->text, "UTF-8"), mb_strtoupper($b->text, "UTF-8"));
			});
		}
		if ($property == 'website') {
			$val = parent::get($property);
			if (!preg_match('/^http/', $val) && $val != '')
				$val = 'http://'.$val;
			
			return $val;
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
