<?php
class JsonResponse extends Template {

	// Output
    function render() {
		header('Content-type: application/json; charset=utf-8');
		echo self::encode($this->variables);
    }

	public static function encode($object) {
		// Is the installed version of PHP 5.4 or newer?
		if (strnatcmp(phpversion(), '5.4.0') >= 0) {
			return json_encode($object);
		} else {
			return json_encode(self::traverse($object));
		}
	}

	public static function traverse($object) {
		// stdclass doesn't have the jsonSerialize method, instead it generates 
		// HTTP status 500
		if (is_object($object) && method_exists($object, 'jsonSerialize')) {
			$object = $object->jsonSerialize();

		} else if (is_array($object)) {
			foreach ($object as &$o) {
				$o = self::traverse($o);
			}
		}

		return $object;
	}
}
?>