<?php
class Translator {
	private $lang_file_path = 'application/lang/';
	private $lang;
	private $data;
	public function __construct($lang) {
		$this->lang = $lang;
		if (file_exists(ROOT.$this->lang_file_path.$this->lang.'.json')) {
			$this->data = json_decode(file_get_contents(ROOT.$this->lang_file_path.$this->lang.'.json'));
		}
		
	}
	
	public function __get($val) {
		// Om translate == true, så sparas strängar som kan översättas i databasen.
		global $translate;
		if ($translate) {
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
