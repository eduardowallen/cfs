<?php
class Template {

	protected $variables = array();
	protected $_controller;
	protected $_action;

	function __construct($controller,$action) {
		$this->_controller = getTableName($controller);
		$this->_action = $action;
	}
	
	//Setter
	function set($name,$value) {
		$this->variables[$name] = $value;
	}

	// Sets new action
	public function setAction($action) {
		$this->_action = $action;
	}
	
	//Output
    function render() {
		extract($this->variables);
		global $translator;
		
		if (!isset($noView)) {
			if (file_exists(ROOT.'application/views/'.$this->_controller.'/header.php')) {
				include (ROOT.'application/views/'. $this->_controller.'/header.php');
			} else {
				include (ROOT.'application/views/header.php');
			}
			
			if (file_exists(ROOT.'application/views/'.$this->_controller.'/'.$this->_action.'.php'))
	        	include (ROOT.'application/views/'.$this->_controller.'/'.$this->_action.'.php');
			
			if (file_exists(ROOT.'application/views/'.$this->_controller.'/footer.php')) {
				include (ROOT.'application/views/'.$this->_controller.'/footer.php');
			} else {
				include (ROOT.'application/views/footer.php');
			}

		} else if (isset($onlyContent)) {
			if (file_exists(ROOT.'application/views/'.$this->_controller.'/'.$this->_action.'.php'))
	        	include (ROOT.'application/views/'.$this->_controller.'/'.$this->_action.'.php');
		}
    }
	
}