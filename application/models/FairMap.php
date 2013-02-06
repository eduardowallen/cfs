<?php

class FairMap extends Model {
	
	protected $image;
	protected $large_image;
	protected $positions = array();
	
	public function load($key, $by) {
		
		parent::load($key, $by);
		if ($this->wasLoaded()) {
			$this->fetchExternal('FairMapPosition', 'positions', 'map', $this->id);
			if (file_exists(ROOT.'public/images/fairs/'.$this->fair.'/maps/'.$this->id.'.png')) {
				$this->image = 'images/fairs/'.$this->fair.'/maps/'.$this->id.'.png';
				$this->large_image = 'images/fairs/'.$this->fair.'/maps/'.$this->id.'_large.png';
			}
		}
		
	}
	
	public function delete() {
		
		foreach ($this->positions as $pos) {
			$pos->delete();
		}
		
		if (file_exists(ROOT.'public/images/fairs/'.$this->fair.'/maps/'.$this->id.'.png')) {
			unlink(ROOT.'public/images/fairs/'.$this->fair.'/maps/'.$this->id.'.png');
			unlink(ROOT.'public/images/fairs/'.$this->fair.'/maps/'.$this->id.'_large.png');
		}
		
		parent::delete();
		
	}
	
}

?>