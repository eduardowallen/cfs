<?php

class FairMap extends Model {
	
	protected $image;
	protected $large_image;
	protected $positions = array();
	protected $before = null;
	protected $after = null;
	
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

	public function setBefore($map) {
		$this->before = $map;
	}

	public function setAfter($map) {
		$this->after = $map;
	}

	public function canMoveUp() {
		return ($this->before !== null);
	}

	public function canMoveDown() {
		return ($this->after !== null);
	}

	public function moveUp() {
		if ($this->canMoveUp()) {
			$temp = $this->get('sortorder');

			$this->set('sortorder', $this->before->get('sortorder'));
			$this->save();

			$this->before->set('sortorder', $temp);
			$this->before->save();
		}
	}

	public function moveDown() {
		if ($this->canMoveDown()) {
			$temp = $this->get('sortorder');

			$this->set('sortorder', $this->after->get('sortorder'));
			$this->save();

			$this->after->set('sortorder', $temp);
			$this->after->save();
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