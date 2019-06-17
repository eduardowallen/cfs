<?php

class Arranger extends User {
	
	protected $fairs = array();
	
	public function __construct() {
		parent::__construct();
		$this->table_name = 'user';
		
	}
	
	public function load($key, $by) {
		parent::load($key, $by);
		if ($this->wasLoaded()) {
			$this->fetchExternal('Fair', 'fairs', 'created_by', $this->id);
		}
	}
	public function loadsimple($key, $by) {
		parent::load($key, $by);
		if ($this->wasLoaded()) {
			$this->fetchExternalSimple('Fair', 'fairs', 'created_by', $this->id);
		}
	}
	function save() {
		
		/*if ($this->id == 0) {
			
			$arr = array_merge(range(0, 9), range('a', 'z'));
			shuffle($arr);
			$str = substr(implode('', $arr), 0, 10);
			
			$this->setPassword($str);
			$emstr = 'Welcome to Chartbooker'."\r\n\r\n";
			$emstr.= 'Username: '.$this->alias."\r\n";
			$emstr.= 'Password: '.$str."\r\n";
			$emstr.= 'Access level: Organizer';
			$emstr.= "\r\n\r\n".BASE_URL."user/login";
			$emstr.= "\r\n\r\nBest regards,\r\nChartbooker International";
			sendMail($this->email, 'Your user account', $emstr);
		}*/
		
		$id = parent::save();
		return $id;
		
	}
	
	public function delete() {
		
		/*foreach ($this->fairs as $fair) {
			$fair->delete();
		}*/
		
		parent::delete();
		
	}
	
}

?>