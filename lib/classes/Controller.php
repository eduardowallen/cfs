<?php
class Controller {

	protected $_model;
	protected $_controller;
	protected $_action;
	protected $_template;
	protected $is_ajax = false;

	function __construct($model, $controller, $action) {
		
		global $globalDB;
		$this->db = $globalDB;
		
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_model = $model;
		
		$this->$model = new $model;
		
		$this->_template = new Template($model, $action);

		//$this->db = $this->$model->db;

		global $translator;
		$this->translate = $translator;

		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			$this->is_ajax = true;
			$this->setNoTranslate('noView', true);
		}

	}

	function setNoTranslate($name, $value) {
		$this->_template->set($name, $value);
	}

	function set($name, $value) {
		if (gettype($value) == 'string' && $value != '') {

			/*$stmt = $this->db->prepare("SELECT `group` FROM language_string ORDER BY `group` DESC LIMIT 0,1");
			$stmt->execute(array());
			$res = $stmt->fetch(PDO::FETCH_ASSOC);
			$nextGroup = $res['group'] + 1;

			$stmt = $this->db->prepare("SELECT * FROM language_string WHERE value = ?");
			$stmt->execute(array($value));
			$res = $stmt->fetch(PDO::FETCH_ASSOC);
			if (!$res) {
				$stmt = $this->db->prepare("INSERT INTO language_string (`value`, `lang`, `group`) VALUES (?, ?, ?)");
				$stmt->execute(array($value, 'en', $nextGroup));
			}*/
			$value = $this->translate->{$value};

		}
		$this->_template->set($name, $value);

	}

	protected function createJsonResponse() {
		$this->_template = new JsonResponse('', '');
	}

	public function changeAction($new_action, $args = array()) {
		$this->_action = $new_action;
		$this->_template->setAction($new_action);

		call_user_func_array(array($this, $new_action), $args);
	}

	function __destruct() {
		$this->_template->render();
	}

}