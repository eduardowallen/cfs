<?php
class Translator {

	private static $translation_config_path = 'config/translation_config.json';
	private $translation_config = null;
	private $lang_file_path = 'application/lang/';
	private $lang;
	private $data;

	public function __construct($lang) {

		$this->lang = $lang;
		if (file_exists(ROOT.$this->lang_file_path.$this->lang.'.json')) {
			$this->data = json_decode(file_get_contents(ROOT.$this->lang_file_path.$this->lang.'.json'));
		}

		// Create the config file if not exists
		if (!file_exists(ROOT . self::$translation_config_path)) {
			file_put_contents(ROOT . self::$translation_config_path, json_encode(array('translator_db' => false)));
		}

		// Read translation configuration
		$this->translation_config = json_decode(file_get_contents(ROOT . self::$translation_config_path));
	}

	public function getTranslatorConfig($key) {
		return $this->translation_config->{$key};
	}

	public function setTranslatorConfig($key, $value) {
		$this->translation_config->{$key} = $value;
		file_put_contents(ROOT . self::$translation_config_path, json_encode($this->translation_config));
	}
	
	public function __get($val) {

		// Check if translations are configured to be saved in database
		if ($this->translation_config->translator_db) {
			global $globalDB;
			$this->db = $globalDB;

			// Search for requested string value
			$stmt = $this->db->prepare("SELECT * FROM language_string WHERE value = ?");
			$stmt->execute(array($val));
			$res = $stmt->fetch(PDO::FETCH_ASSOC);

			// If string wasn't found
			if (!$res) {
				// Create a new string group by getting the highest current group number and increase
				$stmt = $this->db->prepare("SELECT MAX(`group`) AS group_max FROM language_string");
				$stmt->execute(array());
				$res = $stmt->fetch(PDO::FETCH_ASSOC);
				$nextGroup = $res['group_max'] + 1;

				// Insert the new string value and group number
				$stmt = $this->db->prepare("INSERT INTO language_string (`value`, `lang`, `group`) VALUES (?, ?, ?)");
				$stmt->execute(array($val, 'en', $nextGroup));
			}
		}
		
		if (isset($this->data->{$val})) {
			return $this->data->{$val};
		} else {
			return $val;
		}
	}
	
	
	
}
?>
