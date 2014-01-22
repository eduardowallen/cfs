<?php
class JsonResponse {

	protected $variables = array();

	//Setter
	function set($name,$value) {
		$this->variables[$name] = $value;
	}

	//Output
    function render() {
		header('Content-type: application/json; charset=utf-8');
		echo json_encode($this->variables);
    }
}
?>