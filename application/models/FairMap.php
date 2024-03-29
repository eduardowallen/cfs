<?php

class FairMap extends Model {
	
	protected $image;
	protected $large_image;
	protected $positions = array();

	public $can_move_up = false;
	public $can_move_down = false;

	public function loadself($key, $by) {
		$stmt = $this->db->prepare("SELECT `id`, `name`, `fair` FROM `fair_map` WHERE `".$by."` = ?");
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

	public function load($key, $by) {
		
		parent::load($key, $by);
		if ($this->wasLoaded()) {
			$this->fetchExternal('FairMapPosition', 'positions', 'map', $this->id);
			if (file_exists(ROOT.'public/images/fairs/'.$this->fair.'/maps/'.$this->id.'.jpg')) {
				$this->image = 'images/fairs/'.$this->fair.'/maps/'.$this->id.'.jpg';
				$this->large_image = 'images/fairs/'.$this->fair.'/maps/'.$this->id.'_large.jpg';
			} else if (file_exists(ROOT.'public/images/fairs/'.$this->fair.'/maps/'.$this->id.'.png')) {
				$this->image = 'images/fairs/'.$this->fair.'/maps/'.$this->id.'.png';
				$this->large_image = 'images/fairs/'.$this->fair.'/maps/'.$this->id.'_large.png';
			}
		}
		
	}

	public function load2($key, $by) {
		
		parent::load2($key, $by);
		if ($this->wasLoaded()) {
			//$this->fetchExternal2('FairMapPosition', 'positions', 'map', $this->id);
			if (file_exists(ROOT.'public/images/fairs/'.$this->fair.'/maps/'.$this->id.'.jpg')) {
				$this->image = 'images/fairs/'.$this->fair.'/maps/'.$this->id.'.jpg';
				$this->large_image = 'images/fairs/'.$this->fair.'/maps/'.$this->id.'_large.jpg';
			} else if (file_exists(ROOT.'public/images/fairs/'.$this->fair.'/maps/'.$this->id.'.png')) {
				$this->image = 'images/fairs/'.$this->fair.'/maps/'.$this->id.'.png';
				$this->large_image = 'images/fairs/'.$this->fair.'/maps/'.$this->id.'_large.png';
			}
		}
		
	}	

	public function delete() {
		
		foreach ($this->positions as $pos) {
			$pos->delete();
		}
		
		if (file_exists(ROOT.'public/images/fairs/'.$this->fair.'/maps/'.$this->id.'.jpg')) {
			unlink(ROOT.'public/images/fairs/'.$this->fair.'/maps/'.$this->id.'.jpg');
			unlink(ROOT.'public/images/fairs/'.$this->fair.'/maps/'.$this->id.'_large.jpg');
		}

		if (file_exists(ROOT.'public/images/fairs/'.$this->fair.'/maps/'.$this->id.'.png')) {
			unlink(ROOT.'public/images/fairs/'.$this->fair.'/maps/'.$this->id.'.png');
			unlink(ROOT.'public/images/fairs/'.$this->fair.'/maps/'.$this->id.'_large.png');
		}
		
		parent::delete();
		
	}
	
}

?>